<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../includes/SitemapGenerator.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

try {
    $userId = (int)$_SESSION['user_id'];
    $generator = new SitemapGenerator(db(), $userId);
    $baseUrl = (string)setting('site_url', '', $userId);
    if ($baseUrl === '') {
        $baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    $xml = $generator->generateXml($baseUrl);
    $target = __DIR__ . '/../../../public/sitemap.xml';
    file_put_contents($target, $xml);

    echo json_encode(['success' => true, 'path' => '/sitemap.xml', 'count' => count($generator->listUrls())]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
