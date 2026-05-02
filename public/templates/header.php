<?php
declare(strict_types=1);

/**
 * En-tête document complet (pages legacy type biens.php).
 * Le layout principal utilise public/templates/partials/site-header.php uniquement.
 */
require_once __DIR__ . '/header-bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? 'mots-clés, par, défaut') ?>">

    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '') ?>">

    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/nav.css')) ?>">
    <?php foreach ($stylesToInclude as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(asset_url($cssFile)) ?>">
    <?php endforeach; ?>

    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?= htmlspecialchars(asset_url($jsFile)) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<body>
<?php require __DIR__ . '/partials/site-header.php'; ?>
    <main>
