<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../includes/PerformanceAudit.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

try {
    $url = trim((string)($_POST['url_tested'] ?? ''));
    if ($url === '') {
        throw new InvalidArgumentException('URL à auditer obligatoire');
    }

    $audit = new PerformanceAudit(db(), (int)$_SESSION['user_id']);
    $result = $audit->runMockAudit($url);

    echo json_encode(['success' => true, 'data' => $result]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
