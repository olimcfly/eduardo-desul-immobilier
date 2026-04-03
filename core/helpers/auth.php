<?php
// ============================================================
// AUTH HELPERS
// ============================================================

function isLogged(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

function isAdmin(): bool
{
    return isLogged() && in_array($_SESSION['user_role'], ['admin', 'superadmin'], true);
}

function requireAuth(): void
{
    if (!isLogged()) {
        flash('error', 'Connectez-vous pour accéder à cette page.');
        redirect(APP_URL . '/login');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

function currentUser(): ?array
{
    if (!isLogged()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'],
        'name'  => $_SESSION['user_name'] ?? '',
    ];
}
