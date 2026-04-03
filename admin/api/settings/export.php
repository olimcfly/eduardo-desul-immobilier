<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();

$user = Auth::user();
$userId = (int)($user['id'] ?? 0);
$format = strtolower((string)($_GET['format'] ?? 'json'));
$allowedFormats = ['json', 'csv'];

if (!in_array($format, $allowedFormats, true)) {
    http_response_code(400);
    echo 'Format d\'export invalide.';
    exit;
}

try {
    $stmt = db()->prepare(
        'SELECT setting_key, setting_value, setting_type, updated_at
         FROM settings
         WHERE user_id = ?
         ORDER BY setting_key ASC'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('settings export error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Erreur lors de l\'export.';
    exit;
}

$filename = 'settings-export-' . date('Ymd-His');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $out = fopen('php://output', 'wb');
    fputcsv($out, ['setting_key', 'setting_value', 'setting_type', 'updated_at']);
    foreach ($rows as $row) {
        fputcsv($out, [
            (string)($row['setting_key'] ?? ''),
            (string)($row['setting_value'] ?? ''),
            (string)($row['setting_type'] ?? ''),
            (string)($row['updated_at'] ?? ''),
        ]);
    }
    fclose($out);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '.json"');

echo json_encode([
    'exported_at' => gmdate('c'),
    'user_id' => $userId,
    'total' => count($rows),
    'settings' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
