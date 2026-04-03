<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
if (!Auth::check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Non autorisé']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']); exit; }

$service = new GmbService((int) Auth::user()['id']);
$id = $service->createDemandeAvis($_POST);
echo json_encode(['success' => true, 'message' => 'Demande d\'avis envoyée.', 'demande_id' => $id]);
