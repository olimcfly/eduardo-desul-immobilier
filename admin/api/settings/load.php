<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
Auth::requireAuth();

$section = preg_replace('/[^a-z]/', '', (string)($_GET['section'] ?? 'profil'));

$allowed = ['profil', 'site', 'zone', 'api', 'notif', 'smtp', 'securite', 'danger'];

if (!in_array($section, $allowed, true)) {
    http_response_code(400);
    echo '<p style="color:#e74c3c;padding:20px">Section invalide.</p>';
    exit;
}

$file = __DIR__ . '/sections/' . $section . '.php';

if (!file_exists($file)) {
    http_response_code(404);
    echo '<p style="color:#e74c3c;padding:20px">Section introuvable.</p>';
    exit;
}

require $file;
