<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/services/SitemapService.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

try {
    verifyCsrf();

    $userId = (int) (Auth::user()['id'] ?? 0);
    $service = new SitemapService(db());
    $service->ensureSchema();
    $result = $service->generate($userId);

    if (!empty($_POST['submit_gsc'])) {
        $service->submitPlaceholder($userId);
    }

    echo json_encode(['success' => true, 'xml' => $result['xml']]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
