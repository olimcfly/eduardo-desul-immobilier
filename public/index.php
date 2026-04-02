<?php
// ============================================================
// POINT D'ENTRÉE — Eduardo Desul Immobilier
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
define('ROOT', ROOT_PATH); // Alias pour compatibilité avec les anciens fichiers core

// Charger les variables d'environnement
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val, " \t\n\r\"'");
    }
}

// Config & Core
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/core/Database.php';
require ROOT_PATH . '/core/Session.php';
require ROOT_PATH . '/core/Auth.php';
require ROOT_PATH . '/core/Controller.php';
require ROOT_PATH . '/core/Model.php';
require ROOT_PATH . '/core/Router.php';
require ROOT_PATH . '/core/helpers/helpers.php';

// Démarrer la session
Session::start();

// Helper : inclure une page dans le layout
function page(string $template, array $data = []): void
{
    extract($data);
    $tplFile = ROOT_PATH . '/public/' . $template . '.php';
    if (!file_exists($tplFile)) {
        http_response_code(404);
        $errorFile = ROOT_PATH . '/public/pages/404.php';
        if (file_exists($errorFile)) require $errorFile;
        else echo '<h1>404 — Page introuvable</h1>';
        return;
    }
    ob_start();
    require $tplFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
}

// Routeur
$router = new Router();
require ROOT_PATH . '/config/routes.php';
$router->dispatch();
