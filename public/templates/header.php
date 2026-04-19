<?php
// public/templates/header.php

// Définir la variable $stylesToInclude si elle n'existe pas
$stylesToInclude = $stylesToInclude ?? [];
$extraJs = $extraJs ?? [];

// Définir la fonction isActive()
if (!function_exists('isActive')) {
    function isActive($path) {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $path ||
               strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $path) === 0;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$advisorFirstname = trim((string) setting('advisor_firstname', ''));
$advisorLastname  = trim((string) setting('advisor_lastname', ''));
$advisorName      = trim($advisorFirstname . ' ' . $advisorLastname);
if ($advisorName === '') {
    // Fallback : ADVISOR_NAME ou 'Eduardo Desul' (pas APP_NAME qui contient "Immobilier")
    if (defined('ADVISOR_NAME') && ADVISOR_NAME !== '') {
        $advisorName = ADVISOR_NAME;
    } else {
        // Extraire juste le nom (sans " Immobilier") depuis APP_NAME si besoin
        $advisorName = defined('APP_NAME') ? preg_replace('/\s+Immobilier$/i', '', APP_NAME) : 'Eduardo Desul';
    }
}
$advisorPhoto = setting('advisor_photo', '');
if (empty($advisorPhoto)) {
    $advisorPhoto = '/assets/images/advisor-photo.jpg';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? 'mots-clés, par, défaut') ?>">

    <!-- Balises Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">

    <!-- Inclure les styles -->

<link rel="stylesheet" href="<?= e(asset_url('/assets/css/nav.css')) ?>">  <?php foreach ($stylesToInclude as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(asset_url($cssFile)) ?>">
    <?php endforeach; ?>

    <!-- Inclure les scripts JavaScript -->
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?= htmlspecialchars(asset_url($jsFile)) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
    

</head>
<body>
    <header class="site-header" id="site-header">
        <div class="container header__inner">

            <!-- Logo -->
           <a href="<?= htmlspecialchars(url('/')) ?>" class="header__logo" aria-label="<?= htmlspecialchars($advisorName) ?> — Accueil">
    <span class="logo__text">
        <strong><?= htmlspecialchars($advisorName) ?></strong>
        <em>Immobilier</em>
    </span>
</a>

            <!-- Navigation principale -->
            <?php require __DIR__ . '/nav.php'; ?>

            <!-- CTA header -->
            <div class="header__actions">
                <a href="<?= htmlspecialchars(url('/avis-de-valeur')) ?>" class="btn btn--outline btn--header-cta">Avis de valeur</a>
                <a href="<?= htmlspecialchars(url('/prendre-rendez-vous')) ?>" class="btn btn--primary btn--header-cta">Prendre RDV</a>
            </div>

            <!-- Burger mobile -->
            <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-mobile">
                <span></span><span></span><span></span>
            </button>

        </div>
    </header>

    <!-- Début du contenu principal -->
    <main>
