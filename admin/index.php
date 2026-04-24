<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ── Charger la fonction de gestion de session ────────────────
require_once __DIR__ . '/session-helper.php';

// ── Démarrer la session ──────────────────────────────────────
startAdminSession();

// ── Vérifier authentification ────────────────────────────────
if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/login');
}

// ── Définir les constantes ──────────────────────────────────
// Résoudre le symlink si on y accède via /public/admin
$adminDir = realpath(__DIR__);
define('ROOT_PATH', dirname($adminDir));

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

// ── Définir les fonctions manquantes si nécessaire ───────────
if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        return $default;
    }
}

if (!function_exists('replacePlaceholders')) {
    function replacePlaceholders(string $text): string {
        return $text;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string {
        return $path;
    }
}

if (!function_exists('e')) {
    function e(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_ia_status')) {
    function get_ia_status(): string {
        return 'inactive';
    }
}

// ── Définir les constantes manquantes ────────────────────────
if (!defined('ADVISOR_NAME')) {
    define('ADVISOR_NAME', 'Eduardo Desul');
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Eduardo Desul Immobilier');
}

// ── Charger les classes core essentielles ───────────────────
if (file_exists(ROOT_PATH . '/core/Session.php')) {
    require_once ROOT_PATH . '/core/Session.php';
}

if (file_exists(ROOT_PATH . '/core/Auth.php')) {
    require_once ROOT_PATH . '/core/Auth.php';
}

// ── Créer une classe Auth minimale si nécessaire ───────────
if (!class_exists('Auth')) {
    class Auth {
        public static function user() {
            return [
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

// ── Préparer les données par défaut ──────────────────────────
$pageTitle = 'Tableau de bord';
$stats = [
    'biens' => 0,
    'leads' => 0,
];

// ── Charger le module ou le dashboard ────────────────────────
$modulePath = ROOT_PATH . '/modules/' . $module . '/accueil.php';
$moduleLoaded = false;

if (is_file($modulePath) && $module !== 'dashboard') {
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
            global $stats;
            require __DIR__ . '/views/dashboard/index.php';
        }
    }
}

// ── Charger le layout principal ──────────────────────────────
require __DIR__ . '/views/layout.php';
?>
