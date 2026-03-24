<?php
/**
 * ============================================================
 * CORE API ROUTER - Point d'entrée central AJAX
 * /admin/api/router.php
 *
 * Tous les modules sont désormais enregistrés ici.
 * ============================================================
 */

if (!defined('ADMIN_ACCESS')) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        if (defined('APP_ENV') && APP_ENV === 'production') {
            http_response_code(403);
            exit(json_encode(['success' => false, 'message' => 'Accès interdit']));
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/includes/init.php';

// ── Fonction CSRF ───────────────────────────────────────────
if (!function_exists('validateCsrfToken')) {
    function validateCsrfToken($token) {
        return !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// ── CSRF (sauf GET) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $csrfToken = $_POST['csrf_token']
                 ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                 ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
        exit;
    }
}

// ── ROUTING ──────────────────────────────────────────────────
$module = $_GET['module'] ?? $_POST['module'] ?? '';
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

/**
 * Carte des modules -> fichiers handlers
 * Chemin : /admin/core/handlers/{module}.php
 */
$handlersDir = dirname(__DIR__) . '/core/handlers/';

$moduleMap = [
    // ── CONTENT ──────────────────────────
    'articles'    => $handlersDir . 'articles.php',
    'pages'       => $handlersDir . 'pages.php',
    'captures'    => $handlersDir . 'captures.php',
    'secteurs'    => $handlersDir . 'secteurs.php',

    // ── IMMOBILIER ───────────────────────
    'biens'       => $handlersDir . 'biens.php',
    'rdv'         => $handlersDir . 'rdv.php',

    // ── MARKETING / CRM ──────────────────
    'leads'       => $handlersDir . 'leads.php',

    // ── SEO ──────────────────────────────
    'seo'              => $handlersDir . 'seo.php',
    'seo-semantic'     => $handlersDir . 'seo-semantic.php',
    'local-onboarding' => $handlersDir . 'local-onboarding.php',

    // ── SOCIAL / GMB ─────────────────────
    'gmb'         => $handlersDir . 'gmb.php',
    'gmb-posts'   => $handlersDir . 'gmb-posts.php',

    // ── SYSTEM ───────────────────────────
    'media'       => $handlersDir . 'media.php',
    'settings'    => $handlersDir . 'settings.php',
    'maintenance' => $handlersDir . 'maintenance.php',

    // ── IA (dispatcher vers /core/ai/) ───
    'ai'          => $handlersDir . 'ai.php',
];

// ── VALIDATION ───────────────────────────────────────────────
if (empty($module) || !array_key_exists($module, $moduleMap)) {
    http_response_code(400);
    echo json_encode([
        'success'           => false,
        'message'           => 'Module invalide ou manquant : ' . htmlspecialchars($module),
        'available_modules' => array_keys($moduleMap),
    ]);
    exit;
}

$handlerFile = $moduleMap[$module];

if (!file_exists($handlerFile)) {
    http_response_code(501);
    echo json_encode([
        'success' => false,
        'message' => "Handler '{$module}' non encore implémenté",
    ]);
    exit;
}

// ── DISPATCH ─────────────────────────────────────────────────
define('CURRENT_MODULE', $module);
define('CURRENT_ACTION', $action);

require_once $handlerFile;
