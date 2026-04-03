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

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
