<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../services/SeoTechnicalPerformanceService.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

verifyCsrf();

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new SeoTechnicalPerformanceService(db(), $userId);
$mode = (string)($_POST['mode'] ?? 'batch');

try {
    if ($mode === 'single') {
        $path = (string)($_POST['path'] ?? '/');
        $type = (string)($_POST['page_type'] ?? 'autre');
        $result = $service->runAuditForPath($path, $type);
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    $result = $service->runBatchAudit();
    echo json_encode(['success' => true, 'count' => count($result), 'data' => $result]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
