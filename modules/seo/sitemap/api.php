<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/services/SitemapService.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

try {
    verifyCsrf();

    $userId = (int) (Auth::user()['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'verify');

    $service = new SitemapService(db());
    $service->ensureSchema();

    if ($action === 'verify') {
        echo json_encode(['success' => true, 'data' => $service->verify($userId)]);
        exit;
    }

    if ($action === 'submit') {
        echo json_encode(['success' => true, 'data' => $service->submitPlaceholder($userId)]);
        exit;
    }

    throw new InvalidArgumentException('Action sitemap inconnue.');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
