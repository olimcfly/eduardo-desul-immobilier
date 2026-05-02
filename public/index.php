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
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim((string) $key);
        if ($key === '') {
            continue;
        }

        $value = trim((string) $value);
        $firstChar = $value[0] ?? '';
        $lastChar = $value !== '' ? substr($value, -1) : '';
        if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

// Config & Core
require ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/helpers/helpers.php';
require_once ROOT_PATH . '/core/services/PageContentService.php';
require_once ROOT_PATH . '/core/helpers/cms.php';
require_once ROOT_PATH . '/core/services/ModuleService.php';
require_once ROOT_PATH . '/core/services/LeadService.php';
require_once ROOT_PATH . '/core/services/DvfEstimatorService.php';
require_once ROOT_PATH . '/core/services/DvfImportService.php';
require_once ROOT_PATH . '/includes/settings.php';
require_once ROOT_PATH . '/core/services/SectionRenderer.php';

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
    $GLOBALS['__cms_page_text_slug'] = PageContentService::slugFromTemplate($template);
    ob_start();
    require $tplFile;
    $buffer = ob_get_clean();
    // Gabarits qui font uniquement $pageContent = '...' (sans echo) : le buffer est vide ;
    // ne pas écraser la variable déjà remplie par le template inclus.
    if ($buffer !== '') {
        $pageContent = $buffer;
    } elseif (!isset($pageContent)) {
        $pageContent = '';
    }
    $pageContent = replacePlaceholders($pageContent);
    if (isset($pageTitle)) { $pageTitle = replacePlaceholders((string)$pageTitle); }
    if (isset($metaDesc)) { $metaDesc = replacePlaceholders((string)$metaDesc); }
    require ROOT_PATH . '/public/templates/layout.php';
}

// Routeur
$router = new Router();
require ROOT_PATH . '/config/routes.php';
$router->dispatch();
