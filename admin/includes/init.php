<?php
/**
 * ══════════════════════════════════════════════════════════════
 * /admin/includes/init.php
 * Initialisation Admin — Pont vers config/config.php
 *
 * Important:
 * La session doit être initialisée avec le même nom que celui défini
 * dans config/config.php (SESSION_NAME), sinon les appels API peuvent
 * lire une session différente et déclencher des faux "session expirée".
 * ══════════════════════════════════════════════════════════════
 */

// Éviter la double inclusion
if (defined('ADMIN_INIT_LOADED')) return;
define('ADMIN_INIT_LOADED', true);

// ─── Charger la config principale ───
// config.php initialise la session avec SESSION_NAME si nécessaire.
$configPath = dirname(dirname(__DIR__)) . '/config/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    die('Erreur: config/config.php introuvable');
}
require_once $configPath;

// ─── Bootstrap CRM (constantes, storage, helpers) ───
require_once __DIR__ . '/bootstrap.php';

// ─── Vérifier la session admin ───
if (empty($_SESSION['auth_admin_id'])) {
    // Si c'est un appel AJAX/API, retourner du JSON au lieu de rediriger
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
              || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Session expirée']);
        exit;
    }
    header('Location: /admin/login.php');
    exit;
}

// ─── CSRF token ───
if (empty($_SESSION['auth_csrf_token'])) {
    $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
}

if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
// ─── Connexion DB ($pdo et $db disponibles pour les modules) ───
require_once ROOT_PATH . '/includes/classes/Database.php';
$pdo = Database::getInstance();
$db  = $pdo;

// ─── Constante ADMIN_ROUTER pour compatibilité ───
if (!defined('ADMIN_ROUTER')) {
    define('ADMIN_ROUTER', true);
}

// ─── Infos admin ───
$adminName    = $_SESSION['auth_admin_name'] ?? $_SESSION['auth_admin_email'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$adminId      = $_SESSION['auth_admin_id'];
$adminRole    = $_SESSION['auth_admin_role'] ?? 'admin';

// ─── Helpers rôles & permissions ───

if (!function_exists('isSuperUser')) {
    function isSuperUser() {
        if (($_SESSION['auth_admin_role'] ?? 'admin') === 'superuser') {
            return true;
        }

        // Compte admin historique qui doit conserver un accès complet
        $adminEmail = strtolower(trim((string)($_SESSION['auth_admin_email'] ?? '')));
        $fullAccessEmails = [
            'admin@duardo-desul-immobilier.fr',
        ];

        return in_array($adminEmail, $fullAccessEmails, true);
    }
}

if (!function_exists('isModuleAllowed')) {
    /**
     * Vérifie si l'admin courant a accès à un module.
     * Le Super User a accès à tout.
     * Les admins n'ont accès qu'aux modules autorisés dans admin_module_permissions.
     */
    function isModuleAllowed($moduleSlug) {
        if (isSuperUser()) return true;

        // Modules toujours accessibles pour tous
        $alwaysAllowed = ['dashboard', 'profile', 'advisor-context'];
        if (in_array($moduleSlug, $alwaysAllowed)) return true;

        static $permissions = null;
        if ($permissions === null) {
            $permissions = [];
            try {
                $db = Database::getInstance();
                $countStmt = $db->prepare("SELECT COUNT(*) FROM admin_module_permissions WHERE admin_id = ?");
                $countStmt->execute([$_SESSION['auth_admin_id']]);
                $hasCustomPermissions = (int)$countStmt->fetchColumn() > 0;

                if (!$hasCustomPermissions) {
                    // Compatibilité comptes existants : si aucune permission n'est encore définie,
                    // on autorise tous les modules (comportement historique).
                    return true;
                }

                $stmt = $db->prepare("SELECT module_slug FROM admin_module_permissions WHERE admin_id = ? AND is_allowed = 1");
                $stmt->execute([$_SESSION['auth_admin_id']]);
                $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                // Si la table n'existe pas encore, autoriser tout
                return true;
            }
        }

        return in_array($moduleSlug, $permissions);
    }
}

if (!function_exists('getAdminRole')) {
    function getAdminRole() {
        return $_SESSION['auth_admin_role'] ?? 'admin';
    }
}

if (!function_exists('getRoleLabel')) {
    function getRoleLabel($role = null) {
        $role = $role ?? getAdminRole();
        return $role === 'superuser' ? 'Super Administrateur' : 'Administrateur';
    }
}
