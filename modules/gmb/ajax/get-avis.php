<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
if (!Auth::check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Non autorisé']); exit; }

$service = new GmbService((int) Auth::user()['id']);
$count = $service->syncAvisFromGoogle();
$avis = $service->avis();
echo json_encode(['success' => true, 'message' => 'Avis synchronisés.', 'count' => $count, 'avis' => $avis]);
