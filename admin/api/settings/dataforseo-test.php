<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/bootstrap.php';
Auth::requireAuth();

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$login    = trim($body['login']    ?? '');
$password = trim($body['password'] ?? '');

if (!$login || !$password) {
    echo json_encode(['success' => false, 'error' => 'Login ou mot de passe manquant.']);
    exit;
}

$credentials = base64_encode("{$login}:{$password}");

$response = file_get_contents('https://api.dataforseo.com/v3/appendix/user_data', false,
    stream_context_create([
        'http' => [
            'method'        => 'GET',
            'header'        => "Authorization: Basic {$credentials}\r\nContent-Type: application/json",
            'timeout'       => 10,
            'ignore_errors' => true,
        ],
    ])
);

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'Impossible de joindre l\'API DataForSEO.']);
    exit;
}

$data = json_decode($response, true);

// status_code 20000 = OK chez DataForSEO
if (($data['status_code'] ?? 0) === 20000) {
    $balance = $data['tasks'][0]['result'][0]['money']['balance'] ?? null;
    echo json_encode([
        'success' => true,
        'balance' => $balance !== null ? number_format((float)$balance, 2) : '?',
    ]);
} else {
    $msg = $data['status_message']
        ?? $data['tasks'][0]['status_message']
        ?? 'Identifiants incorrects.';
    echo json_encode(['success' => false, 'error' => htmlspecialchars($msg)]);
}
