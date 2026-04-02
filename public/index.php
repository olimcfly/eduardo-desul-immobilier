<?php
// ============================================================
// POINT D'ENTRÉE UNIQUE
// ============================================================

define('ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

// Chargement configuration
require ROOT . '/config/config.php';
require ROOT . '/config/database.php';

// Chargement core
require ROOT . '/core/helpers/helpers.php';
require ROOT . '/core/helpers/sanitize.php';
require ROOT . '/core/helpers/auth.php';
require ROOT . '/core/Model.php';
require ROOT . '/core/Controller.php';
require ROOT . '/core/Router.php';

// Chargement modèles
foreach (glob(ROOT . '/app/Models/*.php') as $model) {
    require $model;
}

// Chargement contrôleurs
foreach (glob(ROOT . '/app/Controllers/*.php') as $ctrl) {
    require $ctrl;
}

// Démarrage session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Routeur
$router = new Router();
require ROOT . '/routes/web.php';
$router->dispatch();
