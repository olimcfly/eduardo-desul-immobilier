<?php
declare(strict_types=1);

/**
 * Variables partagées pour la barre d’en-tête (layout principal + header legacy).
 */
$stylesToInclude = $stylesToInclude ?? [];
$extraJs = $extraJs ?? [];

if (!function_exists('isActive')) {
    function isActive(string $path): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        return $uri === $path || str_starts_with((string) $uri, $path);
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$advisorFirstname = trim((string) setting('advisor_firstname', ''));
$advisorLastname = trim((string) setting('advisor_lastname', ''));
$advisorName = trim($advisorFirstname . ' ' . $advisorLastname);
if ($advisorName === '') {
    if (defined('ADVISOR_NAME') && ADVISOR_NAME !== '') {
        $advisorName = ADVISOR_NAME;
    } else {
        $advisorName = defined('APP_NAME') ? preg_replace('/\s+Immobilier$/i', '', APP_NAME) : 'Eduardo Desul';
    }
}
$advisorPhoto = setting('advisor_photo', '');
if ($advisorPhoto === '') {
    $advisorPhoto = '/assets/images/advisor-photo.jpg';
}
