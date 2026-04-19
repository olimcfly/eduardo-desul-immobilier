<?php
$pageTitle = 'Actualités immobilières — ' . ADVISOR_NAME;
$metaDesc  = 'Suivez l\'actualité du marché immobilier bordelais avec <?= ADVISOR_NAME ?>.';
$extraCss  = ['/assets/css/guide.css'];
$publicDomainImages = [
    'city' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Bordeaux_Miroir_d%27eau.jpg/800px-Bordeaux_Miroir_d%27eau.jpg',
    'keys' => 'https://commons.wikimedia.org/wiki/Special:FilePath/House%20keys.jpg',
    'map'  => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Bordeaux_metropole_map.svg/600px-Bordeaux_metropole_map.svg.png',
];

$actus = [
    ['slug' => 'prix-m2-etude-marche-bordeaux-via-perplexity', 'cat' => 'Analyse IA', 'titre' => 'Prix au m² & étude de marché Bordeaux : analyse assistée par Perplexity', 'excerpt' => 'Une synthèse claire des prix au m², des tendances quartier par quartier et des signaux de marché à suivre pour vendre ou acheter en 2026.', 'date' => '3 avril 2026', 'img' => $publicDomainImages['city']],
    ['slug' => 'marche-immobilier-bordeaux-t1-2026', 'cat' => 'Marché', 'titre' => 'Le marché immobilier bordelais au T1 2026 : reprise prudente', 'excerpt' => 'Après un ralentissement en 2025, les premiers signes de reprise se confirment sur le marché bordelais. Analyse des indicateurs clés.', 'date' => '2 avril 2026', 'img' => $publicDomainImages['map']],
    ['slug' => 'ptz-prolonge-2026', 'cat' => 'Financement', 'titre' => 'PTZ élargi : les nouvelles conditions d\'éligibilité', 'excerpt' => 'Le Prêt à Taux Zéro est prolongé et ses conditions modifiées. Ce que ça change pour les primo-accédants en 2026.', 'date' => '18 mars 2026', 'img' => $publicDomainImages['keys']],
    ['slug' => 'barometre-prix-bordeaux-2026', 'cat' => 'Prix', 'titre' => 'Baromètre des prix : Bordeaux en détail par quartier', 'excerpt' => 'Tour d\'horizon complet des prix au m² dans les différents quartiers et communes de la Métropole bordelaise au premier trimestre 2026.', 'date' => '1er mars 2026', 'img' => $publicDomainImages['city']],
];
$categories = ['Tous', 'Marché', 'Financement', 'Prix', 'Réglementation', 'Bordeaux', 'Analyse IA'];
$activeCat = trim((string) ($_GET['cat'] ?? ''));
$filteredActus = array_values(array_filter(
    $actus,
    static function (array $news) use ($activeCat): bool {
        if ($activeCat === '') {
            return true;
        }

        return strcasecmp($news['cat'], $activeCat) === 0;
    }
));
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Actualités</span></nav>
            <span class="section-label">Marché immobilier</span>
            <h1>Actualités immobilières Bordeaux</h1>
            <p>Prix, tendances, réglementations — restez informé des dernières évolutions du marché bordelais et français.</p>
            <div class="blog-hero__actions">
                <a href="/guide-offert" class="btn btn--accent">Recevoir la newsletter</a>
                <a href="/blog" class="btn btn--outline">Voir le blog</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong>Hebdo</strong><span>mise à jour du marché</span></div>
            <div class="blog-hero__metric"><strong>100%</strong><span>sources officielles</span></div>
            <div class="blog-hero__metric"><strong>Local</strong><span>focus Bordeaux Métropole</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-toolbar">
            <div class="tags">
                <?php foreach ($categories as $cat):
                    $isActive = ($cat === 'Tous' && $activeCat === '') || strcasecmp($activeCat, $cat) === 0;
                    $href = $cat === 'Tous' ? '/actualites' : '/actualites?cat=' . urlencode(strtolower($cat));
                ?>
                    <a href="<?= e($href) ?>" class="tag <?= $isActive ? 'tag--active' : '' ?>"><?= e($cat) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="blog-toolbar__count"><?= count($filteredActus) ?> actualité<?= count($filteredActus) > 1 ? 's' : '' ?></p>
        </div>

        <div class="article-layout">
            <div>
                <div class="blog-grid blog-grid--news">
                    <?php if (empty($filteredActus)): ?>
                    <article class="article-card article-card--empty">
                        <div class="article-card__body">
                            <span class="article-card__cat">Aucune actualité</span>
                            <h2 class="article-card__title">Aucun résultat pour cette catégorie</h2>
                            <p class="article-card__excerpt">Essayez une autre catégorie ou revenez sur "Tous" pour afficher l'ensemble des actualités.</p>
                        </div>
                    </article>
                    <?php endif; ?>

                    <?php foreach ($filteredActus as $a): ?>
                    <article class="article-card article-card--news">
                        <div class="article-card__img article-card__img--news">
                            <img src="<?= e($a['img']) ?>" alt="<?= e($a['titre']) ?>" loading="lazy" width="200" height="150">
                        </div>
                        <div class="article-card__body">
                            <span class="article-card__cat"><?= e($a['cat']) ?></span>
                            <h2 class="article-card__title"><a href="/actualites/<?= e($a['slug']) ?>"><?= e($a['titre']) ?></a></h2>
                            <p class="article-card__excerpt"><?= e($a['excerpt']) ?></p>
                            <div class="article-card__meta">
                                <span>📅 <?= e($a['date']) ?></span>
                                <a href="/actualites/<?= e($a['slug']) ?>" class="article-card__readmore">Lire →</a>
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
                            <?php foreach ($categories as $tag):
                                if ($tag === 'Tous') {
                                    continue;
                                }
                            ?>
                            <a href="/actualites?cat=<?= urlencode(strtolower($tag)) ?>" class="tag"><?= e($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <h4 style="color:white;margin-bottom:.75rem">Newsletter</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Recevez les actualités immobilières bordelaises chaque semaine.</p>
                    <a href="/guide-offert" class="btn btn--accent btn--sm btn--full">S'abonner gratuitement</a>
                </div>
            </aside>
        </div>
    </div>
</section>
