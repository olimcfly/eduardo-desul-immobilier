<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /modules/seo/mots-cles/api.php */
declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../services/KeywordTracker.php';
require_once __DIR__ . '/../_legacy_guard.php';
seoLegacyGuard('modules/seo/ajax/check-keyword.php', '/modules/seo/mots-cles/api.php');

// ── Helpers JSON ──────────────────────────────────────────────────────────────
function jsonOk(mixed $data = null, string $message = 'OK'): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

function jsonError(string $message, int $code = 400): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message, 'data' => null]);
    exit;
}

// ── Auth ──────────────────────────────────────────────────────────────────────
if (!Auth::check()) {
    jsonError('Non autorisé', 401);
}

$userId  = (int)(Auth::user()['id'] ?? 0);
$tracker = new KeywordTracker(db(), $userId);

// ── Action ────────────────────────────────────────────────────────────────────
$action = isset($_GET['action'])
    ? preg_replace('/[^a-z_]/', '', (string)$_GET['action'])
    : '';

match ($action) {
    'save'     => handleSave($tracker),
    'delete'   => handleDelete($tracker),
    'refresh'  => handleRefresh($tracker),
    default    => jsonError('Action inconnue', 404),
};

// ── Handlers ──────────────────────────────────────────────────────────────────

function handleSave(KeywordTracker $tracker): never
{
    // CSRF
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        jsonError('Token CSRF invalide', 403);
    }

    // Validation
    $keyword    = trim((string)($_POST['keyword']    ?? ''));
    $targetUrl  = trim((string)($_POST['target_url'] ?? ''));
    $volume     = max(0, (int)($_POST['estimated_volume'] ?? 0));
    $difficulty = min(100, max(0, (int)($_POST['difficulty'] ?? 0)));

    if ($keyword === '') {
        jsonError('Le mot-clé est obligatoire');
    }
    if (strlen($keyword) > 190) {
        jsonError('Le mot-clé ne doit pas dépasser 190 caractères');
    }
    if ($targetUrl === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
        jsonError('L\'URL cible est invalide');
    }

    // Insert
    $id = $tracker->addKeyword($keyword, $targetUrl, $volume, $difficulty);

    jsonOk(['id' => $id], 'Mot-clé ajouté');
}

// ─────────────────────────────────────────────────────────────────────────────

function handleDelete(KeywordTracker $tracker): never
{
    // CSRF via header ou POST
    $token = $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? $_POST['csrf_token']
          ?? '';

    if (!verifyCsrf($token)) {
        jsonError('Token CSRF invalide', 403);
    }

    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonError('ID invalide');
    }

    $deleted = $tracker->deleteKeyword($id);

    if (!$deleted) {
        jsonError('Mot-clé introuvable ou non autorisé', 404);
    }

    jsonOk(['id' => $id], 'Mot-clé supprimé');
}

// ─────────────────────────────────────────────────────────────────────────────

function handleRefresh(KeywordTracker $tracker): never
{
    // CSRF
    $token = $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? $_POST['csrf_token']
          ?? '';

    if (!verifyCsrf($token)) {
        jsonError('Token CSRF invalide', 403);
    }

    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonError('ID invalide');
    }

    $metrics = fetchKeywordMetricsFromApi($id, $tracker);
    $newPosition = $metrics['position'];
    $searchVolume = $metrics['search_volume'];

    $tracker->updatePosition($id, $newPosition);

    if ($searchVolume !== null) {
        $stmt = db()->prepare(
            'UPDATE seo_keywords
             SET estimated_volume = :volume, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':volume' => $searchVolume,
            ':id' => $id,
            ':user_id' => (int)(Auth::user()['id'] ?? 0),
        ]);
    }

    jsonOk([
        'id'               => $id,
        'provider'         => $metrics['provider'],
        'current_position' => $newPosition,
        'estimated_volume' => $searchVolume,
        'last_checked_at'  => date('d/m/Y H:i'),
    ], 'Position mise à jour');
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retourne les métriques (position + volume) récupérées depuis le provider SEO
 * configuré dans les settings utilisateur.
 */
function fetchKeywordMetricsFromApi(int $keywordId, KeywordTracker $tracker): array
{
    $default = [
        'provider' => 'none',
        'position' => null,
        'search_volume' => null,
    ];

    $userId = (int)(Auth::user()['id'] ?? 0);
    if ($userId <= 0) {
        return $default;
    }

    $stmt = db()->prepare(
        'SELECT keyword, target_url
         FROM seo_keywords
         WHERE id = :id AND user_id = :user_id
         LIMIT 1'
    );
    $stmt->execute([
        ':id' => $keywordId,
        ':user_id' => $userId,
    ]);
    $keywordRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keywordRow) {
        return $default;
    }

    $keyword = trim((string)($keywordRow['keyword'] ?? ''));
    $targetUrl = trim((string)($keywordRow['target_url'] ?? ''));
    if ($keyword === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
        return $default;
    }

    $provider = resolveSeoProvider($userId);

    if ($provider === 'dataforseo') {
        $position = fetchDataForSeoPosition($keyword, $targetUrl, $userId);
        $searchVolume = fetchDataForSeoSearchVolume($keyword, $userId);

        return [
            'provider' => 'dataforseo',
            'position' => $position,
            'search_volume' => $searchVolume,
        ];
    }

    return $default;
}

function resolveSeoProvider(int $userId): string
{
    $dfsLogin = trim((string)setting('api_dataforseo_login', '', $userId));
    $dfsPassword = trim((string)setting('api_dataforseo_password', '', $userId));

    if ($dfsLogin !== '' && $dfsPassword !== '') {
        return 'dataforseo';
    }

    return 'none';
}

/**
 * Interroge DataForSEO SERP API et retourne la meilleure position
 * organique trouvée pour l'URL cible.
 */
function fetchDataForSeoPosition(string $keyword, string $targetUrl, int $userId): ?int
{
    $credentials = getDataForSeoCredentials($userId);
    $login = $credentials['login'];
    $password = $credentials['password'];

    if ($login === '' || $password === '') {
        return null;
    }

    $apiUrl = 'https://api.dataforseo.com/v3/serp/google/organic/live/regular';
    $payload = [[
        'language_name' => 'French',
        'location_name' => 'France',
        'keyword' => $keyword,
        'device' => 'desktop',
        'os' => 'windows',
        'depth' => 100,
    ]];

    $response = dataForSeoRequest($apiUrl, $payload, $login, $password);
    if (!is_array($response)) {
        return null;
    }

    $items = $response['tasks'][0]['result'][0]['items'] ?? null;
    if (!is_array($items)) {
        return null;
    }

    $targetHost = strtolower((string)parse_url($targetUrl, PHP_URL_HOST));
    if ($targetHost === '') {
        return null;
    }

    $bestPosition = null;
    foreach ($items as $item) {
        if (!is_array($item) || ($item['type'] ?? '') !== 'organic') {
            continue;
        }

        $itemUrl = (string)($item['url'] ?? '');
        $itemHost = strtolower((string)parse_url($itemUrl, PHP_URL_HOST));
        if ($itemHost === '') {
            continue;
        }

        if ($itemHost !== $targetHost && !str_ends_with($itemHost, '.' . $targetHost)) {
            continue;
        }

        $position = (int)($item['rank_absolute'] ?? 0);
        if ($position <= 0) {
            continue;
        }

        if ($bestPosition === null || $position < $bestPosition) {
            $bestPosition = $position;
        }
    }

    return $bestPosition;
}

function fetchDataForSeoSearchVolume(string $keyword, int $userId): ?int
{
    $credentials = getDataForSeoCredentials($userId);
    $login = $credentials['login'];
    $password = $credentials['password'];

    if ($login === '' || $password === '') {
        return null;
    }

    $apiUrl = 'https://api.dataforseo.com/v3/keywords_data/google_ads/search_volume/live';
    $payload = [[
        'keywords' => [$keyword],
        'language_name' => 'French',
        'location_name' => 'France',
    ]];

    $response = dataForSeoRequest($apiUrl, $payload, $login, $password);
    if (!is_array($response)) {
        return null;
    }

    $results = $response['tasks'][0]['result'] ?? null;
    if (!is_array($results)) {
        return null;
    }

    foreach ($results as $row) {
        if (!is_array($row)) {
            continue;
        }

        $rowKeyword = trim((string)($row['keyword'] ?? ''));
        if ($rowKeyword !== '' && mb_strtolower($rowKeyword) !== mb_strtolower($keyword)) {
            continue;
        }

        $value = $row['search_volume'] ?? $row['monthly_searches'] ?? null;
        if ($value === null) {
            continue;
        }

        $volume = (int)$value;
        if ($volume >= 0) {
            return $volume;
        }
    }

    return null;
}

function getDataForSeoCredentials(int $userId): array
{
    $login = trim((string)setting('api_dataforseo_login', '', $userId));
    $password = trim((string)setting('api_dataforseo_password', '', $userId));

    // Fallback env pour compatibilité avec anciennes installations.
    if ($login === '') {
        $login = trim((string)($_ENV['DATAFORSEO_LOGIN'] ?? getenv('DATAFORSEO_LOGIN') ?: ''));
    }
    if ($password === '') {
        $password = trim((string)($_ENV['DATAFORSEO_PASSWORD'] ?? getenv('DATAFORSEO_PASSWORD') ?: ''));
    }

    return ['login' => $login, 'password' => $password];
}

function dataForSeoRequest(string $apiUrl, array $payload, string $login, string $password): ?array
{
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($jsonPayload)) {
        return null;
    }

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => $login . ':' . $password,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_TIMEOUT => 20,
    ]);

    $rawResponse = curl_exec($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode < 200 || $statusCode >= 300 || !is_string($rawResponse) || $rawResponse === '') {
        return null;
    }

    $response = json_decode($rawResponse, true);
    if (!is_array($response)) {
        return null;
    }

    return $response;
}
