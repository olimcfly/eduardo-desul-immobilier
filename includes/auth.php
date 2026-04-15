<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

const ADMIN_SESSION_TIMEOUT = 1800; // 30 minutes

/**
 * Démarre la session admin de manière sécurisée.
 */
function startAdminSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Vérifie le timeout de session.
 */
function checkSessionTimeout(): bool
{
    startAdminSession();

    if (!isset($_SESSION['last_activity'])) {
        return false;
    }

    if ((time() - (int) $_SESSION['last_activity']) > ADMIN_SESSION_TIMEOUT) {
        logout();
        return true;
    }

    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Vérifie si l'admin est connecté.
 */
function isLoggedIn(): bool
{
    startAdminSession();

    if (checkSessionTimeout()) {
        return false;
    }

    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Vérifie si le changement de mot de passe est obligatoire.
 */
function isPasswordResetRequired(): bool
{
    return defined('ADMIN_PASSWORD_RESET_REQUIRED') && ADMIN_PASSWORD_RESET_REQUIRED === true;
}

/**
 * Connecte l'administrateur.
 *
 * @return array{success:bool,message:string,force_password_reset?:bool}
 */
function login(string $email, string $password): array
{
    startAdminSession();

    $email = trim($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Veuillez saisir un email valide.'];
    }

    if (!defined('ADMIN_EMAIL') || !defined('ADMIN_PASSWORD_HASH')) {
        return ['success' => false, 'message' => 'Configuration admin introuvable.'];
    }

    if (!hash_equals((string) ADMIN_EMAIL, $email) || !password_verify($password, (string) ADMIN_PASSWORD_HASH)) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_email'] = $email;
    $_SESSION['last_activity'] = time();

    return [
        'success' => true,
        'message' => 'Connexion réussie.',
        'force_password_reset' => isPasswordResetRequired(),
    ];
}

/**
 * Déconnecte l'utilisateur.
 */
function logout(): void
{
    startAdminSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

/**
 * Met à jour config.php avec un remplacement regex sécurisé.
 */
function updateConfigConstant(string $constantName, string $value, bool $isString = true): bool
{
    $configPath = __DIR__ . '/config.php';
    $content = @file_get_contents($configPath);

    if ($content === false) {
        return false;
    }

    $replacementValue = $isString ? "'" . addslashes($value) . "'" : $value;
    $pattern = "/define\\('" . preg_quote($constantName, '/') . "',\\s*(.*?)\\);/";

    if (!preg_match($pattern, $content)) {
        return false;
    }

    $updated = preg_replace($pattern, "define('{$constantName}', {$replacementValue});", $content, 1);
    if ($updated === null) {
        return false;
    }

    return file_put_contents($configPath, $updated) !== false;
}

/**
 * Met à jour l'email admin.
 *
 * @return array{success:bool,message:string}
 */
function updateAdminEmail(string $newEmail): array
{
    startAdminSession();

    $newEmail = trim($newEmail);
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Adresse email invalide.'];
    }

    if (!updateConfigConstant('ADMIN_EMAIL', $newEmail)) {
        return ['success' => false, 'message' => 'Impossible de mettre à jour l\'email.'];
    }

    $_SESSION['admin_email'] = $newEmail;
    return ['success' => true, 'message' => 'Email mis à jour avec succès.'];
}

/**
 * Met à jour le mot de passe admin.
 *
 * @return array{success:bool,message:string}
 */
function updateAdminPassword(string $currentPassword, string $newPassword): array
{
    if (!password_verify($currentPassword, (string) ADMIN_PASSWORD_HASH)) {
        return ['success' => false, 'message' => 'Mot de passe actuel incorrect.'];
    }

    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.'];
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($newHash === false) {
        return ['success' => false, 'message' => 'Impossible de sécuriser le mot de passe.'];
    }

    if (!updateConfigConstant('ADMIN_PASSWORD_HASH', $newHash) || !updateConfigConstant('ADMIN_PASSWORD_RESET_REQUIRED', 'false', false)) {
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du mot de passe.'];
    }

    return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès.'];
}

/**
 * Protège une page admin.
 */
function requireAdminAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}
