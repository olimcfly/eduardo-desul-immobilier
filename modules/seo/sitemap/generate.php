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

    echo json_encode(['success' => true, 'data' => $service->generate($userId)]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
