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

    <style>
        .role-topbar { position: fixed; top: 0; left: 0; width: 100%; z-index: 9999; padding: 8px 20px; font-weight: 700; font-family: Inter, Arial, sans-serif; font-size: 14px; }
        .topbar-admin { background: #1a73e8; color: #fff; }
        .topbar-superadmin { background: linear-gradient(90deg, #b8860b, #ffd700); color: #1a1a1a; }
        .role-topbar a { color: inherit; text-decoration: underline; }
        body.has-role-topbar { padding-top: 42px; }

        .session-access-modal { position: fixed; inset: 0; z-index: 10000; background: rgba(15, 23, 42, .45); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .session-access-modal__card { background: #fff; border-radius: 12px; padding: 18px; max-width: 420px; width: 100%; box-shadow: 0 15px 45px rgba(0,0,0,.25); }
        .session-access-modal__card h3 { margin: 0 0 8px; }
        .session-access-modal__card p { margin: 0 0 14px; color: #334155; }
        .session-access-modal__actions { display: flex; gap: 10px; }
        .session-access-modal__actions button { border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; background: #2563eb; color: #fff; }
        .session-access-modal__actions button.danger { background: #dc2626; }
    </style>

</head>
<body class="<?= e($bodyClass ?? '') ?>">

<?php require ROOT_PATH . '/includes/layout/header.php'; ?>

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

<script>
(function(){
    var hasRoleTopbar = !!document.querySelector('.role-topbar');
    if (hasRoleTopbar) document.body.classList.add('has-role-topbar');
})();
</script>


<script>
window.__APP_SETTINGS__ = {
    advisorName: <?= json_encode(trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', '')) ?: (ADVISOR_NAME ?: APP_NAME), JSON_UNESCAPED_UNICODE) ?>,
    zoneCity: <?= json_encode((string) setting('zone_city', APP_CITY), JSON_UNESCAPED_UNICODE) ?>
};
</script>

<!-- JS -->
<script src="/assets/js/main.js" defer></script>
<?php foreach ($extraJs ?? [] as $js): ?>
<script src="<?= e($js) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>
