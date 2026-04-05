<?php
$pageTitle = 'Blog immobilier — Eduardo Desul';
$metaDesc  = 'Conseils et actualités du marché immobilier bordelais';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];

require_once ROOT_PATH . '/core/helpers/cms.php';
require_once ROOT_PATH . '/core/helpers/articles.php';

$hero = get_page_content('blog', 'hero');
$articles = get_articles_list();
?>

<section class="hero">
    <div class="container">
        <h1><?= e($hero['title'] ?? 'Blog immobilier') ?></h1>
        <p><?= e($hero['subtitle'] ?? 'Conseils et actualités du marché immobilier bordelais') ?></p>
    </div>
</section>

<section class="articles-list section">
    <div class="container">
        <?php if (empty($articles)): ?>
            <p>Aucun article publié pour le moment.</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <h2><?= e($article['title']) ?></h2>
                    <p><?= e($article['excerpt']) ?></p>
                    <a href="/blog/<?= rawurlencode((string) $article['slug']) ?>" class="btn">Lire la suite</a>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
