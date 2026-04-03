<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
if (!Auth::check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Non autorisé']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']); exit; }

$avisId = (int) ($_POST['avis_id'] ?? 0);
$reponse = trim((string) ($_POST['reponse'] ?? ''));
if ($avisId <= 0 || $reponse === '') { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Paramètres invalides']); exit; }

$service = new GmbService((int) Auth::user()['id']);
$ok = $service->replyToAvis($avisId, $reponse);
echo json_encode(['success' => $ok, 'message' => $ok ? 'Réponse publiée.' : 'Impossible de répondre.']);
