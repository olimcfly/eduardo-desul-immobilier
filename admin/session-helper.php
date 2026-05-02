<?php
declare(strict_types=1);

/**
 * Démarre la session de manière cohérente et sécurisée
 */
function startAdminSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('edo_immo_sess');
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

/**
 * Vérifie si l'utilisateur est connecté
 */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Redirige avec écriture de la session
 */
function redirectAdmin(string $url): void
{
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
