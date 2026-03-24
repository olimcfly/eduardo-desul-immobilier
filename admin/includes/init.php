<?php
/**
 * ══════════════════════════════════════════════════════════════
 * /admin/includes/init.php
 * Initialisation Admin — Pont vers config/config.php
 * 
 * v2.0 — Fix session : démarre la session AVANT config.php
 *         pour éviter le changement de nom de session (logout)
 * 
 * Explication du bug :
 *   - dashboard.php fait session_start() en ligne 18 (nom par défaut PHPSESSID)
 *   - login.php fait pareil → la session admin est dans PHPSESSID
 *   - config.php fait session_name('ECOSYSTEM_...') + session_start()
 *   - Si config.php est chargé en premier → il crée une NOUVELLE session vide
 *   - $_SESSION['admin_id'] n'existe pas → redirect vers login = LOGOUT
 * 
 * Solution : on démarre session_start() ICI avant config.php.
 *   config.php voit session_status() === PHP_SESSION_ACTIVE → skip.
 * ══════════════════════════════════════════════════════════════
 */

// Éviter la double inclusion
if (defined('ADMIN_INIT_LOADED')) return;
define('ADMIN_INIT_LOADED', true);

// ─── CRITIQUE : Démarrer la session avec le nom par défaut ───
// C'est le même comportement que dashboard.php et login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Charger la config principale ───
// config.php va voir que la session est déjà active et ne la redémarrera pas
$configPath = dirname(dirname(__DIR__)) . '/config/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    die('Erreur: config/config.php introuvable');
}
require_once $configPath;

// ─── Vérifier la session admin ───
if (empty($_SESSION['admin_id'])) {
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
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ─── Connexion DB ($pdo et $db disponibles pour les modules) ───
$pdo = getDB();
$db  = $pdo;

// ─── Constante ADMIN_ROUTER pour compatibilité ───
if (!defined('ADMIN_ROUTER')) {
    define('ADMIN_ROUTER', true);
}

// ─── Infos admin ───
$adminName    = $_SESSION['admin_name'] ?? $_SESSION['admin_email'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$adminId      = $_SESSION['admin_id'];
$adminRole    = $_SESSION['admin_role'] ?? 'admin';

// ─── Helpers rôles & permissions ───

if (!function_exists('isSuperUser')) {
    function isSuperUser() {
        return ($_SESSION['admin_role'] ?? 'admin') === 'superuser';
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
                $db = getDB();
                $stmt = $db->prepare("SELECT module_slug FROM admin_module_permissions WHERE admin_id = ? AND is_allowed = 1");
                $stmt->execute([$_SESSION['admin_id']]);
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
        return $_SESSION['admin_role'] ?? 'admin';
    }
}

if (!function_exists('getRoleLabel')) {
    function getRoleLabel($role = null) {
        $role = $role ?? getAdminRole();
        return $role === 'superuser' ? 'Super Administrateur' : 'Administrateur';
    }
}