<?php
/**
 * 🔧 CONFIG INSTANCE - EXEMPLE
 * /config/config.php
 *
 * Copier ce fichier en config/config.php et remplir les valeurs
 * À MODIFIER pour chaque duplication
 */

// ═══════════════════════════════════════════════════════════
// 📌 INSTANCE-SPECIFIC (À CHANGER POUR CHAQUE DUPLICATION)
// ═══════════════════════════════════════════════════════════

define('INSTANCE_ID', 'mon-instance');                  // Identifiant unique
define('SITE_TITLE', 'Mon Site Immobilier');
define('SITE_DOMAIN', 'mon-domaine.fr');
define('ADMIN_EMAIL', 'admin@mon-domaine.fr');

define('DB_HOST', 'localhost');
define('DB_NAME', 'ma_base_de_donnees');
define('DB_USER', 'mon_utilisateur_db');
define('DB_PASS', 'mon_mot_de_passe_db');

// ═══════════════════════════════════════════════════════════
// 🤖 CONFIGURATION IA (SEO, Génération contenu)
// ═══════════════════════════════════════════════════════════

// OpenAI API Key (GPT-4)
define('OPENAI_API_KEY', 'sk-proj-VOTRE_CLE_OPENAI');

// Claude (Anthropic) API Key - Utilisé en priorité
define('ANTHROPIC_API_KEY', 'sk-ant-VOTRE_CLE_ANTHROPIC');

// ═══════════════════════════════════════════════════════════
// 🔧 CHEMINS (Automatique - Ne pas modifier)
// ═══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(dirname(__FILE__)));
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ═══════════════════════════════════════════════════════════
// 🌐 URLS (Auto-détection du domaine + fallback)
// ═══════════════════════════════════════════════════════════

$detected_domain = $_SERVER['HTTP_HOST'] ?? SITE_DOMAIN;
$detected_domain = str_replace('www.', '', $detected_domain);

define('SITE_URL', 'https://' . $detected_domain);
define('ADMIN_URL', SITE_URL . '/admin');
define('API_URL', SITE_URL . '/api');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

// ═══════════════════════════════════════════════════════════
// 🗄️ BASE DE DONNÉES
// ═══════════════════════════════════════════════════════════

define('DB_CHARSET', 'utf8mb4');
define('DB_TIMEZONE', 'Europe/Paris');

// ═══════════════════════════════════════════════════════════
// 🔒 SÉCURITÉ
// ═══════════════════════════════════════════════════════════

define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'ECOSYSTEM_' . strtoupper(INSTANCE_ID));
define('CSRF_TOKEN_NAME', '_csrf_token');

// ═══════════════════════════════════════════════════════════
// 📊 FEATURES
// ═══════════════════════════════════════════════════════════

define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');
define('SITE_DESCRIPTION', 'Conseiller immobilier independant. Achat, vente, location.');
define('SITE_KEYWORDS', 'immobilier, achat, vente, location');
define('ITEMS_PER_PAGE', 12);
define('ARTICLES_PER_PAGE', 10);
define('ADMIN_ITEMS_PER_PAGE', 50);

define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_SMS', false);
define('ENABLE_ANALYTICS', true);
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// ═══════════════════════════════════════════════════════════
// 📝 LOGS & ERRORS
// ═══════════════════════════════════════════════════════════

define('LOGS_PATH', ROOT_PATH . '/logs');
define('DEBUG_MODE', false);

error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_errors.log');

// ═══════════════════════════════════════════════════════════
// 🔌 SESSION
// ═══════════════════════════════════════════════════════════

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ═══════════════════════════════════════════════════════════
// 🗂️ FONCTIONS GLOBALES
// ═══════════════════════════════════════════════════════════

if (!function_exists('sanitize')) {
    function sanitize($input, $type = 'string') {
        if ($type === 'email') {
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        }
        if ($type === 'int') {
            return (int) $input;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
        exit;
    }
}

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_email']);
    }
}

if (!function_exists('getAdminId')) {
    function getAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }
}

if (!function_exists('getAdminEmail')) {
    function getAdminEmail() {
        return $_SESSION['admin_email'] ?? null;
    }
}

if (!function_exists('writeLog')) {
    function writeLog($message, $level = 'INFO') {
        $log_file = LOGS_PATH . '/app.log';
        @mkdir(dirname($log_file), 0755, true);

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $log_message = "[$timestamp] [$level] [$ip] $message\n";

        @file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}

if (!function_exists('getDB')) {
    function getDB() {
        static $pdo = null;

        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                writeLog("DB Connection Error: " . $e->getMessage(), 'ERROR');
                die('Erreur de connexion base de donnees');
            }
        }

        return $pdo;
    }
}

if (!function_exists('getSiteEmail')) {
    function getSiteEmail() {
        if (isAdminLoggedIn()) {
            return getAdminEmail();
        }
        return ADMIN_EMAIL;
    }
}

if (!function_exists('getSiteUrl')) {
    function getSiteUrl() {
        return SITE_URL;
    }
}

if (!function_exists('getSiteDomain')) {
    function getSiteDomain() {
        return str_replace('https://', '', SITE_URL);
    }
}

// ═══════════════════════════════════════════════════════════
// ROLES & PERMISSIONS
// ═══════════════════════════════════════════════════════════

if (!function_exists('isSuperUser')) {
    function isSuperUser() {
        return ($_SESSION['admin_role'] ?? 'admin') === 'superuser';
    }
}

if (!function_exists('getAdminRole')) {
    function getAdminRole() {
        return $_SESSION['admin_role'] ?? 'admin';
    }
}

if (!function_exists('getRoleLabel')) {
    function getRoleLabel($role = null) {
        $role = $role ?? getAdminRole();
        return $role === 'superuser' ? 'Super Administrateur' : 'Administrateur';
    }
}

if (!function_exists('isModuleAllowed')) {
    function isModuleAllowed($moduleSlug) {
        if (isSuperUser()) return true;
        $alwaysAllowed = ['dashboard', 'profile', 'advisor-context'];
        if (in_array($moduleSlug, $alwaysAllowed)) return true;

        static $permissions = null;
        if ($permissions === null) {
            $permissions = [];
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT module_slug FROM admin_module_permissions WHERE admin_id = ? AND is_allowed = 1");
                $stmt->execute([$_SESSION['admin_id']]);
                $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                return true;
            }
        }
        return in_array($moduleSlug, $permissions);
    }
}
