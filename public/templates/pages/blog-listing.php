<?php
declare(strict_types=1);

/**
 * Template page : BLOG LISTING
 *
 * Fichier :
 * /public/templates/pages/blog-listing.php
 */

$pageTitle = trim((string) ($page['meta_title'] ?? $page['title'] ?? 'Blog'));
$metaDesc  = trim((string) ($page['meta_description'] ?? ''));

/**
 * Optionnel :
 * si un controller injecte une liste d’articles, on l’utilise.
 * sinon on laisse le CMS afficher ses sections.
 */
$posts = $posts ?? [];
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
</head>
<body class="page-template page-template-blog-listing">

<header class="site-header">
    <?php require ROOT_PATH . '/public/templates/layout/header-brand.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/navigation.php'; ?>
    <?php require ROOT_PATH . '/public/templates/layout/header-ctas.php'; ?>
</header>

<main class="site-main">

    <section class="section">
        <div class="container">
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($metaDesc !== ''): ?>
                <p><?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($posts)): ?>
        <section class="section">
            <div class="container">
                <div class="grid-3">
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $title = (string) ($post['title'] ?? 'Article');
                        $excerpt = (string) ($post['excerpt'] ?? '');
                        $slug = (string) ($post['slug'] ?? '#');
                        $image = (string) ($post['image'] ?? '/assets/images/placeholder.php');
                        $url = str_starts_with($slug, '/') ? $slug : '/blog/' . $slug;
                        ?>
                        <article class="card">
                            <img class="card__img" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <div class="card__body">
                                <h2 class="card__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                                <?php if ($excerpt !== ''): ?>
                                    <p class="card__text"><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <a class="btn btn--primary btn--sm" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">Lire l’article</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

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