<?php
require_once '../../core/bootstrap.php';
require_once '../../core/Database.php';
require_once '../../core/services/ModuleService.php';

// Protège l'accès — redirige vers /admin/login si non connecté
Auth::requireAuth('/admin/login');

$module = isset($_GET['module']) ? (string) $_GET['module'] : 'construire';
$module = preg_replace('/[^a-z0-9_-]/', '', strtolower($module));

if ($module === '') {
    $module = 'construire';
}

$user = Auth::user();
$role = (string) ($user['role'] ?? 'user');

if (!ModuleService::isEnabledForRole($module, $role)) {
    ModuleService::renderUnavailablePage($module);
    exit;
}

$user = Auth::user();
$role = (string) ($user['role'] ?? 'user');

if (!ModuleService::isEnabledForRole($module, $role)) {
    ModuleService::renderUnavailablePage($module);
    exit;
}

$user = Auth::user();
$role = (string) ($user['role'] ?? 'user');

if (!ModuleService::isEnabledForRole($module, $role)) {
    ModuleService::renderUnavailablePage($module);
    exit;
}

$modulePath = "../../modules/{$module}/accueil.php";

if (!is_file($modulePath)) {
    http_response_code(404);
    $module = 'construire';
    $modulePath = __DIR__ . '/../../modules/construire/accueil.php';
}

require_once $modulePath;

if (!function_exists('renderContent')) {
    throw new RuntimeException('Le module "' . $module . '" ne définit pas renderContent().');
}

require_once $layoutPath;
