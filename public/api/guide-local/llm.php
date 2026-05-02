<?php
/**
 * POST /api/guide-local/llm.php
 * Génération LLM guide local (cache + double rate limit : requêtes API / générations Anthropic).
 *
 * Auth : en-tête X-Guide-Local-Llm-Key ou Authorization: Bearer … = GUIDE_LOCAL_LLM_API_KEY (obligatoire hors APP_ENV=development).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Guide-Local-Llm-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__, 3) . '/core/services/GuideLocalLlmService.php';

$raw = file_get_contents('php://input');
$input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
if (!is_array($input)) {
    $input = [];
}

$appEnv = strtolower((string) ($_ENV['APP_ENV'] ?? 'production'));
$secret = trim((string) ($_ENV['GUIDE_LOCAL_LLM_API_KEY'] ?? ''));
$supplied = trim((string) ($input['api_key'] ?? ''));
if ($supplied === '') {
    $supplied = trim((string) ($_SERVER['HTTP_X_GUIDE_LOCAL_LLM_KEY'] ?? ''));
}
if ($supplied === '') {
    $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
        $supplied = trim($m[1]);
    }
}

if ($secret !== '') {
    if ($supplied === '' || !hash_equals($secret, $supplied)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Clé API invalide ou manquante'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} elseif ($appEnv !== 'development') {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'Endpoint désactivé : définissez GUIDE_LOCAL_LLM_API_KEY dans .env (obligatoire en production).',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $clientKey = GuideLocalLlmGuardService::clientKeyFromRequest();
    GuideLocalLlmGuardService::assertApiAllowed($clientKey);
} catch (RuntimeException $e) {
    if ($e->getCode() === 429) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
    throw $e;
}

$action = strtolower(preg_replace('/[^a-z_]/', '', (string) ($input['action'] ?? '')));

function gl_llm_clamp(string $s, int $max): string
{
    $s = trim($s);

    return mb_strlen($s, 'UTF-8') > $max ? mb_substr($s, 0, $max, 'UTF-8') : $s;
}

try {
    if ($action === 'describe_district') {
        $district = gl_llm_clamp((string) ($input['district_name'] ?? ''), 120);
        $city = gl_llm_clamp((string) ($input['city_name'] ?? 'Bordeaux'), 120);
        if ($district === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'district_name requis'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $out = GuideLocalLlmService::describeDistrictForClient($clientKey, $district, $city);
    } elseif ($action === 'describe_poi') {
        $poi = gl_llm_clamp((string) ($input['poi_name'] ?? ''), 200);
        $cat = gl_llm_clamp((string) ($input['category_name'] ?? ''), 120);
        $area = gl_llm_clamp((string) ($input['area_label'] ?? ''), 200);
        if ($poi === '' || $cat === '' || $area === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'poi_name, category_name et area_label requis'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $out = GuideLocalLlmService::describePoiForClient($clientKey, $poi, $cat, $area);
    } else {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'action inconnue (describe_district | describe_poi)',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(
        [
            'ok'      => true,
            'text'    => $out['text'],
            'cached'  => !empty($out['cached']),
            'action'  => $action,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (RuntimeException $e) {
    if ($e->getCode() === 429) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
    error_log('[guide-local/llm] ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[guide-local/llm] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
}
