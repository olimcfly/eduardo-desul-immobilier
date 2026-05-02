<?php
declare(strict_types=1);

require_once __DIR__ . '/../../session-helper.php';
startAdminSession();

header('Content-Type: application/json; charset=utf-8');

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifie']);
    exit;
}

$script = dirname(__DIR__, 3) . '/scripts/checklist-agent-worker.php';
if (!is_file($script)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Worker introuvable']);
    exit;
}

$phpBin = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
$cmd = sprintf(
    '%s %s --max-jobs=3 > /dev/null 2>&1 &',
    escapeshellarg($phpBin),
    escapeshellarg($script)
);

@exec($cmd, $output, $exitCode);

if ($exitCode !== 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Echec du dispatch', 'exit_code' => $exitCode]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Worker lance en arriere-plan',
]);
