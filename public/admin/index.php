<?php
require_once __DIR__ . '/../../core/bootstrap.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Protège l'accès — redirige vers /admin/login si non connecté
Auth::requireAuth('/admin/login');

$module = isset($_GET['module']) ? (string) $_GET['module'] : 'construire';
$module = preg_replace('/[^a-z0-9_-]/', '', strtolower($module));

if ($module === '') {
    $module = 'construire';
}

$modulePath = __DIR__ . '/../../modules/' . $module . '/accueil.php';
$layoutPath = __DIR__ . '/../../admin/views/layout.php';

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
