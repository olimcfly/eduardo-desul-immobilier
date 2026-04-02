<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <meta name="description" content="<?= e($metaDesc ?? 'Conseiller immobilier indépendant à Bordeaux — Eduardo Desul vous accompagne dans tous vos projets immobiliers.') ?>">
    <?php if (!empty($metaRobots)): ?><meta name="robots" content="<?= e($metaRobots) ?>"><?php endif; ?>
    <link rel="canonical" href="<?= e($canonical ?? APP_URL . $_SERVER['REQUEST_URI']) ?>">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= e($pageTitle ?? APP_NAME) ?>">
    <meta property="og:description" content="<?= e($metaDesc ?? '') ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= e(APP_URL . $_SERVER['REQUEST_URI']) ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image"       content="<?= e($ogImage) ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <?php foreach ($extraCss ?? [] as $css): ?>
    <link rel="stylesheet" href="<?= e($css) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">

<?php require __DIR__ . '/header.php'; ?>

<main id="main-content">
    <?php
    $flash = Session::getFlash();
    if ($flash): ?>
    <div class="flash flash--<?= e($flash['type']) ?>" role="alert">
        <span><?= e($flash['message']) ?></span>
        <button class="flash__close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>

    <?= $pageContent ?? '' ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<!-- JS -->
<script src="/assets/js/main.js" defer></script>
<?php foreach ($extraJs ?? [] as $js): ?>
<script src="<?= e($js) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>
