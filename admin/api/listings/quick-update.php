<?php
declare(strict_types=1);
/**
 * Même logique que l'ancien quick-update listings — bootstrap depuis admin/api/.
 */
ob_start();
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__, 3) . '/core/helpers/scraping_import_biens.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = [];
$raw = file_get_contents('php://input') ?: '';
$d = json_decode($raw, true);
if (is_array($d)) {
    $payload = $d;
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($payload['csrf_token'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expirée, rechargez la page.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($payload['id']) ? (int) $payload['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Identifiant invalide.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
$cols = scraping_import_biens_column_set($pdo);

$updates = [];

if (array_key_exists('publier_vitrine', $payload)) {
    $v = $payload['publier_vitrine'];
    if ($v === '' || $v === null || $v === 'null') {
        $val = null;
    } elseif (!in_array((int) $v, [0, 1], true)) {
        echo json_encode(['success' => false, 'message' => 'Publication vitrine invalide.'], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $val = (int) $v;
    }
    if (!isset($cols['publier_vitrine'])) {
        echo json_encode(['success' => false, 'message' => 'Colonne publier_vitrine absente. Exécutez la migration 039.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $updates['publier_vitrine'] = $val;
}

if (!empty($payload['statut']) && isset($cols['statut'])) {
    $st = strtolower(trim((string) $payload['statut']));
    $allowedStat = ['actif', 'pending', 'vendu', 'archive'];
    if (!in_array($st, $allowedStat, true)) {
        echo json_encode(['success' => false, 'message' => 'Statut non autorisé.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $updates['statut'] = $st;
}

if (!empty($payload['source']) && isset($cols['source'])) {
    $so = strtolower(trim((string) $payload['source']));
    if ($so !== 'own' && $so !== 'partage') {
        echo json_encode(['success' => false, 'message' => 'Type de propriété invalide.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $updates['source'] = $so;
}

if ($updates === []) {
    echo json_encode(['success' => false, 'message' => 'Aucun champ à modifier.'], JSON_UNESCAPED_UNICODE);
    exit;
}

foreach ($updates as $_k => $_v) {
    if (!preg_match('/^[a-z_]+$/', (string) $_k)) {
        echo json_encode(['success' => false, 'message' => 'Requête refusée.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$snap = $pdo->prepare('SELECT id, statut FROM biens WHERE id = ? LIMIT 1');
$snap->execute([$id]);
$before = $snap->fetch(PDO::FETCH_ASSOC);
if (!$before) {
    echo json_encode(['success' => false, 'message' => 'Bien introuvable.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$setParts = [];
$params = [];
foreach ($updates as $k => $v) {
    if (!isset($cols[$k])) {
        echo json_encode(['success' => false, 'message' => 'Ce champ est indisponible sur ce schéma.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $setParts[] = '`' . $k . '` = :' . $k;
    $params[':' . $k] = $v;
}

$params[':id'] = $id;

$sql = 'UPDATE biens SET ' . implode(', ', $setParts) . ' WHERE id = :id';

try {
    $st = $pdo->prepare($sql);
    $st->execute($params);
} catch (Throwable $e) {
    error_log('[listings quick-update] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Enregistré.'], JSON_UNESCAPED_UNICODE);
