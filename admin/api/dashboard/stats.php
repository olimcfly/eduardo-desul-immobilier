<?php
require_once '../../../core/bootstrap.php';

header('Content-Type: application/json');

try {
    $controller = new DashboardController();
    $controller->getStats();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
