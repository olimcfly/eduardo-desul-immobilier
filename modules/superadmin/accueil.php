<?php
require_once __DIR__ . '/../../core/services/ModuleService.php';

$action = isset($_GET['action']) ? preg_replace('/[^a-z_-]/', '', (string) $_GET['action']) : 'dashboard';

if ($action === 'toggle_module') {
    require __DIR__ . '/toggle_module.php';
    return;
}

if ($action === 'update_profile') {
    require __DIR__ . '/update_profile.php';
    return;
}

if ($action === 'toggle_user') {
    require __DIR__ . '/toggle_user.php';
    return;
}

if ($action === 'update_setting') {
    require __DIR__ . '/update_setting.php';
    return;
}

if ($action === 'page_request' || $action === 'poll_request' || $action === 'respond_request') {
    require __DIR__ . '/page_request.php';
    return;
}

$user = Auth::user();
if (!$user || ($user['role'] ?? '') !== 'superadmin') {
    http_response_code(403);
    echo '<div class="loading-spinner"><i class="fas fa-triangle-exclamation"></i>&nbsp;Accès réservé au superadmin.</div>';
    return;
}

$pageTitle = 'Superadmin';
$pageDescription = 'Pilotage global des modules, comptes et paramètres plateforme';

function renderContent(): void
{
    require __DIR__ . '/dashboard.php';
}
