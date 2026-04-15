<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /modules/seo/performance/api.php */

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/services/PerformanceAudit.php';
require_once __DIR__ . '/../_legacy_guard.php';
seoLegacyGuard('modules/seo/ajax/performance-audit.php', '/modules/seo/performance/api.php');

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

$userId = (int)(Auth::user()['id'] ?? 0);
$audit = new PerformanceAudit(db(), $userId);

try {
    verifyCsrf();
    $url = trim((string)($_POST['url'] ?? ''));
    $device = (string)($_POST['device'] ?? 'mobile');
    if (!in_array($device, ['mobile', 'desktop'], true)) {
        throw new InvalidArgumentException('Device invalide.');
    }

    $results = $audit->runAudit($url, $device);
    $results['url'] = $url;
    $results['device'] = $device;
    $id = $audit->saveAudit($userId, $results);

    echo json_encode(['success' => true, 'audit_id' => $id] + $results);
} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] performance: ' . $e->getMessage() . PHP_EOL, 3, dirname(__DIR__, 3) . '/logs/seo.log');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
