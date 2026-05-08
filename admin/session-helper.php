<?php
declare(strict_types=1);

/**
 * Démarre la session de manière cohérente et sécurisée
 */
if (!function_exists('startAdminSession')) {
    function startAdminSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('pascalhamm_sess');
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_set_cookie_params([
                'lifetime' => 28800,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 */
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
    }
}

/**
 * Redirige avec écriture de la session
 */
if (!function_exists('redirectAdmin')) {
    function redirectAdmin(string $url): void
    {
        if ($url === '/admin/login' || $url === '/admin/login.php') {
            $url = '/admin/auth/login.php';
        } elseif ($url === '/admin/logout' || $url === '/admin/logout.php') {
            $url = '/admin/auth/logout.php';
        } elseif ($url === '/admin/forgot-password.php') {
            $url = '/admin/auth/forgot-password.php';
        } elseif ($url === '/admin/reset-password.php') {
            $url = '/admin/auth/reset-password.php';
        } elseif ($url === '/admin/profile.php') {
            $url = '/admin/auth/profile.php';
        }

        // Éviter les boucles de redirection
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';

        // Normaliser pour comparaison
        $url = str_replace('//', '/', $url);
        $currentPath = parse_url($currentUri, PHP_URL_PATH) ?? '';
        $currentPath = str_replace('//', '/', $currentPath);

        // Si on essaie de rediriger vers la même URL, arrêter
        if (rtrim($url, '/') === rtrim($currentPath, '/')) {
            return;
        }

        error_log("[REDIRECT DEBUG] Redirecting from $currentPath to $url");
        session_write_close();
        header('Location: ' . $url);
        exit;
    }
}
