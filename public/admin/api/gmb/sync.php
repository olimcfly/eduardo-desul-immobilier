<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../core/bootstrap.php';
require_once __DIR__ . '/../../../../modules/gmb/includes/GmbService.php';

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$userId = (int) (Auth::user()['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utilisateur invalide']);
    exit;
}

$service = new GmbService($userId);
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'POST') {
    $jobId = $service->enqueueSyncJob('admin');

    echo json_encode([
        'success' => $jobId > 0,
        'job_id' => $jobId,
        'status' => 'pending',
        'message' => $jobId > 0 ? 'Synchronisation mise en file d\'attente.' : 'Impossible de lancer la synchronisation.',
    ]);
    exit;
}

if ($method === 'GET') {
    $job = $service->getLatestSyncJob();

    echo json_encode([
        'success' => true,
        'job' => $job,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
