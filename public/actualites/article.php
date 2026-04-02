<?php
$slug      = $slug ?? '';
$pageTitle = 'Actualité — Eduardo Desul Immobilier';
$metaDesc  = '';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];

$article = [
    'titre'   => 'Le marché immobilier bordelais au T1 2026 : reprise prudente',
    'cat'     => 'Marché',
    'date'    => '2 avril 2026',
    'auteur'  => 'Eduardo Desul',
    'img'     => '/assets/images/blog-1.jpg',
];
$pageTitle = e($article['titre']) . ' — Eduardo Desul';
?>

<div class="page-header" style="padding-bottom:2rem">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/actualites">Actualités</a>
            <span><?= e($article['cat']) ?></span>
        </nav>
    </div>
</div>

<section class="section" style="padding-top:2rem">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-header">
                    <span class="article-card__cat"><?= e($article['cat']) ?></span>
                    <h1><?= e($article['titre']) ?></h1>
                    <div class="article-meta">
                        <span>✍️ <?= e($article['auteur']) ?></span>
                        <span>📅 <?= e($article['date']) ?></span>
                    </div>
                </div>
                <div class="article-cover">
                    <img src="<?= e($article['img']) ?>" alt="<?= e($article['titre']) ?>" width="800" height="450">
                </div>
                <div class="article-content">
                    <p>Le premier trimestre 2026 confirme la tendance prudente d'un marché en cours de stabilisation après deux années de correction. Les volumes de transactions à Bordeaux métropole restent en retrait de 12% par rapport à 2023, mais les premiers signes de reprise sont encourageants.</p>
                    <h2>Des prix qui se stabilisent</h2>
                    <p>Après une correction de 8 à 12% sur certains secteurs, les prix au m² semblent trouver un plancher. Le prix médian à Bordeaux intra-muros s'établit autour de 4 200 €/m² pour les appartements, contre 4 800 €/m² au pic de 2022.</p>
                    <h2>Les secteurs les plus dynamiques</h2>
                    <p>Chartrons, Saint-Seurin et Cauderan résistent mieux que le reste de la métropole, portés par une demande soutenue de familles et de jeunes actifs. En périphérie, Mérignac et Pessac offrent des opportunités intéressantes.</p>
                </div>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:1.5rem 0;border-top:1px solid var(--clr-border);margin-top:2rem">
                    <span style="font-weight:600;font-size:.875rem">Partager :</span>
                    <button data-share="facebook" class="btn btn--outline btn--sm">Facebook</button>
                    <button data-share="linkedin" class="btn btn--outline btn--sm">LinkedIn</button>
                </div>
            </div>
            <aside class="blog-sidebar">
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <h4 style="color:white;margin-bottom:.75rem">Estimation gratuite</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Votre bien vaut-il plus ou moins en 2026 ?</p>
                    <a href="/estimation-gratuite" class="btn btn--accent btn--sm btn--full">Estimer mon bien</a>
                </div>
            </aside>
        </div>
    </div>
</section>
