<?php
declare(strict_types=1);

/**
 * Système d'authentification complet
 * ─────────────────────────────────
 * Rôles : user < editor < admin < superadmin
 *
 * - user       : accès aux modules activés pour les users
 * - admin      : mêmes droits que user + modules admin
 * - superadmin : tout, y compris activer/désactiver modules
 */

require_once __DIR__ . '/db.php';

// ── Constantes rôles ──────────────────────────────────────────────────────────

const ROLE_LEVELS = [
    'user'       => 1,
    'editor'     => 2,
    'admin'      => 3,
    'superadmin' => 4,
];

// ── Session sécurisée ─────────────────────────────────────────────────────────

if (!function_exists('startSecureSession')) {
    function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }
}

// ── Récupération utilisateur ──────────────────────────────────────────────────

if (!function_exists('getUserByEmail')) {
    function getUserByEmail(string $email): ?array
    {
        $pdo = getPDOSafe();
        if (!$pdo) return null;

        try {
            $stmt = $pdo->prepare(
                'SELECT * FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute([':email' => strtolower(trim($email))]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('[AUTH] getUserByEmail : ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getUserById')) {
    function getUserById(int $id): ?array
    {
        $pdo = getPDOSafe();
        if (!$pdo) return null;

        try {
            $stmt = $pdo->prepare(
                'SELECT * FROM users WHERE id = :id LIMIT 1'
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('[AUTH] getUserById : ' . $e->getMessage());
            return null;
        }
    }
}

// ── Tentative de connexion ────────────────────────────────────────────────────

if (!function_exists('attemptLogin')) {
    /**
     * @return array{success: bool, user?: array, error?: string}
     */
    function attemptLogin(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        // Validations format
        if ($email === '' || $password === '') {
            return ['success' => false, 'error' => 'Email et mot de passe requis.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Format d\'email invalide.'];
        }

        // Récupération (timing-safe : on vérifie toujours le hash)
        $user = getUserByEmail($email);

        if ($user === null) {
            // Hash fictif pour éviter le timing attack
            password_verify('dummy_timing_protection',
                '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234');
            return ['success' => false, 'error' => 'Identifiants incorrects.'];
        }

        // Compte désactivé
        if ((int)$user['is_active'] !== 1) {
            return ['success' => false, 'error' => 'Compte désactivé. Contactez l\'administrateur.'];
        }

        // Vérification mot de passe
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Identifiants incorrects.'];
        }

        // Rehash si algo obsolète
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            try {
                getPDO()->prepare(
                    'UPDATE users SET password = :h WHERE id = :id'
                )->execute([':h' => $newHash, ':id' => $user['id']]);
            } catch (Throwable) { /* non bloquant */ }
        }

        // Mise à jour last_login
        try {
            getPDO()->prepare(
                'UPDATE users SET last_login = NOW() WHERE id = :id'
            )->execute([':id' => $user['id']]);
        } catch (Throwable) { /* non bloquant */ }

        return ['success' => true, 'user' => $user];
    }
}

// ── Initialiser la session ────────────────────────────────────────────────────

if (!function_exists('loginUser')) {
    function loginUser(array $user): void
    {
        startSecureSession();
        session_regenerate_id(true);

        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;
        $_SESSION['login_at']   = time();
        $_SESSION['ip']         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// ── Vérifications d'état ──────────────────────────────────────────────────────

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool
    {
        startSecureSession();
        return !empty($_SESSION['logged_in'])
            && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string $requiredRole): bool
    {
        if (!isLoggedIn()) return false;

        $userRole    = $_SESSION['user_role'] ?? 'user';
        $userLevel   = ROLE_LEVELS[$userRole]      ?? 0;
        $neededLevel = ROLE_LEVELS[$requiredRole]  ?? 999;

        return $userLevel >= $neededLevel;
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin(): bool { return hasRole('superadmin'); }
}
if (!function_exists('isAdmin')) {
    function isAdmin(): bool { return hasRole('admin'); }
}
if (!function_exists('isEditor')) {
    function isEditor(): bool { return hasRole('editor'); }
}

// ── Protection de pages ───────────────────────────────────────────────────────

if (!function_exists('requireLogin')) {
    function requireLogin(string $redirectTo = '/auth/login.php'): void
    {
        if (!isLoggedIn()) {
            startSecureSession();
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole(string $role, string $redirectTo = '/auth/login.php'): void
    {
        requireLogin($redirectTo);

        if (!hasRole($role)) {
            http_response_code(403);
            include __DIR__ . '/../errors/403.php';
            exit;
        }
    }
}

// ── Modules ───────────────────────────────────────────────────────────────────

if (!function_exists('canAccessModule')) {
    function canAccessModule(string $moduleName): bool
    {
        if (!isLoggedIn()) return false;

        // Superadmin : accès total sans vérification DB
        if (isSuperAdmin()) return true;

        $pdo = getPDOSafe();
        if (!$pdo) return false;

        try {
            $stmt = $pdo->prepare(
                'SELECT enabled_for_users, enabled_for_admins
                 FROM module_settings
                 WHERE module_name = :m
                 LIMIT 1'
            );
            $stmt->execute([':m' => $moduleName]);
            $module = $stmt->fetch();
        } catch (Throwable $e) {
            error_log('[AUTH] canAccessModule : ' . $e->getMessage());
            return false;
        }

        if (!$module) return false;

        // Admin → colonne enabled_for_admins
        if (isAdmin()) {
            return (int)$module['enabled_for_admins'] === 1;
        }

        // User / Editor → colonne enabled_for_users
        return (int)$module['enabled_for_users'] === 1;
    }
}

/**
 * Retourne tous les modules accessibles pour le user connecté
 * Utile pour construire le menu de navigation dynamiquement
 */
if (!function_exists('getAccessibleModules')) {
    function getAccessibleModules(): array
    {
        if (!isLoggedIn()) return [];

        $pdo = getPDOSafe();
        if (!$pdo) return [];

        try {
            $stmt = $pdo->query(
                'SELECT module_name, enabled_for_users, enabled_for_admins
                 FROM module_settings
                 ORDER BY id ASC'
            );
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[AUTH] getAccessibleModules : ' . $e->getMessage());
            return [];
        }

        $accessible = [];
        foreach ($rows as $row) {
            if (isSuperAdmin()) {
                $accessible[] = $row['module_name'];
                continue;
            }
            if (isAdmin() && (int)$row['enabled_for_admins'] === 1) {
                $accessible[] = $row['module_name'];
                continue;
            }
            if (!isAdmin() && (int)$row['enabled_for_users'] === 1) {
                $accessible[] = $row['module_name'];
            }
        }

        return $accessible;
    }
}

// ── Déconnexion ───────────────────────────────────────────────────────────────

if (!function_exists('logoutUser')) {
    function logoutUser(): void
    {
        startSecureSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }
        session_destroy();
    }
}

// ── User connecté ─────────────────────────────────────────────────────────────

if (!function_exists('currentUser')) {
    function currentUser(): ?array
    {
        if (!isLoggedIn()) return null;
        return getUserById((int)$_SESSION['user_id']);
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────────

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string
    {
        startSecureSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken(string $token): bool
    {
        startSecureSession();
        return !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
