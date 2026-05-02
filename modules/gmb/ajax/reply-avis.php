<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
function gmb_json(bool $success, string $message, array $data = [], int $status = 200): void {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!Auth::check()) { gmb_json(false, 'Non autorisé', [], 401); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { gmb_json(false, 'Méthode non autorisée', [], 405); }
if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) { gmb_json(false, 'Token CSRF invalide.', [], 403); }

$avisId = (int) ($_POST['avis_id'] ?? 0);
$reponse = mb_substr(trim((string) ($_POST['reponse'] ?? '')), 0, 2000);
if ($avisId <= 0 || $reponse === '') { gmb_json(false, 'Paramètres invalides', [], 422); }

$service = new GmbService((int) Auth::user()['id']);
$ok = $service->replyToAvis($avisId, $reponse);
gmb_json($ok, $ok ? 'Réponse enregistrée.' : 'Impossible de répondre.');
