<?php
$zoneCity = setting('zone_city', APP_CITY);
$siteMetaDescription = setting('site_meta_description', 'Conseiller immobilier indépendant dans votre secteur.');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <meta name="description" content="<?= e($metaDesc ?? $siteMetaDescription) ?>">
    <meta name="robots" content="<?= e($metaRobots ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= e($canonical ?? APP_URL . strtok($_SERVER['REQUEST_URI'], '?')) ?>">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= e($pageTitle ?? APP_NAME) ?>">
    <meta property="og:description" content="<?= e($metaDesc ?? ('Conseiller immobilier à ' . ($zoneCity ?: 'votre secteur'))) ?>">
    <meta property="og:type"        content="<?= e($ogType ?? 'website') ?>">
    <meta property="og:url"         content="<?= e(APP_URL . $_SERVER['REQUEST_URI']) ?>">
    <meta property="og:locale"      content="fr_FR">
    <meta property="og:site_name"   content="<?= e(APP_NAME) ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image"       content="<?= e($ogImage) ?>">
    <meta property="og:image:alt"   content="<?= e($pageTitle ?? APP_NAME) ?>">
    <?php endif; ?>

    <!-- Pagination SEO -->
    <?php if (!empty($prevPage)): ?><link rel="prev" href="<?= e($prevPage) ?>"><?php endif; ?>
    <?php if (!empty($nextPage)): ?><link rel="next" href="<?= e($nextPage) ?>"><?php endif; ?>

    <!-- JSON-LD : Organisation -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": "<?= e(APP_NAME) ?>",
        "description": "<?= e($siteMetaDescription) ?>",
        "url": "<?= e(APP_URL) ?>",
        "telephone": "<?= e(APP_PHONE) ?>",
        "email": "<?= e(APP_EMAIL) ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "<?= e($zoneCity) ?>",
            "addressCountry": "FR"
        },
        "areaServed": {
            "@type": "City",
            "name": "<?= e($zoneCity) ?>"
        }
        <?php if (!empty($jsonLd)): ?>,<?= $jsonLd ?><?php endif; ?>
    }
    </script>

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
    <div class="flash flash--<?= e($flash['type']) ?>" role="alert" aria-live="assertive" aria-atomic="true">
        <span><?= e($flash['message']) ?></span>
        <button class="flash__close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
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
