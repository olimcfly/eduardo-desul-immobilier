<?php

declare(strict_types=1);

/**
 * Guard léger pour figer les endpoints legacy SEO.
 * Objectif: visibilité et traçabilité, sans casser la compatibilité existante.
 */
function seoLegacyGuard(string $legacyPath, ?string $modernPath = null): void
{
    header('X-IMMO-SEO-Legacy: ' . $legacyPath);

    if ($modernPath !== null && $modernPath !== '') {
        header('X-IMMO-SEO-Replacement: ' . $modernPath);
    }

    static $alreadyLogged = [];
    if (isset($alreadyLogged[$legacyPath])) {
        return;
    }

    $alreadyLogged[$legacyPath] = true;
    $message = '[SEO legacy frozen] ' . $legacyPath;
    if ($modernPath !== null && $modernPath !== '') {
        $message .= ' -> use ' . $modernPath;
    }

    error_log($message);
}
