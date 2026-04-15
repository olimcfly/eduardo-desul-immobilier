<?php
$slug      = $slug ?? '';
$pageTitle = 'Actualité — Pascal Hamm Immobilier';
$metaDesc  = '';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];
$publicDomainImages = [
    'city' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Aix-en-Provence%20-%20Cours%20Mirabeau%20(2).jpg',
    'map'  => 'https://commons.wikimedia.org/wiki/Special:FilePath/Aix-en-Provence%20locator%20map.png',
];

$articles = [
    'prix-m2-etude-marche-aix-en-provence-via-perplexity' => [
        'titre'   => 'Prix au m² & étude de marché Aix-en-Provence : analyse assistée par Perplexity',
        'cat'     => 'Analyse IA',
        'date'    => '3 avril 2026',
        'auteur'  => 'Pascal Hamm',
        'img'     => $publicDomainImages['city'],
        'intro'   => "Cette page centralise les repères essentiels pour comprendre le marché local : prix au m², dynamique de demande et signaux d'évolution. Les recherches sont appuyées par Perplexity pour accélérer la veille et croiser les sources.",
        'sections' => [
            [
                'titre' => 'Prix au m² : repères rapides',
                'texte' => "Les niveaux de prix varient fortement selon la typologie du bien, l'état, l'étage et la proximité des transports. Utilisez ces fourchettes comme base de discussion avant une estimation précise sur place.",
                'points' => [
                    'Aix-en-Provence intra-muros : environ 3 900 € à 5 200 €/m².',
                    'Chartrons / Saint-Seurin : tension plus forte sur les biens familiaux bien rénovés.',
                    'Mérignac / Pessac : marché plus accessible avec une demande active sur les maisons.',
                ],
            ],
            [
                'titre' => 'Étude de marché : signaux à surveiller',
                'texte' => "Le marché 2026 reste sélectif : les biens correctement positionnés se vendent, tandis que les biens surévalués subissent des délais plus longs et des négociations plus marquées.",
                'points' => [
                    'Délai moyen de vente : variable selon le quartier et la qualité du dossier.',
                    'Négociation : plus présente que pendant les années de forte hausse.',
                    'Demande : retour progressif des acquéreurs solvables, surtout sur les biens prêts à habiter.',
                ],
            ],
            [
                'titre' => 'Analyse via Perplexity (recherches prêtes à l’emploi)',
                'texte' => "Pour une mise à jour continue, lancez directement ces recherches Perplexity et comparez les résultats avec les données de terrain.",
                'links' => [
                    ['label' => 'Prix au m² Aix-en-Provence par quartier', 'url' => 'https://www.perplexity.ai/search?q=prix+au+m2+Aix-en-Provence+par+quartier+2026'],
                    ['label' => 'Volume de transactions immobilières Aix-en-Provence 2026', 'url' => 'https://www.perplexity.ai/search?q=volume+transactions+immobilieres+Aix-en-Provence+2026'],
                    ['label' => 'Évolution des taux crédit immobilier France 2026', 'url' => 'https://www.perplexity.ai/search?q=evolution+taux+credit+immobilier+France+2026'],
                ],
            ],
        ],
    ],
    'default' => [
        'titre'   => 'Le marché immobilier aixois au T1 2026 : reprise prudente',
        'cat'     => 'Marché',
        'date'    => '2 avril 2026',
        'auteur'  => 'Pascal Hamm',
        'img'     => $publicDomainImages['map'],
        'intro'   => "Le premier trimestre 2026 confirme la tendance prudente d'un marché en cours de stabilisation après deux années de correction. Les volumes de transactions à Aix-en-Provence métropole restent en retrait de 12% par rapport à 2023, mais les premiers signes de reprise sont encourageants.",
        'sections' => [
            [
                'titre' => 'Des prix qui se stabilisent',
                'texte' => "Après une correction de 8 à 12% sur certains secteurs, les prix au m² semblent trouver un plancher. Le prix médian à Aix-en-Provence intra-muros s'établit autour de 4 200 €/m² pour les appartements, contre 4 800 €/m² au pic de 2022.",
            ],
            [
                'titre' => 'Les secteurs les plus dynamiques',
                'texte' => "Chartrons, Saint-Seurin et Cauderan résistent mieux que le reste de la métropole, portés par une demande soutenue de familles et de jeunes actifs. En périphérie, Mérignac et Pessac offrent des opportunités intéressantes.",
            ],
        ],
    ],
];
$article = $articles[$slug] ?? $articles['default'];
$pageTitle = e($article['titre']) . ' — Pascal Hamm';
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
                    <p><?= e($article['intro']) ?></p>
                    <?php foreach ($article['sections'] as $section): ?>
                        <h2><?= e($section['titre']) ?></h2>
                        <p><?= e($section['texte']) ?></p>
                        <?php if (!empty($section['points'])): ?>
                            <ul>
                                <?php foreach ($section['points'] as $point): ?>
                                    <li><?= e($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($section['links'])): ?>
                            <div style="display:grid;gap:.5rem;margin-top:.75rem">
                                <?php foreach ($section['links'] as $link): ?>
                                    <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn--outline btn--sm" style="justify-content:flex-start">
                                        🔎 <?= e($link['label']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
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
