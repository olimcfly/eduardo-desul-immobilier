<?php
if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

require_once dirname(__DIR__, 2) . '/includes/classes/SecteurSeoService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurPublishService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurService.php';

$db = $db ?? $pdo ?? null;
if (!$db instanceof PDO) {
    http_response_code(500);
    exit('DB indisponible');
}

$websiteId = (int)($_SESSION['current_website_id'] ?? $_GET['website_id'] ?? 1);
$slug = (string)($_GET['slug'] ?? '');
$preview = isset($_GET['preview']) && $_GET['preview'] === '1';

$service = new SecteurService($db);
$service->ensureSchema();

if ($slug === '') {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

if ($preview) {
    $stmt = $db->prepare('SELECT * FROM secteurs WHERE slug = ? AND website_id = ? LIMIT 1');
    $stmt->execute([$slug, $websiteId]);
    $secteur = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($secteur) {
        $secteur['sections'] = $service->getSections((int)$secteur['id'], true);
    }
} else {
    $secteur = $service->findPublishedBySlug($slug, $websiteId);
}

if (!$secteur) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$sections = $secteur['sections'] ?? [];
require __DIR__ . '/../templates/secteur.php';
