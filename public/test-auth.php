<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    die('❌ Session non active');
}

$results = [];

// Session
$results[] = [
    'label' => 'Session active',
    'value' => session_status() === PHP_SESSION_ACTIVE ? '✅ OUI' : '❌ NON',
];

// Nom session
$results[] = [
    'label' => 'Nom session',
    'value' => session_name(),
];

// ID session
$results[] = [
    'label' => 'ID session',
    'value' => session_id() !== '' ? '✅ ' . session_id() : '❌ vide',
];

// Classe Auth
$results[] = [
    'label' => 'Classe Auth',
    'value' => class_exists('Auth') ? '✅ OK' : '❌ Introuvable',
];

// Données session
$results[] = [
    'label' => '$_SESSION',
    'value' => !empty($_SESSION) ? '✅ contient des données' : '⚠️ vide',
];

// Cookie session
$cookieName = session_name();
$results[] = [
    'label' => 'Cookie session reçu',
    'value' => isset($_COOKIE[$cookieName]) ? '✅ OUI' : '⚠️ NON (normal au 1er chargement)',
];

// Simulation login simple
if (!isset($_SESSION['auth_test_counter'])) {
    $_SESSION['auth_test_counter'] = 1;
} else {
    $_SESSION['auth_test_counter']++;
}

$results[] = [
    'label' => 'Persistance session',
    'value' => 'Compteur = ' . (int) $_SESSION['auth_test_counter'],
];

// Test utilisateur simulé
if (isset($_GET['login']) && $_GET['login'] === '1') {
    $_SESSION['user_id'] = 999;
    $_SESSION['user_email'] = 'test-auth@example.com';
    $_SESSION['user_name'] = 'Test Auth';
    $_SESSION['logged_in'] = true;
}

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_name'], $_SESSION['logged_in']);
}

$isLogged = !empty($_SESSION['logged_in']);

$results[] = [
    'label' => 'Utilisateur connecté simulé',
    'value' => $isLogged ? '✅ OUI' : '❌ NON',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Auth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 32px;
            background: #f6f7fb;
            color: #1f2937;
        }
        .wrap {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .actions a {
            display: inline-block;
            margin-right: 12px;
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            background: #111827;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        td:first-child {
            width: 280px;
            font-weight: bold;
        }
        pre {
            background: #0f172a;
            color: #e5e7eb;
            padding: 16px;
            border-radius: 10px;
            overflow: auto;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Test authentification</h1>
        <div class="actions">
            <a href="/test-auth">Recharger</a>
            <a href="/test-auth?login=1">Simuler login</a>
            <a href="/test-auth?logout=1">Simuler logout</a>
        </div>
    </div>

    <div class="card">
        <table>
            <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $row['value'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Session brute</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
</div>
</body>
</html>