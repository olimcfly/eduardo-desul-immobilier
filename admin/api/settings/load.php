<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
Auth::requireAuth();
require_once ROOT_PATH . '/core/helpers/settings.php';

$section = preg_replace('/[^a-z]/', '', (string)($_GET['section'] ?? 'profil'));
$allowed = ['profil', 'site', 'zone', 'api', 'notif', 'smtp', 'securite', 'danger'];

if (!in_array($section, $allowed, true)) {
    http_response_code(400);
    echo '<div class="settings-error">Section invalide.</div>';
    exit;
}

$sectionFile = __DIR__ . '/sections/' . $section . '.php';

if (!is_file($sectionFile)) {
    http_response_code(404);
    echo '<div class="settings-error">Section introuvable.</div>';
    exit;
}

require $sectionFile;
