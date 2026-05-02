<?php
/**
 * POST poi_action=llm_suggest — génère description et/ou mots-clés SEO (admin authentifié).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('csrfToken')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Session indisponible'], JSON_UNESCAPED_UNICODE);
    exit;
}
$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Jeton CSRF invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

$llmKind = (string) ($_POST['llm_kind'] ?? 'description');
if (!in_array($llmKind, ['description', 'seo'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'llm_kind = description|seo'], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$villeId = (int) ($_POST['ville_id'] ?? 0);
$quartierId = (int) ($_POST['quartier_id'] ?? 0);

if ($name === '' || $categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nom et catégorie requis pour générer.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Base indisponible'], JSON_UNESCAPED_UNICODE);
    exit;
}

$st = $pdo->prepare('SELECT name FROM guide_poi_categories WHERE id = ? AND is_active = 1 LIMIT 1');
$st->execute([$categoryId]);
$catRow = $st->fetch(PDO::FETCH_ASSOC);
$categoryName = trim((string) ($catRow['name'] ?? '')) ?: 'Commerce';

$cityName = 'Bordeaux Métropole';
$areaLabel = $cityName;

if ($quartierId > 0) {
    $qst = $pdo->prepare('SELECT q.nom AS qnom, v.nom AS vnom FROM quartiers q INNER JOIN villes v ON v.id = q.ville_id WHERE q.id = ? AND q.actif = 1 LIMIT 1');
    $qst->execute([$quartierId]);
    $qrow = $qst->fetch(PDO::FETCH_ASSOC);
    if (is_array($qrow)) {
        $areaLabel = trim((string) ($qrow['qnom'] ?? '')) . (isset($qrow['vnom']) ? ', ' . trim((string) $qrow['vnom']) : '');
        $cityName = trim((string) ($qrow['vnom'] ?? $cityName));
    }
} elseif ($villeId > 0) {
    $vst = $pdo->prepare('SELECT nom FROM villes WHERE id = ? AND actif = 1 LIMIT 1');
    $vst->execute([$villeId]);
    $vrow = $vst->fetch(PDO::FETCH_ASSOC);
    if (is_array($vrow) && $vrow['nom'] !== null && (string) $vrow['nom'] !== '') {
        $cityName = trim((string) $vrow['nom']);
        $areaLabel = $cityName;
    }
}

$root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
require_once $root . '/core/services/GuideLocalLlmService.php';

$clientKey = 'admin_' . hash('sha256', (string) (session_id() . ($_SESSION['user_id'] ?? 0)));

try {
    if ($llmKind === 'seo') {
        $out = GuideLocalLlmService::suggestSeoKeywordsForClient($clientKey, $name, $categoryName, $cityName);
    } else {
        $out = GuideLocalLlmService::describePoiForClient($clientKey, $name, $categoryName, $areaLabel);
    }
} catch (Throwable $e) {
    error_log('[poi-llm-suggest] ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Génération indisponible (clé API AI ou service).'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(
    [
        'ok'     => true,
        'text'   => $out['text'],
        'cached' => !empty($out['cached']),
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
