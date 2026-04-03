<?php
$slug      = $slug ?? 'merignac';
$pageTitle = 'Guide du quartier — Eduardo Desul Immobilier';
$metaDesc  = '';
$extraCss  = ['/assets/css/guide.css', '/assets/css/biens.css'];

$secteurs = [
    'merignac' => [
        'nom' => 'Mérignac',
        'prix' => '3 200',
        'tendance' => '↗ +4%',
        'delai' => '49 jours',
        'biens' => 6,
        'img' => '/assets/images/merignac.jpg',
        'desc' => 'Mérignac combine quartiers résidentiels, zones d\'activité et accès rapide à Bordeaux centre. Le secteur attire les familles et les actifs qui recherchent plus d\'espace, avec un bon niveau de services de proximité.',
        'marche' => 'Le marché à Mérignac reste recherché pour les maisons familiales et les appartements proches du tram A. La demande est soutenue dans les secteurs Arlac, Capeyron et autour du centre-ville, avec une dynamique plus stable que l\'hypercentre bordelais.',
        'transports' => 'Le tram A, les lignes de bus TBM et la proximité de la rocade facilitent les déplacements. L\'accès à l\'aéroport de Bordeaux-Mérignac est un atout pour les professionnels et les investisseurs locatifs.',
        'commerces' => 'Le secteur dispose de commerces de proximité, d\'artisans et d\'entreprises spécialisées dans l\'habitat (cuisine, salles de bain, matériaux, rénovation énergétique).',
        'habitat_pros' => [
            [
                'nom' => 'Leroy Merlin Mérignac',
                'categorie' => 'Bricolage & rénovation',
                'zone' => 'Mérignac Soleil',
                'note' => 'Large choix matériaux, outillage, aménagement intérieur.',
            ],
            [
                'nom' => 'Lapeyre Mérignac',
                'categorie' => 'Menuiseries & cuisine',
                'zone' => 'Avenue de la Somme',
                'note' => 'Portes, fenêtres, cuisines et solutions sur mesure.',
            ],
            [
                'nom' => 'Point.P Mérignac',
                'categorie' => 'Matériaux de construction',
                'zone' => 'Zone du Phare',
                'note' => 'Fournitures gros œuvre et second œuvre pour chantiers.',
            ],
            [
                'nom' => 'CEDEO Mérignac',
                'categorie' => 'Plomberie & chauffage',
                'zone' => 'Parc d\'activités',
                'note' => 'Sanitaire, chauffage et conseil technique habitat.',
            ],
            [
                'nom' => 'Schmidt Mérignac',
                'categorie' => 'Cuisine & rangements',
                'zone' => 'Secteur Chemin Long',
                'note' => 'Conception et pose de cuisines et dressing.',
            ],
            [
                'nom' => 'Tryba Mérignac',
                'categorie' => 'Fenêtres & rénovation énergétique',
                'zone' => 'Axe Mérignac-Pessac',
                'note' => 'Menuiseries extérieures, isolation et confort thermique.',
            ],
        ],
    ],
    'bordeaux-chartrons' => [
        'nom' => 'Les Chartrons',
        'prix' => '4 600',
        'tendance' => '↗ +3%',
        'delai' => '45 jours',
        'biens' => 8,
        'img' => '/assets/images/chartrons.jpg',
        'desc' => 'Quartier historique des marchands de vin, les Chartrons sont aujourd\'hui l\'un des secteurs les plus prisés de Bordeaux. Mêlant patrimoine architectural et vie de quartier animée, il attire aussi bien les familles que les jeunes actifs.',
        'marche' => 'Les prix immobiliers aux Chartrons ont connu une légère correction en 2024-2025 (-5 à -8%), mais restent parmi les plus élevés de Bordeaux. La demande est soutenue, notamment pour les appartements avec caractère (moulures, parquet, hauteur sous plafond).',
        'transports' => 'Excellente desserte par le tramway (lignes A et B). Le quartier est également très cyclable, avec de nombreuses pistes aménagées. Parking difficile mais peu nécessaire.',
        'commerces' => 'Le quartier dispose de tous les commerces du quotidien, marchés hebdomadaires, nombreux restaurants et cafés. Plusieurs écoles publiques et privées à proximité.',
        'habitat_pros' => [],
    ],
];

$ville = $secteurs[$slug] ?? $secteurs['bordeaux-chartrons'];
$pageTitle = 'Immobilier ' . $ville['nom'] . ' — Eduardo Desul';
$metaDesc  = 'Guide immobilier ' . $ville['nom'] . ' : prix au m², tendances, quartier. Par Eduardo Desul, expert local.';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/guide-local">Guide local</a>
            <span><?= e($ville['nom']) ?></span>
        </nav>
        <h1>Immobilier <?= e($ville['nom']) ?></h1>
        <p><?= e(mb_strimwidth($ville['desc'], 0, 120, '…')) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-cover"><img src="<?= e($ville['img']) ?>" alt="<?= e($ville['nom']) ?>" width="800" height="400"></div>

                <div class="grid-4" style="margin-bottom:2.5rem" data-animate>
                    <?php foreach ([
                        ['💰', $ville['prix'] . ' €/m²', 'Prix médian'],
                        ['📈', $ville['tendance'], 'Tendance 12 mois'],
                        ['⏱', $ville['delai'], 'Délai de vente moyen'],
                        ['🏠', $ville['biens'], 'Biens disponibles'],
                    ] as [$icon, $val, $lab]): ?>
                    <div style="background:var(--clr-white);border-radius:var(--radius-lg);border:1px solid var(--clr-border);padding:1.25rem;text-align:center">
                        <div style="font-size:1.5rem"><?= $icon ?></div>
                        <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--clr-primary)"><?= $val ?></div>
                        <div style="font-size:.75rem;color:var(--clr-text-muted)"><?= $lab ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="article-content">
                    <h2>Présentation du quartier</h2>
                    <p><?= e($ville['desc']) ?></p>
                    <h2>Le marché immobilier</h2>
                    <p><?= e($ville['marche']) ?></p>
                    <h2>Transports & accessibilité</h2>
                    <p><?= e($ville['transports']) ?></p>
                    <h2>Commerces & services</h2>
                    <p><?= e($ville['commerces']) ?></p>
                </div>

                <?php if (!empty($ville['habitat_pros'])): ?>
                <div style="margin-top:2rem">
                    <h2>Commerçants & entreprises habitat à <?= e($ville['nom']) ?></h2>
                    <p style="color:var(--clr-text-muted);font-size:.9rem">Sélection locale orientée habitat (rénovation, aménagement, matériaux), priorisée pour l\'accompagnement des projets immobiliers.</p>
                    <div style="display:grid;gap:1rem;margin-top:1rem">
                        <?php foreach ($ville['habitat_pros'] as $pro): ?>
                        <article style="background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1rem 1.25rem">
                            <h3 style="margin:0 0 .4rem 0;font-size:1.05rem"><?= e($pro['nom']) ?></h3>
                            <p style="margin:0 0 .35rem 0;font-size:.86rem"><strong>Catégorie :</strong> <?= e($pro['categorie']) ?></p>
                            <p style="margin:0 0 .35rem 0;font-size:.86rem"><strong>Zone :</strong> <?= e($pro['zone']) ?></p>
                            <p style="margin:0;font-size:.86rem;color:var(--clr-text-muted)"><?= e($pro['note']) ?></p>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top:2rem">
                    <h2>Biens disponibles à <?= e($ville['nom']) ?></h2>
                    <a href="/biens?secteur=<?= e($slug) ?>" class="btn btn--primary" style="margin-top:1rem">Voir les <?= $ville['biens'] ?> annonces →</a>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem">
                    <h4 style="color:white;margin-bottom:.75rem">Estimer votre bien ici</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Vous avez un bien à <?= e($ville['nom']) ?> ? Obtenez une estimation gratuite.</p>
                    <a href="/estimation-gratuite?secteur=<?= e($slug) ?>" class="btn btn--accent btn--sm btn--full">Estimation gratuite</a>
                </div>
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Autres quartiers</div>
                    <div class="sidebar-box__body">
                        <?php foreach ([['Bordeaux Centre', 'bordeaux-centre'], ['Cauderan', 'bordeaux-cauderan'], ['Mérignac', 'merignac'], ['Pessac', 'pessac']] as [$n, $s]): ?>
                        <a href="/guide-local/<?= e($s) ?>" style="display:block;padding:.5rem 0;font-size:.875rem;border-bottom:1px solid var(--clr-border);color:var(--clr-text)">📍 <?= e($n) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
