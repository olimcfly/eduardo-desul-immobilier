<?php
// ============================================================
// AUTH
// ============================================================

class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_name']  = $user['name'] ?? '';
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
    }

    public static function isAdmin(): bool
    {
        return self::check() && in_array($_SESSION['user_role'], ['admin', 'superadmin'], true);
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'],
            'name'  => $_SESSION['user_name'] ?? '',
        ];
    }

    public static function requireAuth(string $redirect = '/admin/login'): void
    {
        if (!self::check()) {
            Session::flash('error', 'Connectez-vous pour accéder à cette page.');
            header('Location: ' . $redirect);
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            http_response_code(403);
            die('Accès réservé aux administrateurs.');
        }
    }


    /**
     * Authentifie un utilisateur par email/mot de passe et ouvre la session.
     *
     * @param string $email
     * @param string $password
     * @param array<int, string> $allowedRoles
     */
    public static function attempt(string $email, string $password, array $allowedRoles = []): bool
    {
        $email = trim(mb_strtolower($email));
        if ($email === '' || $password === '') {
            return false;
        }

        $sql = "SELECT id, email, password, role, name FROM users WHERE email = ? LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !isset($user['password']) || !self::verifyPassword($password, (string) $user['password'])) {
            return false;
        }

        if ($allowedRoles !== [] && !in_array((string) ($user['role'] ?? ''), $allowedRoles, true)) {
            return false;
        }

        self::login([
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'] ?? 'admin',
            'name'  => $user['name'] ?? '',
        ]);

        return true;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
