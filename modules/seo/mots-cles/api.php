<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../services/SeoKeywordPilotService.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

verifyCsrf();

$userId = (int)(Auth::user()['id'] ?? 0);
$keywordService = new SeoKeywordPilotService(db(), $userId);
$apiAction = (string)($_POST['api_action'] ?? '');

try {
    if ($apiAction === 'record_position') {
        $keywordService->recordPosition(
            (int)($_POST['keyword_id'] ?? 0),
            $_POST['position_value'] === '' ? null : (int)$_POST['position_value'],
            (string)($_POST['source'] ?? 'manual'),
            (string)($_POST['notes'] ?? '')
        );

        echo json_encode(['success' => true]);
        exit;
    }

    if ($apiAction === 'mock_position') {
        $position = random_int(3, 25);
        $keywordService->recordPosition((int)($_POST['keyword_id'] ?? 0), $position, 'mock', 'Mock API');
        echo json_encode(['success' => true, 'position' => $position]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action API inconnue']);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
}
