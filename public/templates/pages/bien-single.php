<?php
declare(strict_types=1);

/**
 * Template page : BIEN SINGLE (détail d’un bien)
 *
 * Fichier :
 * /public/templates/pages/bien-single.php
 *
 * Variables possibles :
 * - $property (si injecté via controller)
 * - sinon fallback via $page + sections CMS
 */

$pageTitle = trim((string) ($page['meta_title'] ?? $page['title'] ?? 'Détail du bien'));
$metaDesc  = trim((string) ($page['meta_description'] ?? ''));

$property = $property ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if ($metaDesc !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/nav.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/bien-detail.css">
</head>
<body class="page-template page-template-bien-single">

<header class="site-header">
    <?php require ROOT_PATH . '/public/templates/layout/header-brand.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/navigation.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/header-ctas.php'; ?>
</header>

<main class="site-main">

    <?php if ($property): ?>

        <!-- HERO BIEN -->
        <section class="section">
            <div class="container">
                <h1><?= htmlspecialchars($property['title'] ?? 'Bien immobilier', ENT_QUOTES, 'UTF-8') ?></h1>
                <p>
                    <?= htmlspecialchars($property['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($property['price'])): ?>
                        · <strong><?= htmlspecialchars($property['price'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php endif; ?>
                </p>
            </div>
        </section>

        <!-- GALERIE -->
        <?php if (!empty($property['images'])): ?>
        <section class="section">
            <div class="container">
                <div class="gallery">
                    <?php foreach ($property['images'] as $img): ?>
                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- INFOS -->
        <section class="section">
            <div class="container">
                <div class="grid-2">
                    <div>
                        <h2>Description</h2>
                        <p><?= nl2br(htmlspecialchars($property['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                    <div>
                        <h2>Caractéristiques</h2>
                        <ul>
                            <?php if (!empty($property['surface'])): ?>
                                <li>Surface : <?= htmlspecialchars($property['surface']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($property['rooms'])): ?>
                                <li>Pièces : <?= htmlspecialchars($property['rooms']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($property['bedrooms'])): ?>
                                <li>Chambres : <?= htmlspecialchars($property['bedrooms']) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <!-- SECTIONS CMS -->
    <?php foreach ($sections as $section): ?>
        <?php SectionRenderer::render($section, $siteSettings); ?>
    <?php endforeach; ?>

</main>

<footer class="site-footer">
    <?php require ROOT_PATH . '/public/templates/layout/footer-brand.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/footer-links.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/footer-legal.php'; ?>
</footer>

</body>
</html>