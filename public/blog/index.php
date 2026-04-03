<?php
$pageTitle = 'Blog immobilier — Eduardo Desul';
$metaDesc  = 'Conseils pratiques, analyses du marché et guides immobiliers pour vendre, acheter et investir à Bordeaux.';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];

$articles = [
    ['slug' => 'preparer-vente-bien', 'cat' => 'Vente', 'titre' => 'Comment bien préparer la vente de votre bien', 'excerpt' => 'Les 5 étapes essentielles pour maximiser la valeur de votre propriété avant de la mettre sur le marché.', 'date' => '28 mars 2026', 'lecture' => '5 min', 'img' => '/assets/images/blog-1.jpg'],
    ['slug' => 'investir-bordeaux-2026', 'cat' => 'Investissement', 'titre' => 'Investir à Bordeaux en 2026 : les quartiers à suivre', 'excerpt' => 'Analyse des tendances du marché bordelais et des secteurs offrant le meilleur rapport rendement/risque.', 'date' => '15 mars 2026', 'lecture' => '7 min', 'img' => '/assets/images/blog-2.jpg'],
    ['slug' => 'taux-immobiliers-2026', 'cat' => 'Financement', 'titre' => 'Taux immobiliers : ce qui change en 2026', 'excerpt' => 'Comprendre l\'évolution des taux pour optimiser votre plan de financement.', 'date' => '5 mars 2026', 'lecture' => '4 min', 'img' => '/assets/images/blog-3.jpg'],
    ['slug' => 'dpe-nouveau-calcul', 'cat' => 'Réglementation', 'titre' => 'Nouveau DPE : ce que ça change pour votre bien', 'excerpt' => 'Les nouvelles règles du diagnostic de performance énergétique et leur impact sur la valeur immobilière.', 'date' => '20 février 2026', 'lecture' => '6 min', 'img' => '/assets/images/blog-1.jpg'],
    ['slug' => 'negocier-prix-achat', 'cat' => 'Achat', 'titre' => '7 techniques pour négocier le prix d\'achat', 'excerpt' => 'Comment obtenir le meilleur prix lors de l\'achat d\'un bien immobilier sans vexer le vendeur.', 'date' => '10 février 2026', 'lecture' => '5 min', 'img' => '/assets/images/blog-2.jpg'],
    ['slug' => 'diagnostics-obligatoires-vente', 'cat' => 'Vente', 'titre' => 'Les diagnostics obligatoires avant de vendre', 'excerpt' => 'Liste complète des diagnostics immobiliers exigés lors d\'une vente et comment les organiser.', 'date' => '1er février 2026', 'lecture' => '4 min', 'img' => '/assets/images/blog-3.jpg'],
];

$categories = ['Tous', 'Vente', 'Achat', 'Investissement', 'Financement', 'Réglementation'];
$activeCat = trim((string) ($_GET['cat'] ?? ''));
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Blog</span></nav>
            <span class="section-label">Conseils immobiliers</span>
            <h1>Le blog immobilier d'Eduardo Desul</h1>
            <p>Retrouvez des conseils concrets, des analyses locales et des réponses simples pour réussir votre projet immobilier à Bordeaux.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Demander une estimation</a>
                <a href="/contact" class="btn btn--outline">Poser une question</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong>+120</strong><span>articles & guides</span></div>
            <div class="blog-hero__metric"><strong>4.9/5</strong><span>avis lecteurs</span></div>
            <div class="blog-hero__metric"><strong>100%</strong><span>contenu terrain</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-toolbar">
            <div class="tags">
                <?php foreach ($categories as $cat):
                    $isActive = ($cat === 'Tous' && $activeCat === '') || strcasecmp($activeCat, $cat) === 0;
                    $href = $cat === 'Tous' ? '/blog' : '/blog?cat=' . urlencode(strtolower($cat));
                ?>
                    <a href="<?= e($href) ?>" class="tag <?= $isActive ? 'tag--active' : '' ?>"><?= e($cat) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="blog-grid">
            <?php foreach ($articles as $art):
                $imgFile = defined('PUBLIC_PATH') ? PUBLIC_PATH . $art['img'] : __DIR__ . '/../..' . $art['img'];
                $imgSrc  = file_exists($imgFile)
                    ? e($art['img'])
                    : '/assets/images/placeholder.php?type=article&label=' . urlencode($art['cat']);
            ?>
            <article class="article-card" data-animate>
                <div class="article-card__img">
                    <img src="<?= $imgSrc ?>" alt="<?= e($art['titre']) ?>" loading="lazy" width="400" height="225">
                </div>
                <div class="article-card__body">
                    <span class="article-card__cat"><?= e($art['cat']) ?></span>
                    <h2 class="article-card__title">
                        <a href="/blog/<?= e($art['slug']) ?>"><?= e($art['titre']) ?></a>
                    </h2>
                    <p class="article-card__excerpt"><?= e($art['excerpt']) ?></p>
                    <div class="article-card__meta">
                        <span>📅 <?= e($art['date']) ?></span>
                        <span>⏱ <?= e($art['lecture']) ?> de lecture</span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="blog-cta" data-animate>
            <div>
                <h3>Vous avez un projet immobilier en 2026 ?</h3>
                <p>Parlez de votre situation avec Eduardo Desul et recevez des conseils personnalisés en moins de 24h.</p>
            </div>
            <a href="/contact" class="btn btn--accent">Être rappelé</a>
        </div>
    </div>
</section>
