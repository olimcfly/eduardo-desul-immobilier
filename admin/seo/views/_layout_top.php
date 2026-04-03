<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= seo_h($pageTitle) ?> · <?= seo_h(SEO_MODULE_NAME) ?></title>
    <link rel="stylesheet" href="css/seo-styles.css">
    <link rel="stylesheet" href="css/seo-animations.css">
</head>
<body>
<div class="seo-app">
    <aside class="seo-sidebar" aria-label="Navigation SEO">
        <h1><?= seo_h(SEO_MODULE_NAME) ?></h1>
        <nav>
            <a href="?action=dashboard" class="<?= $action === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
            <a href="?action=editor" class="<?= $action === 'editor' ? 'is-active' : '' ?>">Éditeur</a>
            <a href="?action=keywords" class="<?= $action === 'keywords' ? 'is-active' : '' ?>">Mots-clés</a>
            <a href="?action=serp" class="<?= $action === 'serp' ? 'is-active' : '' ?>">SERP</a>
            <a href="?action=silo" class="<?= $action === 'silo' ? 'is-active' : '' ?>">Silo Pilier</a>
        </nav>
    </aside>
    <main class="seo-main">
        <header class="seo-topbar">
            <h2><?= seo_h($pageTitle) ?></h2>
        </header>
