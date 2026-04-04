<?php
// ============================================================
// API — Export des données utilisateur
// ============================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();

$format = preg_replace('/[^a-z]/', '', (string)($_GET['format'] ?? 'json'));
if (!in_array($format, ['json', 'csv'], true)) {
    $format = 'json';
}

$user   = Auth::user();
$userId = (int)($user['id'] ?? 0);

if ($userId <= 0) {
    http_response_code(403);
    exit('Accès refusé.');
}

$pdo  = db();
$data = [];

// ── Settings ─────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        'SELECT setting_key, setting_value, setting_type, setting_group, updated_at
         FROM settings WHERE user_id = ? ORDER BY setting_group, setting_key'
    );
    $stmt->execute([$userId]);
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Masquer les valeurs chiffrées
    foreach ($settings as &$s) {
        if ((int)($s['is_encrypted'] ?? 0) === 1) {
            $s['setting_value'] = '***';
        }
    }
    unset($s);
    $data['settings'] = $settings;
} catch (Throwable) {
    $data['settings'] = [];
}

// ── Contacts ─────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY id');
    $stmt->execute([$userId]);
    $data['contacts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable) {
    $data['contacts'] = [];
}

// ── Biens ────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare('SELECT * FROM biens WHERE user_id = ? ORDER BY id');
    $stmt->execute([$userId]);
    $data['biens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable) {
    $data['biens'] = [];
}

$filename = 'export_' . date('Ymd_His');

// ── JSON ─────────────────────────────────────────────────────
if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── CSV (settings uniquement) ────────────────────────────────
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

// Settings
fputcsv($out, ['section', 'clé', 'valeur', 'type', 'modifié le'], ';');
foreach ($data['settings'] as $row) {
    fputcsv($out, [
        $row['setting_group'] ?? '',
        $row['setting_key'],
        $row['setting_value'],
        $row['setting_type'],
        $row['updated_at'] ?? '',
    ], ';');
}

// Contacts
if (!empty($data['contacts'])) {
    fputcsv($out, [], ';');
    fputcsv($out, ['--- CONTACTS ---'], ';');
    $cols = array_keys($data['contacts'][0]);
    fputcsv($out, $cols, ';');
    foreach ($data['contacts'] as $row) {
        fputcsv($out, array_values($row), ';');
    }
}

// Biens
if (!empty($data['biens'])) {
    fputcsv($out, [], ';');
    fputcsv($out, ['--- BIENS ---'], ';');
    $cols = array_keys($data['biens'][0]);
    fputcsv($out, $cols, ';');
    foreach ($data['biens'] as $row) {
        fputcsv($out, array_values($row), ';');
    }
}

fclose($out);
exit;
