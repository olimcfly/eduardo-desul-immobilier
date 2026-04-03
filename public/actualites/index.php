<?php
$pageTitle = 'Actualités immobilières — Eduardo Desul';
$metaDesc  = 'Suivez l\'actualité du marché immobilier bordelais avec Eduardo Desul.';
$extraCss  = ['/assets/css/guide.css'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Actualités</span></nav>
        <h1>Actualités immobilières</h1>
        <p>Restez informé des dernières nouvelles du marché immobilier à Bordeaux et en France.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="blog-grid" style="grid-template-columns:1fr">
                    <?php
                    $actus = [
                        ['slug' => 'prix-m2-etude-marche-bordeaux-via-perplexity', 'cat' => 'Analyse IA', 'titre' => 'Prix au m² & étude de marché Bordeaux : analyse assistée par Perplexity', 'excerpt' => 'Une synthèse claire des prix au m², des tendances quartier par quartier et des signaux de marché à suivre pour vendre ou acheter en 2026.', 'date' => '3 avril 2026', 'img' => '/assets/images/blog-2.jpg'],
                        ['slug' => 'marche-immobilier-bordeaux-t1-2026', 'cat' => 'Marché', 'titre' => 'Le marché immobilier bordelais au T1 2026 : reprise prudente', 'excerpt' => 'Après un ralentissement en 2025, les premiers signes de reprise se confirment sur le marché bordelais. Analyse des indicateurs clés.', 'date' => '2 avril 2026', 'img' => '/assets/images/blog-1.jpg'],
                        ['slug' => 'ptz-prolonge-2026', 'cat' => 'Financement', 'titre' => 'PTZ élargi : les nouvelles conditions d\'éligibilité', 'excerpt' => 'Le Prêt à Taux Zéro est prolongé et ses conditions modifiées. Ce que ça change pour les primo-accédants en 2026.', 'date' => '18 mars 2026', 'img' => '/assets/images/blog-2.jpg'],
                        ['slug' => 'barometre-prix-bordeaux-2026', 'cat' => 'Prix', 'titre' => 'Baromètre des prix : Bordeaux en détail par quartier', 'excerpt' => 'Tour d\'horizon complet des prix au m² dans les différents quartiers et communes du Bordelais au premier trimestre 2026.', 'date' => '1er mars 2026', 'img' => '/assets/images/blog-3.jpg'],
                    ];
                    foreach ($actus as $a): ?>
                    <article class="article-card" style="display:grid;grid-template-columns:200px 1fr;gap:0">
                        <div class="article-card__img" style="aspect-ratio:auto;height:100%">
                            <img src="<?= e($a['img']) ?>" alt="<?= e($a['titre']) ?>" loading="lazy" width="200" height="150" style="height:100%;width:100%;object-fit:cover">
                        </div>
                        <div class="article-card__body">
                            <span class="article-card__cat"><?= e($a['cat']) ?></span>
                            <h2 class="article-card__title"><a href="/actualites/<?= e($a['slug']) ?>"><?= e($a['titre']) ?></a></h2>
                            <p class="article-card__excerpt"><?= e($a['excerpt']) ?></p>
                            <div class="article-card__meta">
                                <span>📅 <?= e($a['date']) ?></span>
                                <a href="/actualites/<?= e($a['slug']) ?>" style="color:var(--clr-primary);font-weight:600">Lire →</a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Catégories</div>
                    <div class="sidebar-box__body">
                        <div class="tags">
                            <?php foreach (['Marché', 'Financement', 'Prix', 'Réglementation', 'Bordeaux'] as $tag): ?>
                            <a href="?cat=<?= urlencode($tag) ?>" class="tag"><?= $tag ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <h4 style="color:white;margin-bottom:.75rem">Newsletter</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Recevez les actualités immobilières bordelaises chaque semaine.</p>
                    <a href="/capture/guide-offert" class="btn btn--accent btn--sm btn--full">S'abonner gratuitement</a>
                </div>
            </aside>
        </div>
    </div>
</section>
