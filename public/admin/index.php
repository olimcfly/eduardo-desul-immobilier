<?php
require_once '../../core/bootstrap.php';
require_once '../../core/Database.php';
require_once '../../core/services/ModuleService.php';

// Protège l'accès — redirige vers /admin/login si non connecté
Auth::requireAuth('/admin/login');

$module = isset($_GET['module']) ? $_GET['module'] : 'construire';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Sécurisation : n'accepter que des noms de modules valides
$module = preg_replace('/[^a-z0-9_-]/', '', $module);

$user = Auth::user();
$role = (string) ($user['role'] ?? 'user');

if (!ModuleService::isEnabledForRole($module, $role)) {
    ModuleService::renderUnavailablePage($module);
    exit;
}

$modulePath = "../../modules/{$module}/accueil.php";

if (file_exists($modulePath)) {
    require $modulePath;
} else {
    header('Location: /admin/');
    exit;
}
