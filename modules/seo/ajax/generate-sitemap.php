<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/services/SitemapGenerator.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

$userId = (int)(Auth::user()['id'] ?? 0);
$generator = new SitemapGenerator(db(), $userId);

try {
    verifyCsrf();
    $generator->autoDiscoverUrls($userId);
    $xml = $generator->generate($userId);
    $saved = $generator->save($xml);
    $pinged = $saved ? $generator->ping() : false;

    $stmt = db()->prepare('INSERT INTO seo_sitemap_logs (user_id, generated_at, urls_count, ping_status, submitted_to_gsc, xml_size, created_at) VALUES (?, NOW(), ?, ?, ?, ?, NOW())');
    $stmt->execute([$userId, count($generator->getUrls($userId)), $pinged ? 1 : 0, !empty($_POST['submit_gsc']) ? 1 : 0, strlen($xml)]);

    echo json_encode(['success' => $saved, 'pinged' => $pinged, 'xml' => $xml]);
} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] sitemap: ' . $e->getMessage() . PHP_EOL, 3, dirname(__DIR__, 3) . '/logs/seo.log');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
