<?php

declare(strict_types=1);

/**
 * Valide qu'un utilisateur connecté est disponible.
 */
function gmbAssertAuthorizedUser(int $userId): void
{
    if ($userId <= 0) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

/**
 * Routeur interne minimal pour conserver une URL stable
 * (/admin?module=gmb&action=...).
 */
function gmbResolveAction(mixed $action): string
{
    $allowedActions = ['index', 'listing', 'reviews', 'review-requests', 'stats'];
    $rawAction = is_string($action) ? $action : '';
    $rawAction = strtolower(preg_replace('/[^a-z-]/', '', $rawAction) ?? '');

    if ($rawAction === '' || !in_array($rawAction, $allowedActions, true)) {
        return 'index';
    }

    return $rawAction;
}
