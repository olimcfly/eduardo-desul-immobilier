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

// Maintenance publique : désactivée par défaut. N'agit que si PUBLIC_MAINTENANCE=1 dans .env
// ET que le fichier storage/cache/maintenance.flag existe.
if (($_ENV['PUBLIC_MAINTENANCE'] ?? '') === '1') {
    $__mf = STORAGE_PATH . '/cache/maintenance.flag';
    $__p = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
    if (is_file($__mf) && (!isset($_GET['preview']) || (string) $_GET['preview'] !== '1') && !in_array($__p, ['/health', '/healthz'], true)) {
        http_response_code(503);
        header('Retry-After: 3600');
        header('Content-Type: text/html; charset=UTF-8');
        $sn = htmlspecialchars((string) (defined('APP_NAME') ? APP_NAME : 'Site'));
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Maintenance — ', $sn, '</title></head><body style="margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,sans-serif;background:#f5f7fb;color:#0f172a"><main style="max-width:520px;padding:2rem;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,.1);text-align:center"><h1 style="font-size:1.35rem;margin:0 0 1rem">Le site est temporairement en maintenance</h1><p style="line-height:1.6;margin:0;color:#334155">Nous revenons très vite.</p></main></body></html>';
        exit;
    }
}

require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/helpers/helpers.php';
require_once ROOT_PATH . '/core/helpers/cms.php';
require_once ROOT_PATH . '/core/services/CmsPageDiscovery.php';
require_once ROOT_PATH . '/core/helpers/cms_public_page.php';
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

    ob_start();
    require $tplFile;
    $buffer = ob_get_clean();

    // Contenu publié cms_pages (hors accueil, géré dans home.php)
    if ($template !== 'pages/core/home') {
        $cmsSlug = cms_public_resolve_slug($template, get_defined_vars());
        if ($cmsSlug !== '' && $cmsSlug !== 'home') {
            $assignable = array_values(array_unique(array_merge(
                ['pageTitle', 'metaDesc', 'ogImage'],
                CmsPageDiscovery::editableKeysForFile($tplFile)
            )));
            $pick = [];
            $scope = get_defined_vars();
            foreach ($assignable as $ck) {
                if (array_key_exists($ck, $scope)) {
                    $pick[$ck] = (string) $scope[$ck];
                }
            }
            $cmsMerged = cms_public_merge($cmsSlug, $pick);
            if ($cmsMerged !== null) {
                foreach ($assignable as $ck) {
                    if (array_key_exists($ck, $cmsMerged)) {
                        ${$ck} = $cmsMerged[$ck];
                    }
                }
            }
            unset($cmsSlug, $assignable, $pick, $scope, $cmsMerged, $ck);
        }
    }

    // Gabarits qui font uniquement $pageContent = '...' sans echo : le buffer est vide.
    if ($buffer !== '') {
        $pageContent = $buffer;
    } elseif (!isset($pageContent)) {
        $pageContent = '';
    }

    $pageContent = replacePlaceholders($pageContent);
    if (isset($pageTitle)) { $pageTitle = replacePlaceholders((string)$pageTitle); }
    if (isset($metaDesc)) { $metaDesc = replacePlaceholders((string)$metaDesc); }

    $bodyClass = trim((string) ($bodyClass ?? ''));
    if ($template === 'pages/core/home') {
        $bodyClass = trim($bodyClass . ' page-home');
    } else {
        $bodyClass = trim($bodyClass . ' page-inner');
    }

    require ROOT_PATH . '/public/templates/layout.php';
}

// Routeur
$router = new Router();
require ROOT_PATH . '/config/routes.php';
$router->dispatch();
