<?php
// ============================================================
// API — Charge une section du panneau Paramètres
// ============================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();
require_once ROOT_PATH . '/core/helpers/settings.php';

$section = preg_replace('/[^a-z_]/', '', $_GET['section'] ?? 'profil');

$file = __DIR__ . '/sections/' . $section . '.php';
if (!file_exists($file)) {
    http_response_code(404);
    echo '<p style="color:#e74c3c">Section introuvable.</p>';
    exit;
}

require $file;
