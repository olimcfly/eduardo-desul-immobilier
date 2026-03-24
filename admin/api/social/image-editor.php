<?php
header('Content-Type: application/json; charset=utf-8');

$initPath = dirname(dirname(__DIR__)) . '/includes/init.php';
if (file_exists($initPath)) {
    require_once $initPath;
}

if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Connexion DB indisponible']);
    exit;
}

function ieRespond(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ieEnsureSchema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS social_image_designs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        source_type VARCHAR(20) NOT NULL,
        source_id INT UNSIGNED NULL,
        platform VARCHAR(30) NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NULL,
        html LONGTEXT NOT NULL,
        thumbnail_data LONGTEXT NULL,
        share_token VARCHAR(64) NOT NULL UNIQUE,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_platform (platform),
        KEY idx_source (source_type, source_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ieFetchRows(PDO $pdo, string $table, array $cols, string $orderBy): array {
    try {
        $select = implode(',', $cols);
        $stmt = $pdo->query("SELECT {$select} FROM {$table} ORDER BY {$orderBy} DESC LIMIT 300");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

function ieBuildTemplate(string $platform, string $title, string $content): array {
    $safeTitle = htmlspecialchars(trim($title) ?: 'Titre de publication', ENT_QUOTES, 'UTF-8');
    $safeContent = htmlspecialchars(trim($content) ?: 'Contenu à publier', ENT_QUOTES, 'UTF-8');

    $formats = [
        'facebook' => ['w' => 1200, 'h' => 630],
        'gmb' => ['w' => 1200, 'h' => 900],
        'instagram' => ['w' => 1080, 'h' => 1080],
        'blog' => ['w' => 1200, 'h' => 628],
    ];
    $f = $formats[$platform] ?? $formats['facebook'];

    $html = "<!doctype html><html lang='fr'><head><meta charset='utf-8'><style>
        body{margin:0;background:#f8fafc;font-family:Inter,Segoe UI,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh}
        .card{width:92%;height:90%;background:linear-gradient(150deg,#0f172a,#1e293b 55%,#334155);border-radius:28px;padding:56px;box-sizing:border-box;color:#fff;position:relative;overflow:hidden}
        .tag{display:inline-block;background:#f59e0b;color:#111827;padding:8px 14px;border-radius:999px;font-weight:700;font-size:20px}
        h1{font-size:64px;line-height:1.08;margin:24px 0 16px;max-width:92%}
        p{font-size:32px;line-height:1.4;color:#cbd5e1;max-width:88%}
        .foot{position:absolute;left:56px;bottom:42px;font-size:24px;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase}
        .shape{position:absolute;right:-90px;top:-90px;width:420px;height:420px;border-radius:50%;background:rgba(245,158,11,.18)}
    </style></head><body>
        <div class='card'>
            <div class='shape'></div>
            <span class='tag'>" . strtoupper($platform) . "</span>
            <h1>{$safeTitle}</h1>
            <p>{$safeContent}</p>
            <div class='foot'>Design minimal & élégant</div>
        </div>
    </body></html>";

    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$f['w']}' height='{$f['h']}' viewBox='0 0 {$f['w']} {$f['h']}'>"
        . "<defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%' stop-color='#0f172a'/><stop offset='100%' stop-color='#334155'/></linearGradient></defs>"
        . "<rect width='100%' height='100%' fill='url(#g)' rx='28'/>"
        . "<rect x='50' y='42' width='220' height='52' rx='26' fill='#f59e0b'/>"
        . "<text x='160' y='76' text-anchor='middle' font-size='24' font-family='Arial' fill='#111827' font-weight='700'>" . htmlspecialchars(strtoupper($platform), ENT_QUOTES, 'UTF-8') . "</text>"
        . "<text x='56' y='170' font-size='62' font-family='Arial' fill='#ffffff' font-weight='700'>" . htmlspecialchars(mb_substr(trim($title) ?: 'Titre', 0, 34), ENT_QUOTES, 'UTF-8') . "</text>"
        . "<text x='56' y='250' font-size='34' font-family='Arial' fill='#cbd5e1'>" . htmlspecialchars(mb_substr(trim($content) ?: 'Contenu', 0, 70), ENT_QUOTES, 'UTF-8') . "</text>"
        . "</svg>";

    return [
        'html' => $html,
        'thumbnail_data' => 'data:image/svg+xml;base64,' . base64_encode($svg),
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ieRespond(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}
$action = $payload['action'] ?? '';

try {
    ieEnsureSchema($pdo);

    if ($action === 'list_sources') {
        $articles = ieFetchRows($pdo, 'articles', ['id', 'title', 'content'], 'updated_at');
        if (!$articles) {
            $articles = ieFetchRows($pdo, 'blog_articles', ['id', 'title', 'content'], 'updated_at');
        }

        $pages = ieFetchRows($pdo, 'pages', ['id', 'title', 'content'], 'updated_at');
        $secteurs = ieFetchRows($pdo, 'secteurs', ['id', 'nom as title', 'description as content'], 'id');

        ieRespond([
            'success' => true,
            'sources' => [
                'articles' => $articles,
                'pages' => $pages,
                'secteurs' => $secteurs,
            ]
        ]);
    }

    if ($action === 'generate_template') {
        $platform = (string)($payload['platform'] ?? 'facebook');
        $title = (string)($payload['title'] ?? '');
        $content = (string)($payload['content'] ?? '');

        $result = ieBuildTemplate($platform, $title, $content);
        ieRespond(['success' => true] + $result);
    }

    if ($action === 'save_design') {
        $token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO social_image_designs
            (source_type, source_id, platform, title, content, html, thumbnail_data, share_token)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (string)($payload['source_type'] ?? 'article'),
            (int)($payload['source_id'] ?? 0),
            (string)($payload['platform'] ?? 'facebook'),
            (string)($payload['title'] ?? 'Sans titre'),
            (string)($payload['content'] ?? ''),
            (string)($payload['html'] ?? ''),
            (string)($payload['thumbnail_data'] ?? ''),
            $token,
        ]);

        ieRespond(['success' => true, 'id' => (int)$pdo->lastInsertId(), 'share_token' => $token]);
    }

    if ($action === 'list_designs') {
        $stmt = $pdo->query("SELECT id, platform, title, thumbnail_data, share_token, created_at
                             FROM social_image_designs ORDER BY id DESC LIMIT 60");
        ieRespond(['success' => true, 'designs' => $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : []]);
    }

    ieRespond(['success' => false, 'message' => 'Action non supportée'], 400);
} catch (Throwable $e) {
    ieRespond(['success' => false, 'message' => $e->getMessage()], 500);
}
