<?php
header('Content-Type: application/json; charset=utf-8');

$initPath = dirname(dirname(__DIR__)) . '/includes/init.php';
if (file_exists($initPath)) {
    require_once $initPath;
}

// Load ErrorHandler for secure error logging
if (!class_exists('ErrorHandler')) {
    require_once dirname(dirname(__DIR__)) . '/includes/classes/ErrorHandler.php';
}

if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Connexion DB indisponible']);
    exit;
}

function vsRespond(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function vsTenantId(array $payload): int
{
    $tenantId = $payload['tenant_id'] ?? $_GET['tenant_id'] ?? $_POST['tenant_id'] ?? $_SERVER['HTTP_X_TENANT_ID'] ?? 1;
    if (!ctype_digit((string) $tenantId) || (int) $tenantId <= 0) {
        throw new InvalidArgumentException('tenant_id invalide');
    }

    return (int) $tenantId;
}

function vsEnsureSchema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS visual_assets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT UNSIGNED NOT NULL,
        mode ENUM('free','from_content','from_template') NOT NULL,
        engine ENUM('template_html5','ai_image','hybrid') NOT NULL DEFAULT 'template_html5',
        status ENUM('draft','generated','editing','validated','saved','used','archived') NOT NULL DEFAULT 'draft',
        target_platform VARCHAR(40) NOT NULL,
        target_format VARCHAR(40) NOT NULL,
        goal VARCHAR(40) NOT NULL,
        style VARCHAR(120) NOT NULL,
        has_text_overlay TINYINT(1) NOT NULL DEFAULT 1,
        has_cta TINYINT(1) NOT NULL DEFAULT 0,
        title VARCHAR(255) NOT NULL,
        source_text LONGTEXT NULL,
        render_payload JSON NULL,
        render_html LONGTEXT NULL,
        image_url VARCHAR(500) NULL,
        thumb_url VARCHAR(500) NULL,
        source_type VARCHAR(40) NULL,
        source_id BIGINT UNSIGNED NULL,
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        archived_at DATETIME NULL,
        KEY idx_tenant_status (tenant_id, status, created_at),
        KEY idx_tenant_platform (tenant_id, target_platform, target_format),
        KEY idx_source_link (tenant_id, source_type, source_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS visual_system_templates (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(120) NOT NULL UNIQUE,
        name VARCHAR(190) NOT NULL,
        default_platform VARCHAR(40) NOT NULL,
        default_format VARCHAR(40) NOT NULL,
        default_goal VARCHAR(40) NOT NULL,
        default_style VARCHAR(120) NOT NULL,
        template_payload JSON NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS visual_links (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT UNSIGNED NOT NULL,
        visual_asset_id BIGINT UNSIGNED NOT NULL,
        entity_type ENUM('article','page','social_post','gmb_post') NOT NULL,
        entity_id BIGINT UNSIGNED NOT NULL,
        usage_context VARCHAR(40) NOT NULL DEFAULT 'cover',
        is_primary TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_tenant_entity (tenant_id, entity_type, entity_id),
        KEY idx_tenant_asset (tenant_id, visual_asset_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function vsSeedSystemTemplates(PDO $pdo): void
{
    $templates = [
        ['question-magique-facebook', 'Question magique Facebook', 'facebook', '1200x630', 'engagement', 'local_impact'],
        ['conseil-rapide', 'Conseil rapide', 'instagram', '1080x1080', 'information', 'clean_minimal'],
        ['erreur-a-eviter', 'Erreur à éviter', 'facebook', '1200x630', 'credibility', 'expert_warning'],
        ['chiffre-marche-local', 'Chiffre marché local', 'gmb', '1200x900', 'credibility', 'data_card'],
        ['temoignage-client', 'Témoignage client', 'facebook', '1200x630', 'conversion', 'testimonial'],
        ['citation', 'Citation', 'instagram', '1080x1080', 'engagement', 'premium_quote'],
        ['story-engagement', 'Story engagement', 'instagram_story', '1080x1920', 'engagement', 'story_dynamic'],
        ['visuel-gmb', 'Visuel GMB', 'gmb', '1200x900', 'information', 'local_trust'],
        ['couverture-article', 'Couverture article', 'article_cover', '1200x628', 'information', 'editorial_cover'],
        ['promotion-service', 'Promotion service', 'facebook', '1200x630', 'conversion', 'offer_highlight'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO visual_system_templates
        (slug, name, default_platform, default_format, default_goal, default_style, template_payload, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

    foreach ($templates as $template) {
        $stmt->execute([
            $template[0],
            $template[1],
            $template[2],
            $template[3],
            $template[4],
            $template[5],
            json_encode(['headline' => '{{title}}', 'body' => '{{summary}}', 'cta' => '{{cta}}'], JSON_UNESCAPED_UNICODE),
        ]);
    }
}

function vsAllowedTransition(string $from, string $to): bool
{
    $allowed = [
        'draft' => ['generated', 'archived'],
        'generated' => ['editing', 'validated', 'saved', 'archived'],
        'editing' => ['validated', 'saved', 'archived'],
        'validated' => ['saved', 'used', 'archived'],
        'saved' => ['used', 'archived'],
        'used' => ['archived'],
        'archived' => [],
    ];

    return isset($allowed[$from]) && in_array($to, $allowed[$from], true);
}

function vsGetSourceCandidates(PDO $pdo, string $type): array
{
    $map = [
        'article' => ['table' => 'articles', 'id' => 'id', 'title' => 'title', 'body' => 'content', 'order' => 'updated_at'],
        'page' => ['table' => 'pages', 'id' => 'id', 'title' => 'title', 'body' => 'content', 'order' => 'updated_at'],
        'social_post' => ['table' => 'social_posts', 'id' => 'id', 'title' => 'title', 'body' => 'content', 'order' => 'updated_at'],
        'gmb_post' => ['table' => 'gmb_posts', 'id' => 'id', 'title' => 'title', 'body' => 'description', 'order' => 'updated_at'],
    ];

    if (!isset($map[$type])) {
        return [];
    }

    $m = $map[$type];
    try {
        $sql = sprintf(
            'SELECT %s AS id, %s AS title, LEFT(COALESCE(%s, ""), 1200) AS body FROM %s ORDER BY %s DESC LIMIT 100',
            $m['id'],
            $m['title'],
            $m['body'],
            $m['table'],
            $m['order']
        );
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    vsRespond(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}
$action = (string) ($payload['action'] ?? $_GET['action'] ?? '');

try {
    vsEnsureSchema($pdo);
    vsSeedSystemTemplates($pdo);
    $tenantId = vsTenantId($payload);

    if ($action === 'bootstrap') {
        $templates = $pdo->query("SELECT id, slug, name, default_platform, default_format, default_goal, default_style
                                  FROM visual_system_templates
                                  WHERE is_active = 1
                                  ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        vsRespond([
            'success' => true,
            'enums' => [
                'modes' => ['free', 'from_content', 'from_template'],
                'platforms' => ['gmb', 'facebook', 'instagram', 'instagram_story', 'article_cover', 'banner', 'local_real_estate'],
                'goals' => ['engagement', 'credibility', 'conversion', 'information'],
                'statuses' => ['draft', 'generated', 'editing', 'validated', 'saved', 'used', 'archived'],
                'engines' => ['template_html5', 'ai_image', 'hybrid'],
            ],
            'templates' => $templates,
        ]);
    }

    if ($action === 'content_candidates') {
        $type = (string) ($payload['type'] ?? $_GET['type'] ?? 'article');
        vsRespond(['success' => true, 'items' => vsGetSourceCandidates($pdo, $type)]);
    }

    if ($action === 'create_draft') {
        $mode = (string) ($payload['mode'] ?? '');
        $platform = trim((string) ($payload['target_platform'] ?? ''));
        $format = trim((string) ($payload['target_format'] ?? ''));
        $goal = trim((string) ($payload['goal'] ?? ''));
        $style = trim((string) ($payload['style'] ?? ''));

        if (!in_array($mode, ['free', 'from_content', 'from_template'], true)) {
            throw new InvalidArgumentException('Mode invalide');
        }
        if ($platform === '' || $format === '' || $goal === '' || $style === '') {
            throw new InvalidArgumentException('Champs obligatoires manquants');
        }

        $stmt = $pdo->prepare("INSERT INTO visual_assets
            (tenant_id, mode, engine, status, target_platform, target_format, goal, style, has_text_overlay, has_cta, title, source_text, source_type, source_id, render_payload)
            VALUES (?, ?, ?, 'draft', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $tenantId,
            $mode,
            (string) ($payload['engine'] ?? 'template_html5'),
            $platform,
            $format,
            $goal,
            $style,
            (int) (!empty($payload['has_text_overlay'])),
            (int) (!empty($payload['has_cta'])),
            (string) ($payload['title'] ?? 'Sans titre'),
            (string) ($payload['source_text'] ?? ''),
            (string) ($payload['source_type'] ?? null),
            !empty($payload['source_id']) ? (int) $payload['source_id'] : null,
            json_encode([
                'created_from' => 'visual-studio-ui',
                'template_slug' => (string) ($payload['template_slug'] ?? ''),
                'recommended_channel' => (string) ($payload['recommended_channel'] ?? ''),
            ], JSON_UNESCAPED_UNICODE),
        ]);

        vsRespond(['success' => true, 'id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'generate_template') {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('id invalide');
        }

        $assetStmt = $pdo->prepare("SELECT * FROM visual_assets WHERE id = ? AND tenant_id = ? LIMIT 1");
        $assetStmt->execute([$id, $tenantId]);
        $asset = $assetStmt->fetch(PDO::FETCH_ASSOC);
        if (!$asset) {
            throw new RuntimeException('Visuel introuvable');
        }

        $safeTitle = htmlspecialchars($asset['title'] ?: 'Titre', ENT_QUOTES, 'UTF-8');
        $safeBody = htmlspecialchars(mb_substr((string) ($asset['source_text'] ?? ''), 0, 180), ENT_QUOTES, 'UTF-8');

        $html = "<!doctype html><html><head><meta charset='utf-8'><style>
            body{margin:0;font-family:Inter,Arial,sans-serif;background:#f8fafc}
            .card{height:100vh;padding:56px;box-sizing:border-box;background:linear-gradient(140deg,#0f172a,#1d4ed8);color:#fff}
            .badge{display:inline-block;padding:8px 14px;background:#f59e0b;color:#111827;border-radius:999px;font-weight:700}
            h1{font-size:56px;line-height:1.12;margin:20px 0 10px}
            p{font-size:28px;line-height:1.4;opacity:.9;max-width:80%}
            .cta{margin-top:28px;display:inline-block;padding:12px 20px;background:#fff;color:#0f172a;border-radius:10px;font-weight:700}
        </style></head><body><div class='card'>
            <span class='badge'>" . strtoupper((string) $asset['target_platform']) . "</span>
            <h1>{$safeTitle}</h1>
            <p>{$safeBody}</p>
            " . ((int) $asset['has_cta'] ? "<span class='cta'>Contactez-nous</span>" : '') . "
        </div></body></html>";

        $update = $pdo->prepare("UPDATE visual_assets
            SET render_html = ?, status = 'generated',
                render_payload = JSON_SET(COALESCE(render_payload, JSON_OBJECT()), '$.last_generation', NOW())
            WHERE id = ? AND tenant_id = ?");
        $update->execute([$html, $id, $tenantId]);

        vsRespond(['success' => true, 'id' => $id, 'status' => 'generated', 'html' => $html]);
    }

    if ($action === 'change_status') {
        $id = (int) ($payload['id'] ?? 0);
        $to = (string) ($payload['status'] ?? '');
        if ($id <= 0 || $to === '') {
            throw new InvalidArgumentException('Paramètres invalides');
        }

        $stmt = $pdo->prepare("SELECT status FROM visual_assets WHERE id = ? AND tenant_id = ? LIMIT 1");
        $stmt->execute([$id, $tenantId]);
        $current = $stmt->fetchColumn();
        if (!$current) {
            throw new RuntimeException('Visuel introuvable');
        }

        if (!vsAllowedTransition((string) $current, $to)) {
            throw new RuntimeException("Transition interdite: {$current} -> {$to}");
        }

        $update = $pdo->prepare("UPDATE visual_assets
            SET status = ?, archived_at = CASE WHEN ? = 'archived' THEN NOW() ELSE archived_at END
            WHERE id = ? AND tenant_id = ?");
        $update->execute([$to, $to, $id, $tenantId]);

        vsRespond(['success' => true, 'id' => $id, 'status' => $to]);
    }

    if ($action === 'duplicate_asset') {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('id invalide');
        }

        $stmt = $pdo->prepare("SELECT * FROM visual_assets WHERE id = ? AND tenant_id = ? LIMIT 1");
        $stmt->execute([$id, $tenantId]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$asset) {
            throw new RuntimeException('Visuel introuvable');
        }

        $insert = $pdo->prepare("INSERT INTO visual_assets
            (tenant_id, mode, engine, status, target_platform, target_format, goal, style, has_text_overlay, has_cta, title, source_text, render_payload, render_html, image_url, thumb_url, source_type, source_id, created_by)
            VALUES (?, ?, ?, 'draft', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([
            $tenantId,
            $asset['mode'],
            $asset['engine'],
            $asset['target_platform'],
            $asset['target_format'],
            $asset['goal'],
            $asset['style'],
            (int) $asset['has_text_overlay'],
            (int) $asset['has_cta'],
            $asset['title'] . ' (copie)',
            $asset['source_text'],
            $asset['render_payload'],
            $asset['render_html'],
            $asset['image_url'],
            $asset['thumb_url'],
            $asset['source_type'],
            $asset['source_id'],
            $asset['created_by'],
        ]);

        vsRespond(['success' => true, 'id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'link_entity') {
        $id = (int) ($payload['id'] ?? 0);
        $entityType = (string) ($payload['entity_type'] ?? '');
        $entityId = (int) ($payload['entity_id'] ?? 0);
        if ($id <= 0 || $entityId <= 0 || !in_array($entityType, ['article', 'page', 'social_post', 'gmb_post'], true)) {
            throw new InvalidArgumentException('Payload liaison invalide');
        }

        $stmt = $pdo->prepare("INSERT INTO visual_links (tenant_id, visual_asset_id, entity_type, entity_id, usage_context, is_primary)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $tenantId,
            $id,
            $entityType,
            $entityId,
            (string) ($payload['usage_context'] ?? 'cover'),
            (int) (!empty($payload['is_primary'])),
        ]);

        $statusStmt = $pdo->prepare("SELECT status FROM visual_assets WHERE id = ? AND tenant_id = ? LIMIT 1");
        $statusStmt->execute([$id, $tenantId]);
        $current = (string) $statusStmt->fetchColumn();

        if (vsAllowedTransition($current, 'used')) {
            $pdo->prepare("UPDATE visual_assets SET status = 'used' WHERE id = ? AND tenant_id = ?")
                ->execute([$id, $tenantId]);
        }

        vsRespond(['success' => true]);
    }

    if ($action === 'download_html') {
        $id = (int) ($payload['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('id invalide');
        }

        $stmt = $pdo->prepare("SELECT title, render_html FROM visual_assets WHERE id = ? AND tenant_id = ? LIMIT 1");
        $stmt->execute([$id, $tenantId]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$asset) {
            throw new RuntimeException('Visuel introuvable');
        }

        if (empty($asset['render_html'])) {
            throw new RuntimeException('Aucun rendu à télécharger');
        }

        vsRespond([
            'success' => true,
            'filename' => preg_replace('/[^a-z0-9\-]+/i', '-', strtolower((string) $asset['title'])) . '.html',
            'content_base64' => base64_encode((string) $asset['render_html']),
        ]);
    }

    if ($action === 'list_assets') {
        $stmt = $pdo->prepare("SELECT id, mode, engine, status, target_platform, target_format, goal, style, has_text_overlay, has_cta, title, source_type, source_id, created_at
                               FROM visual_assets
                               WHERE tenant_id = ?
                               ORDER BY id DESC
                               LIMIT 100");
        $stmt->execute([$tenantId]);
        vsRespond(['success' => true, 'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    vsRespond(['success' => false, 'message' => 'Action non supportée'], 400);
} catch (Throwable $e) {
    ErrorHandler::log($e, 'visual-studio::api', ['payload' => isset($payload) ? array_keys($payload) : []]);
    vsRespond(['success' => false, 'message' => 'An error occurred'], 500);
}
