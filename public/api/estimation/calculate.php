<?php
/**
 * API POST /api/estimation/calculate
 * Calcule une estimation et sauvegarde la requête.
 * Retourne JSON.
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

// ── Lecture du corps (JSON ou form-encoded) ───────────────────
$raw = file_get_contents('php://input');
if ($raw) {
    $input = json_decode($raw, true) ?? [];
} else {
    $input = $_POST;
}

// ── CSRF ──────────────────────────────────────────────────────
// verifyCsrf() envoie un 403 et die() si invalide
$sessionToken  = $_SESSION['csrf_token'] ?? '';
$suppliedToken = $input['csrf_token'] ?? '';
if ($sessionToken === '' || !hash_equals($sessionToken, $suppliedToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Session expirée. Rechargez la page.']);
    exit;
}

// ── Rate limiting ─────────────────────────────────────────────
$rateLimitKey = 'calc_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!EstimationTunnelService::checkRateLimit($rateLimitKey, 8)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Trop de requêtes. Réessayez dans une heure.']);
    exit;
}

// ── Validation ────────────────────────────────────────────────
$propertyType   = trim(strtolower((string)($input['property_type'] ?? '')));
$surface        = (float)($input['surface'] ?? 0);
$valuationMode  = trim((string)($input['valuation_mode'] ?? 'sold'));
$ville          = trim((string)($input['ville'] ?? ''));
$postalCode     = trim((string)($input['postal_code'] ?? ''));
$lat            = isset($input['lat']) ? (float)$input['lat'] : 0.0;
$lng            = isset($input['lng']) ? (float)$input['lng'] : 0.0;
$rooms          = isset($input['rooms']) && $input['rooms'] !== '' ? (int)$input['rooms'] : null;

$allowedTypes = ['appartement', 'maison', 'villa', 'terrain', 'local', 'immeuble'];
if (!in_array($propertyType, $allowedTypes, true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Type de bien invalide.']);
    exit;
}
if ($surface < 10 || $surface > 5000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Surface invalide (10–5000 m²).']);
    exit;
}
if ($ville === '' && $postalCode === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Veuillez indiquer une ville ou un code postal.']);
    exit;
}
if ($postalCode !== '' && (!is_numeric($postalCode) || strlen($postalCode) < 4)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Code postal invalide.']);
    exit;
}
if (!in_array($valuationMode, ['sold', 'live', 'both'], true)) {
    $valuationMode = 'sold';
}

// ── Calcul ────────────────────────────────────────────────────
try {
    EstimationTunnelService::ensureTables();

    $result = EstimationTunnelService::calculate([
        'property_type'  => $propertyType,
        'surface'        => $surface,
        'valuation_mode' => $valuationMode,
        'ville'          => $ville,
        'postal_code'    => $postalCode,
        'lat'            => $lat,
        'lng'            => $lng,
        'rooms'          => $rooms,
    ]);

    // ── Sauvegarde en base ────────────────────────────────────
    $requestId = EstimationTunnelService::saveRequest([
        'property_type'  => $propertyType,
        'surface'        => $surface,
        'valuation_mode' => $valuationMode,
        'ville'          => $ville,
        'postal_code'    => $postalCode,
        'lat'            => $lat ?: null,
        'lng'            => $lng ?: null,
        'rooms'          => $rooms,
    ], $result);

    // ── Formatage des prix pour le frontend ───────────────────
    $formatted = [];
    if ($result['ok']) {
        $formatted = [
            'low'    => number_format((int)$result['low'],    0, ',', ' ') . ' €',
            'median' => number_format((int)$result['median'], 0, ',', ' ') . ' €',
            'high'   => number_format((int)$result['high'],   0, ',', ' ') . ' €',
            'low_raw'    => (int)$result['low'],
            'median_raw' => (int)$result['median'],
            'high_raw'   => (int)$result['high'],
            'comparables_count' => (int)($result['comparables_count'] ?? 0),
            'reliability_score' => (int)($result['reliability_score'] ?? 0),
            'source'    => $result['source'] ?? 'unknown',
        ];
    }

    echo json_encode([
        'ok'         => $result['ok'],
        'status'     => $result['status'] ?? ($result['ok'] ? 'ok' : 'error'),
        'message'    => $result['message'] ?? '',
        'request_id' => $requestId,
        'data'       => $formatted,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('[calculate.php] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erreur interne. Veuillez réessayer.']);
}
