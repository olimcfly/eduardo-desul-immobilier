<?php
declare(strict_types=1);

/**
 * Template page : LISTING (biens, catégories)
 */

$pageTitle = trim((string) ($page['meta_title'] ?? $page['title'] ?? 'Biens'));
$metaDesc = trim((string) ($page['meta_description'] ?? ''));
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
    <link rel="stylesheet" href="/assets/css/biens.css">
</head>
<body class="page-template page-template-listing">

<header class="site-header">
    <?php require ROOT_PATH . '/public/templates/layout/header-brand.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/navigation.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/header-ctas.php'; ?>
</header>

<main class="site-main">

    <!-- Hero listing -->
    <section class="section">
        <div class="container">
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($metaDesc !== ''): ?>
                <p><?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sections CMS -->
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