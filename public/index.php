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
require ROOT_PATH . '/core/services/ModuleService.php';
require_once ROOT_PATH . '/includes/settings.php';

// Démarrer la session
Session::start();

// Helper : inclure une page dans le layout
function page(string $template, array $data = []): void
{
    extract($data);

    $user = Auth::user();
    $role = (string) ($user['role'] ?? 'guest');
    $parts = explode('/', $template);
    $moduleName = $parts[0] ?? '';
    if ($moduleName === 'pages' && isset($parts[1])) {
        $moduleName = $parts[1];
    }

    if (in_array($role, ['user', 'admin'], true) && !ModuleService::isEnabledForRole($moduleName, $role)) {
        ModuleService::renderUnavailablePage($moduleName);
        return;
    }

    if ($role === 'user') {
        ModuleService::trackUserPagePresence((int) ($user['id'] ?? 0), $_SERVER['REQUEST_URI'] ?? '/');
    }

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
    $pageContent = replacePlaceholders($pageContent);
    if (isset($pageTitle)) { $pageTitle = replacePlaceholders((string)$pageTitle); }
    if (isset($metaDesc)) { $metaDesc = replacePlaceholders((string)$metaDesc); }
    require ROOT_PATH . '/public/templates/layout.php';
}

// Routeur
$router = new Router();
require ROOT_PATH . '/config/routes.php';
$router->dispatch();
