<?php
declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../services/KeywordTracker.php';

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

    // Simuler un check de position
    // À remplacer par un vrai appel API (Google Search Console, DataForSEO…)
    $newPosition = fetchPositionFromApi($id, $tracker);

    $tracker->updatePosition($id, $newPosition);

    jsonOk([
        'id'               => $id,
        'current_position' => $newPosition,
        'last_checked_at'  => date('d/m/Y H:i'),
    ], 'Position mise à jour');
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Stub — brancher ici DataForSEO / GSC / SerpAPI
 * Retourne null si le mot-clé n'est pas classé
 */
function fetchPositionFromApi(int $keywordId, KeywordTracker $tracker): ?int
{
    // TODO: appel API réel
    // Exemple DataForSEO :
    // $client = new DataForSeoClient($_ENV['DATAFORSEO_LOGIN'], $_ENV['DATAFORSEO_PASS']);
    // return $client->getPosition($keyword, $targetUrl);

    // Pour l'instant on retourne null (pas classé)
    return null;
}
