<?php
/**
 * INDEX.PHP - PAGE D'ACCUEIL
 */
define('ROOT_PATH', __DIR__);
define('FRONT_ROUTER', true);

// Maintenance
if (file_exists(__DIR__ . '/includes/maintenance-check.php')) {
    require_once __DIR__ . '/includes/maintenance-check.php';
}

// Définir les valeurs par défaut SANS écraser ce qui existe
$_GET['type'] ??= 'cms';
$_GET['slug'] ??= 'accueil';

$routerPath = __DIR__ . '/front/page.php';
if (file_exists($routerPath)) {
    require $routerPath;
} else {
    header('Location: /accueil');
    exit;
}