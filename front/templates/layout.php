<?php
declare(strict_types=1);
/**
 * Layout principal du site vitrine
 * Variables attendues :
 *  $pageTitle       string  — Titre SEO
 *  $pageDesc        string  — Meta description
 *  $pageCanonical   string  — URL canonique
 *  $pageOgImage     string  — Image OG
 *  $bodyClass       string  — Classe CSS body
 *  $advisor         array   — Données conseiller
 *  $settings        array   — Settings globaux
 */

$advisor  = $advisor  ?? [];
$settings = $settings ?? [];

$siteName   = $advisor['trade_name']   ?? 'Conseiller Immobilier';
$siteColor  = $settings['primary_color'] ?? '#2563EB';
$sitePhone  = $advisor['phone']        ?? '';
$siteEmail  = $advisor['email']        ?? '';
$siteLogo   = $advisor['logo_url']     ?? '/assets/img/logo-default.svg';
$gtmId      = $settings['gtm_container_id'] ?? '';
$fbPixelId  = $settings['fb_pixel_id'] ?? '';
$ga4Id      = $settings['ga4_id']      ?? '';

$canonical  = $pageCanonical ?? 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
$ogImage    = $pageOgImage   ?? $advisor['photo_url'] ?? '/assets/img/og-default.jpg';

?><!DOCTYPE html>
<html lang="fr" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? '') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta name="robots" content="<?= ($pageNoIndex ?? false) ? 'noindex,nofollow' : 'index,follow' ?>">

    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="<?= htmlspecialchars($siteName) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($pageTitle ?? $siteName) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc ?? '') ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:locale"      content="fr_FR">

    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($pageTitle ?? $siteName) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc ?? '') ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($ogImage) ?>">

    <script type="application/ld+json">
    <?= json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'RealEstateAgent',
        'name'            => $siteName,
        'description'     => $advisor['bio'] ?? '',
        'url'             => 'https://' . ($_SERVER['HTTP_HOST'] ?? ''),
        'telephone'       => $sitePhone,
        'email'           => $siteEmail,
        'image'           => $ogImage,
        'address'         => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $advisor['address']     ?? '',
            'addressLocality' => $advisor['city']        ?? '',
            'postalCode'      => $advisor['zip']         ?? '',
            'addressCountry'  => 'FR',
        ],
        'geo' => (!empty($advisor['lat']) ? [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $advisor['lat'],
            'longitude' => $advisor['lng'],
        ] : null),
        'sameAs' => array_filter([
            $advisor['facebook_url']  ?? null,
            $advisor['instagram_url'] ?? null,
            $advisor['linkedin_url']  ?? null,
        ]),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <?php if ($gtmId): ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= htmlspecialchars($gtmId) ?>');</script>
    <?php endif; ?>

    <?php if ($fbPixelId): ?>
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
    (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= htmlspecialchars($fbPixelId) ?>');
    fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id=<?= htmlspecialchars($fbPixelId) ?>&ev=PageView&noscript=1"/>
    </noscript>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/pages.css">

    <style>:root { --color-primary: <?= htmlspecialchars($siteColor) ?>; }</style>

    <?php if (!empty($pageExtraHead)) echo $pageExtraHead; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass ?? 'page') ?>">

<?php if ($gtmId): ?>
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId) ?>"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<?php endif; ?>

<?php require __DIR__ . '/header.php'; ?>

<main id="main-content">
    <?= $pageContent ?? '' ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<script src="/assets/js/app.js"></script>
<?php if (!empty($pageExtraJs)) echo $pageExtraJs; ?>

</body>
</html>
