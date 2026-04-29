<?php
declare(strict_types=1);

// public/templates/header.php
// — $siteHeaderEmbedOnly === true : uniquement le bloc <header> inclus depuis layout.php.
// — false : document autonome pour compatibilité avec d'anciens gabarits.

$siteHeaderEmbedOnly = $siteHeaderEmbedOnly ?? false;
$stylesToInclude = $stylesToInclude ?? [];
$extraJs = $extraJs ?? [];

if (!function_exists('isActive')) {
    function isActive(string $path): bool {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return $uri === $path || strpos($uri, $path) === 0;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$advisorFirstname = trim((string) setting('advisor_firstname', setting('profil_prenom', '')));
$advisorLastname  = trim((string) setting('advisor_lastname', setting('profil_nom', '')));
$advisorName      = trim($advisorFirstname . ' ' . $advisorLastname);
if ($advisorName === '') {
    $advisorName = (defined('ADVISOR_NAME') && ADVISOR_NAME !== '')
        ? ADVISOR_NAME
        : (defined('APP_NAME') ? preg_replace('/\s+Immobilier$/i', '', APP_NAME) : '');
}
if ($advisorName === '') {
    $advisorName = 'Conseiller immobilier';
}

$advisorPhoto = trim((string) setting('advisor_photo', ''));
if ($advisorPhoto === '') {
    $advisorPhoto = defined('DEFAULT_ADVISOR_PHOTO_URL') ? DEFAULT_ADVISOR_PHOTO_URL : '/assets/images/advisor-photo.jpg';
}

if (!$siteHeaderEmbedOnly) : ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? (defined('APP_NAME') ? APP_NAME : 'Site immobilier')) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'Conseiller immobilier indépendant') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? '') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? (defined('APP_NAME') ? APP_NAME : 'Site immobilier')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc ?? 'Conseiller immobilier indépendant') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars('https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">

    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/nav.css')) ?>">
    <?php foreach ($stylesToInclude as $cssFile) : ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(asset_url((string) $cssFile)) ?>">
    <?php endforeach; ?>
    <?php foreach ($extraJs as $jsFile) : ?>
        <script src="<?= htmlspecialchars(asset_url((string) $jsFile)) ?>" defer></script>
    <?php endforeach; ?>
</head>
<body>
<?php endif; ?>

<header class="site-header" id="site-header">
    <div class="container header__inner">
        <a href="<?= htmlspecialchars(url('/')) ?>" class="header__logo" aria-label="<?= htmlspecialchars($advisorName) ?> — Accueil">
            <span class="logo__text">
                <strong><?= htmlspecialchars($advisorName) ?></strong>
                <em>Immobilier</em>
            </span>
        </a>

        <?php require __DIR__ . '/nav.php'; ?>

        <div class="header__actions">
            <a href="<?= htmlspecialchars(url('/avis-de-valeur')) ?>" class="btn btn--outline btn--header-cta">Avis de valeur</a>
            <a href="<?= htmlspecialchars(url('/prendre-rendez-vous')) ?>" class="btn btn--primary btn--header-cta">Prendre RDV</a>
        </div>

        <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-mobile">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<?php if (!$siteHeaderEmbedOnly) : ?>
<main>
<?php endif; ?>
