<?php
/**
 * Bootstrap - Initialisation du CRM
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', ROOT_PATH . '/admin');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', ROOT_PATH . '/storage');
}
if (!defined('COMPONENTS_PATH')) {
    define('COMPONENTS_PATH', ADMIN_PATH . '/components');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ADMIN_PATH . '/config');
}

// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Création du dossier storage si inexistant
if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

// Initialisation du fichier JSON seen-modules
$seenModulesFile = STORAGE_PATH . '/seen-modules.json';
if (!file_exists($seenModulesFile)) {
    file_put_contents($seenModulesFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Autoload des composants
require_once COMPONENTS_PATH . '/ModuleWelcomePage.php';

// Helpers globaux
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/admin/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('e')) {
    function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
