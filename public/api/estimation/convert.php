<?php
/**
 * API POST /api/estimation/convert
 * Enregistre une action de conversion (rapport email / contact / RDV).
 * Crée un lead CRM, envoie les emails, retourne JSON.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';

// ── Méthode ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// ── Lecture du corps ──────────────────────────────────────────
$raw = file_get_contents('php://input');
if ($raw) {
    $input = json_decode($raw, true) ?? [];
} else {
    $input = $_POST;
}

// ── CSRF ──────────────────────────────────────────────────────
$sessionToken  = $_SESSION['csrf_token'] ?? '';
$suppliedToken = $input['csrf_token'] ?? '';
if ($sessionToken === '' || !hash_equals($sessionToken, $suppliedToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Session expirée. Rechargez la page.']);
    exit;
}

// ── Rate limiting ─────────────────────────────────────────────
$rateLimitKey = 'conv_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!EstimationTunnelService::checkRateLimit($rateLimitKey, 5)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Trop de requêtes. Réessayez plus tard.']);
    exit;
}

// ── Validation ────────────────────────────────────────────────
$requestId  = (int)($input['request_id'] ?? 0);
$actionType = trim((string)($input['action_type'] ?? ''));
$firstName  = trim((string)($input['first_name'] ?? ''));
$lastName   = trim((string)($input['last_name'] ?? ''));
$email      = strtolower(trim((string)($input['email'] ?? '')));
$phone      = trim((string)($input['phone'] ?? ''));
$message    = trim((string)($input['message'] ?? ''));

$allowedActions = ['email_report', 'contact_request', 'rdv_request'];
if (!in_array($actionType, $allowedActions, true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Type d\'action invalide.']);
    exit;
}
if ($firstName === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Veuillez indiquer votre prénom.']);
    exit;
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Adresse email invalide.']);
    exit;
}
if ($requestId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Référence d\'estimation manquante.']);
    exit;
}

// ── Vérification que la request existe ───────────────────────
try {
    EstimationTunnelService::ensureTables();
    $req = EstimationTunnelService::findRequest($requestId);
    if (!$req) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Estimation introuvable.']);
        exit;
    }
} catch (Throwable $e) {
    error_log('[convert.php] findRequest error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erreur interne.']);
    exit;
}

// ── Capture CRM ───────────────────────────────────────────────
$stageMap = [
    'email_report'    => 'new',
    'contact_request' => 'contacted',
    'rdv_request'     => 'rdv_scheduled',
];
$priorityMap = [
    'email_report'    => 'normal',
    'contact_request' => 'high',
    'rdv_request'     => 'high',
];

try {
    $leadId = LeadService::capture([
        'source_type'      => LeadService::SOURCE_ESTIMATION,
        'pipeline'         => 'vente',
        'stage'            => $stageMap[$actionType],
        'priority'         => $priorityMap[$actionType],
        'first_name'       => $firstName,
        'last_name'        => $lastName,
        'email'            => $email,
        'phone'            => $phone,
        'intent'           => $actionType,
        'property_type'    => $req['property_type'] ?? '',
        'property_address' => ($req['city'] ?? '') . ' ' . ($req['postal_code'] ?? ''),
        'notes'            => $message,
        'consent'          => 1,
        'metadata_json'    => json_encode([
            'estimation_request_id' => $requestId,
            'result_low'  => $req['result_low'],
            'result_med'  => $req['result_med'],
            'result_high' => $req['result_high'],
            'surface'     => $req['surface'],
            'valuation_mode' => $req['valuation_mode'] ?? 'sold',
        ], JSON_UNESCAPED_UNICODE),
    ]);
} catch (Throwable $e) {
    error_log('[convert.php] LeadService::capture error: ' . $e->getMessage());
    $leadId = 0; // Non bloquant
}

// ── Enregistrement de l'action ────────────────────────────────
try {
    $actionId = EstimationTunnelService::saveAction([
        'request_id'  => $requestId,
        'action_type' => $actionType,
        'first_name'  => $firstName,
        'last_name'   => $lastName,
        'email'       => $email,
        'phone'       => $phone,
        'message'     => $message,
        'crm_lead_id' => $leadId ?: null,
    ]);

    // Marquer la request comme convertie
    if ($leadId > 0) {
        EstimationTunnelService::markConverted($requestId, $leadId, $actionType);
    }
} catch (Throwable $e) {
    error_log('[convert.php] saveAction error: ' . $e->getMessage());
    // Non bloquant — on continue
}

// ── Emails ────────────────────────────────────────────────────
$fullName = trim($firstName . ' ' . $lastName) ?: $firstName;

try {
    // Rapport détaillé par email
    if ($actionType === 'email_report' && $req['result_low'] !== null) {
        EstimationTunnelService::sendEmailReport($email, $fullName, $req, [
            'ok'     => true,
            'low'    => (int)$req['result_low'],
            'median' => (int)$req['result_med'],
            'high'   => (int)$req['result_high'],
        ]);
    } else {
        // Confirmation générique
        EstimationTunnelService::sendConfirmationProspect($email, $fullName, $actionType);
    }
} catch (Throwable $e) {
    error_log('[convert.php] sendEmailReport error: ' . $e->getMessage());
    // Non bloquant
}

try {
    EstimationTunnelService::notifyAdvisor($actionType, [
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
        'phone'      => $phone ?: 'non fourni',
        'message'    => $message,
    ], $req);
} catch (Throwable $e) {
    error_log('[convert.php] notifyAdvisor error: ' . $e->getMessage());
    // Non bloquant
}

// ── Réponse ───────────────────────────────────────────────────
// Nom du conseiller dynamique (settings DB > constante > fallback générique)
if (function_exists('setting')) {
    $_advFirst = trim((string) setting('advisor_firstname', ''));
    $_advLast  = trim((string) setting('advisor_lastname',  ''));
    $_advName  = trim($_advFirst . ' ' . $_advLast);
}
if (empty($_advName)) {
    $_advName = defined('ADVISOR_NAME') && ADVISOR_NAME !== ''
        ? ADVISOR_NAME
        : (defined('APP_NAME') ? (string) preg_replace('/\s+Immobilier\b.*/iu', '', APP_NAME) : 'Votre conseiller');
}

$successMessages = [
    'email_report'    => 'Votre rapport d\'estimation a été envoyé à ' . htmlspecialchars($email) . '.',
    'contact_request' => $_advName . ' vous contactera dans les meilleurs délais.',
    'rdv_request'     => 'Votre demande de rendez-vous a bien été reçue. ' . $_advName . ' reviendra vers vous pour confirmer un créneau.',
];

echo json_encode([
    'ok'      => true,
    'message' => $successMessages[$actionType] ?? 'Votre demande a bien été reçue.',
], JSON_UNESCAPED_UNICODE);
