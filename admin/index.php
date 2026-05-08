<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();

// ── Charger la fonction de gestion de session ────────────────
require_once __DIR__ . '/session-helper.php';

// ── Démarrer la session ──────────────────────────────────────
startAdminSession();

// ── Vérifier authentification ────────────────────────────────
if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/auth/login.php');
}

// ── Définir les constantes ──────────────────────────────────
// Racine projet = dossier qui contient core/ (remonter jusqu’à trouver core/)
$adminDir = realpath(__DIR__) ?: __DIR__;
$rootCandidate = $adminDir;
for ($i = 0; $i < 8; $i++) {
    if (is_file($rootCandidate . '/core/AdminModulePlugin.php')) {
        break;
    }
    $parent = dirname($rootCandidate);
    if ($parent === $rootCandidate) {
        break;
    }
    $rootCandidate = $parent;
}
if (!is_file($rootCandidate . '/core/AdminModulePlugin.php')) {
    http_response_code(500);
    exit('Configuration invalide : ROOT_PATH introuvable (core/AdminModulePlugin.php manquant).');
}
define('ROOT_PATH', $rootCandidate);

// ── Charger les variables d'environnement ────────────────────
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
    }
}

// ── Charger la database config ──────────────────────────────
if (file_exists(ROOT_PATH . '/core/config/database.php')) {
    require_once ROOT_PATH . '/core/config/database.php';
}

// ── Charger les helpers essentiels ───────────────────────────
if (file_exists(ROOT_PATH . '/core/helpers/helpers.php')) {
    require_once ROOT_PATH . '/core/helpers/helpers.php';
}

// ── Config (constantes APP_*, ADVISOR_*, etc.) ────────────────
if (file_exists(ROOT_PATH . '/core/config/config.php')) {
    require_once ROOT_PATH . '/core/config/config.php';
}

// ── Paramètres DB — requis pour le module profil et le layout
if (file_exists(ROOT_PATH . '/includes/settings.php')) {
    require_once ROOT_PATH . '/includes/settings.php';
}

// ── Définir les constantes manquantes ────────────────────────
if (!defined('ADVISOR_NAME')) {
    define('ADVISOR_NAME', 'Pascal Hamm');
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Pascal Hamm Immobilier');
}

$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';

// ── Charger Auth avant les routes spéciales qui en dépendent ──
if (file_exists(ROOT_PATH . '/core/Auth.php')) {
    require_once ROOT_PATH . '/core/Auth.php';
}

if (preg_match('#/admin/google-business/callback/?$#', $requestPath)) {
    require ROOT_PATH . '/admin/google-business/callback.php';
    exit;
}

// ── Charger les classes core essentielles ───────────────────
if (file_exists(ROOT_PATH . '/core/Session.php')) {
    require_once ROOT_PATH . '/core/Session.php';
}

if (file_exists(ROOT_PATH . '/core/TenantContext.php')) {
    require_once ROOT_PATH . '/core/TenantContext.php';
}
if (file_exists(ROOT_PATH . '/core/helpers/saas_session.php')) {
    require_once ROOT_PATH . '/core/helpers/saas_session.php';
}
if (function_exists('db')) {
    try {
        saas_ensure_tenant_session(db());
    } catch (Throwable $e) {
        error_log('saas_ensure_tenant_session: ' . $e->getMessage());
    }
}
if (($_SESSION['user_role'] ?? '') === 'superadmin' && isset($_GET['tenant_scope'])) {
    $ts = (string) $_GET['tenant_scope'];
    if ($ts === 'all') {
        TenantContext::setViewAllOrganizations(true);
    } elseif ($ts === 'active') {
        TenantContext::setViewAllOrganizations(false);
    }
}

// ── Charger les services ────────────────────────────────────
if (file_exists(ROOT_PATH . '/core/services/LeadService.php')) {
    require_once ROOT_PATH . '/core/services/LeadService.php';
}

if (file_exists(ROOT_PATH . '/core/services/ConversionTrackingService.php')) {
    require_once ROOT_PATH . '/core/services/ConversionTrackingService.php';
}

if (file_exists(ROOT_PATH . '/core/services/EmailSequenceService.php')) {
    require_once ROOT_PATH . '/core/services/EmailSequenceService.php';
}

// ── Créer une classe Auth minimale si nécessaire ───────────
if (!class_exists('Auth')) {
    class Auth {
        public static function user() {
            return [
                'id' => (int) ($_SESSION['user_id'] ?? 0),
                'name' => $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Utilisateur',
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['user_role'] ?? 'user',
            ];
        }
    }
}

// ── Déterminer le module demandé ────────────────────────────
$module = $_GET['module'] ?? 'dashboard';
$module = preg_replace('/[^a-z0-9_-]/i', '', $module);
// Rétro-compat : ancien nom du module annuaire
if ($module === 'guide-local-crm') {
    $q = $_GET;
    $q['module'] = 'annuaire-local';
    header('Location: ' . (function_exists('admin_url') ? admin_url($q) : ('/admin/?' . http_build_query($q))), true, 301);
    exit;
}

// ── Métadonnées « plugin » du module actif (voir modules/<slug>/admin_plugin.php) ──
require_once ROOT_PATH . '/core/AdminModulePlugin.php';
$adminModulePlugin = AdminModulePlugin::tryFromModuleDir(ROOT_PATH . '/modules/' . $module) ?? null;

// ── Préparer les données par défaut ──────────────────────────
$pageTitle = 'Tableau de bord';
$stats = [
    'biens' => 0,
    'leads' => 0,
];

// ── Charger le module ou le dashboard ────────────────────────
$modulePath = ROOT_PATH . '/modules/' . $module . '/accueil.php';
$moduleLoaded = false;

if (is_file($modulePath)) {
    try {
        require $modulePath;
        $moduleLoaded = function_exists('renderContent');
    } catch (Throwable $e) {
        // Si le module échoue, utiliser le dashboard
        error_log('Module error: ' . $e->getMessage());
        $moduleLoaded = false;
    }
}

// Si pas de module chargé, afficher le dashboard
if (!$moduleLoaded) {
    $module = 'dashboard';
    $pageTitle = 'Tableau de bord';

    // Créer renderContent pour le dashboard (si pas déjà défini)
    if (!function_exists('renderContent')) {
        function renderContent() {
            echo '<!-- Dashboard renderContent called -->';
            require __DIR__ . '/views/dashboard/index.php';
        }
    }
}

// ── Charger le layout principal ──────────────────────────────
require __DIR__ . '/views/layout.php';
?>
