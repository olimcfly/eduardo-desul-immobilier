<?php
// ============================================================
// ADMIN — Point d'entrée
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
define('ROOT', ROOT_PATH);
define('IS_ADMIN', true);

// ── Chargement .env ──────────────────────────────────────────
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
        putenv(trim($k) . '=' . trim($v, " \t\"'"));
    }
}

// ── Core ─────────────────────────────────────────────────────
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Auth.php';

Session::start();

// ── Fonctions utilitaires ────────────────────────────────────
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function adminView(string $view, array $data = []): void
{
    extract($data);
    $flash = Session::getFlash();
    require_once ROOT_PATH . '/admin/views/' . $view . '.php';
}

function adminLayout(string $view, array $data = []): void
{
    extract($data);
    $flash = Session::getFlash();
    ob_start();
    require_once ROOT_PATH . '/admin/views/' . $view . '.php';
    $content = ob_get_clean();
    $pageTitle = $data['pageTitle'] ?? 'Admin';
    require_once ROOT_PATH . '/admin/views/layout.php';
}

// ── Router simple ────────────────────────────────────────────
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = '/admin';
$path   = '/' . ltrim(substr($uri, strlen($base)), '/');
if ($path !== '/') $path = rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// ── Routes publiques (sans auth) ─────────────────────────────

// Login GET
if ($path === '/login' && $method === 'GET') {
    if (Auth::isAdmin()) { header('Location: /admin'); exit; }
    adminView('login');
    exit;
}

// Login POST
if ($path === '/login' && $method === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        Session::flash('error', 'Email et mot de passe requis.');
        header('Location: /admin/login'); exit;
    }

    try {
        $db   = Database::getInstance();
        $stmt = $db->prepare('SELECT id, email, password, role, name FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && Auth::verifyPassword($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                Session::flash('error', 'Accès réservé aux administrateurs.');
                header('Location: /admin/login'); exit;
            }
            Auth::login($user);
            Session::flash('success', 'Bienvenue, ' . $user['name'] . ' !');
            header('Location: /admin'); exit;
        } else {
            Session::flash('error', 'Email ou mot de passe incorrect.');
            header('Location: /admin/login'); exit;
        }
    } catch (Exception $e) {
        $msg = APP_DEBUG ? $e->getMessage() : 'Erreur serveur. Réessayez.';
        Session::flash('error', $msg);
        header('Location: /admin/login'); exit;
    }
}

// Logout
if ($path === '/logout') {
    Auth::logout();
    Session::flash('success', 'Vous avez été déconnecté.');
    header('Location: /admin/login'); exit;
}

// ── Routes protégées (auth requise) ──────────────────────────
Auth::requireAdmin();

// Dashboard
if ($path === '/' || $path === '') {
    require_once ROOT_PATH . '/admin/controllers/DashboardController.php';
    $ctrl = new DashboardController();
    $ctrl->index();
    exit;
}

// 404 admin
http_response_code(404);
adminLayout('errors/404', ['pageTitle' => 'Page introuvable']);
