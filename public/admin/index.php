<?php
require_once '../../core/bootstrap.php';
require_once '../../core/services/ModuleService.php';

$loginRedirect = '/admin/login';

// Protège l'accès — redirige vers /admin/login si non connecté.
if (!Auth::check()) {
    if (APP_DEBUG) {
        error_log('[admin/index] Accès refusé: session absente ou expirée. URI=' . ($_SERVER['REQUEST_URI'] ?? 'n/a'));
    }
    Session::flash('error', 'Connectez-vous pour accéder à cette page.');
    header('Location: ' . $loginRedirect);
    exit;
}

$user = Auth::user();
if ($user === null) {
    if (APP_DEBUG) {
        error_log('[admin/index] Auth::user() a retourné null alors que Auth::check() est true.');
    }
    Session::flash('error', 'Votre session est invalide. Merci de vous reconnecter.');
    header('Location: ' . $loginRedirect);
    exit;
}

$module = isset($_GET['module']) ? (string) $_GET['module'] : 'dashboard';
$module = preg_replace('/[^a-z0-9_-]/', '', strtolower($module));
if ($module === '') {
    $module = 'dashboard';
}

$role = (string) ($user['role'] ?? 'user');
if (!ModuleService::isEnabledForRole($module, $role)) {
    if (APP_DEBUG) {
        error_log(sprintf('[admin/index] Module non autorisé: module=%s role=%s user_id=%s', $module, $role, (string) ($user['id'] ?? 'n/a')));
    }
    ModuleService::renderUnavailablePage($module);
    exit;
}

$modulePath = __DIR__ . "/../../modules/{$module}/accueil.php";
if (!is_file($modulePath)) {
    if (APP_DEBUG) {
        error_log('[admin/index] Module introuvable: ' . $modulePath . '. Fallback sur construire.');
    }
    $module = 'construire';
    $modulePath = __DIR__ . '/../../modules/construire/accueil.php';
}

require_once $modulePath;
if (!function_exists('renderContent')) {
    throw new RuntimeException('Le module "' . $module . '" ne définit pas renderContent().');
}

$layoutPath = __DIR__ . '/../../admin/views/layout.php';
if (!is_file($layoutPath)) {
    throw new RuntimeException('Le layout admin est introuvable: ' . $layoutPath);
}

require_once $layoutPath;
