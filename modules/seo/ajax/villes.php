<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

$userId = (int)(Auth::user()['id'] ?? 0);
$action = (string)($_POST['action'] ?? 'save');

try {
    verifyCsrf();

    if ($action === 'generate') {
        $ville = trim((string)($_POST['ville'] ?? ''));
        if ($ville === '') {
            throw new InvalidArgumentException('Ville requise.');
        }
        $zone = (string)setting('zone_city', '', $userId);
        $prompt = "Génère une fiche ville SEO pour {$ville} pour un conseiller immobilier à {$zone}.";
        $content = "<h2>Immobilier à " . htmlspecialchars($ville, ENT_QUOTES) . "</h2><p>" . htmlspecialchars($prompt, ENT_QUOTES) . "</p>";
        echo json_encode(['success' => true, 'content' => $content]);
        exit;
    }

    $city = trim((string)($_POST['city'] ?? ''));
    $postal = trim((string)($_POST['postal_code'] ?? ''));
    $h1 = trim((string)($_POST['h1'] ?? ''));
    $seoTitle = trim((string)($_POST['seo_title'] ?? ''));
    $meta = trim((string)($_POST['meta_description'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    if ($city === '' || $postal === '' || $h1 === '' || $seoTitle === '' || $meta === '' || $content === '') {
        throw new InvalidArgumentException('Tous les champs principaux sont requis.');
    }

    $slug = strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $city)));
    $slug = trim($slug, '-');
    $status = (string)($_POST['status'] ?? 'draft');
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $keywords = array_values(array_filter(array_map('trim', explode(',', (string)($_POST['targeted_keywords'] ?? '')))));

    $stmt = db()->prepare(
        'INSERT INTO seo_city_pages (user_id, city, postal_code, slug, h1, seo_title, meta_description, content, price_m2, population, targeted_keywords, status, published_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
    );
    $stmt->execute([
        $userId,
        $city,
        $postal,
        $slug,
        $h1,
        mb_substr($seoTitle, 0, 60),
        mb_substr($meta, 0, 160),
        $content,
        (float)($_POST['price_m2'] ?? 0),
        (int)($_POST['population'] ?? 0),
        json_encode($keywords, JSON_UNESCAPED_UNICODE),
        $status,
        $status === 'published' ? date('Y-m-d H:i:s') : null,
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] villes: ' . $e->getMessage() . PHP_EOL, 3, dirname(__DIR__, 3) . '/logs/seo.log');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
