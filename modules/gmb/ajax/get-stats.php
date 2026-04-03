<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
if (!Auth::check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Non autorisé']); exit; }

$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_POST['end_date'] ?? date('Y-m-d');

$service = new GmbService((int) Auth::user()['id']);
$stats = $service->getStats($startDate, $endDate);
echo json_encode(['success' => true, 'stats' => $stats]);
