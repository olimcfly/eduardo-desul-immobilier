<?php
$pageTitle = 'Guide local Bordeaux — Eduardo Desul Immobilier';
$metaDesc  = 'Découvrez les quartiers de Bordeaux et du Bordelais : prix, ambiance, transports, commerces. Guide local par Eduardo Desul.';
$extraCss  = ['/assets/css/guide.css'];

$villes = [
    ['slug' => 'merignac',            'nom' => 'Mérignac',        'prix' => '3 200 €/m²', 'desc' => 'Secteur prioritaire Bordeaux Métropole : habitat familial, accès tram et proximité aéroport.', 'biens' => 6, 'img' => '/assets/images/merignac.jpg'],
    ['slug' => 'bordeaux-centre',     'nom' => 'Bordeaux Centre', 'prix' => '4 800 €/m²', 'desc' => 'Le cœur historique de Bordeaux, classé UNESCO. Animation et prestige.', 'biens' => 12, 'img' => '/assets/images/bordeaux-centre.jpg'],
    ['slug' => 'bordeaux-cauderan',   'nom' => 'Cauderan',        'prix' => '3 900 €/m²', 'desc' => 'Quartier résidentiel calme, idéal pour les familles avec maisons et jardins.', 'biens' => 6, 'img' => '/assets/images/pessac.jpg'],
    ['slug' => 'bordeaux-chartrons',  'nom' => 'Chartrons',       'prix' => '4 600 €/m²', 'desc' => 'Quartier bohème et branché, très prisé des jeunes actifs et des familles.', 'biens' => 8, 'img' => '/assets/images/chartrons.jpg'],
    ['slug' => 'pessac',              'nom' => 'Pessac',          'prix' => '2 900 €/m²', 'desc' => 'Ville universitaire avec une belle diversité de biens et une vraie vie de quartier.', 'biens' => 5, 'img' => '/assets/images/pessac.jpg'],
    ['slug' => 'talence',             'nom' => 'Talence',         'prix' => '3 100 €/m²', 'desc' => 'Résidentielle et verte, proche des campus et bien desservie par le tram.', 'biens' => 4, 'img' => '/assets/images/bordeaux-centre.jpg'],
];
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Guide local</span></nav>
            <span class="section-label">Bordeaux & métropole</span>
            <h1>Guide local des quartiers bordelais</h1>
            <p>Prix au m², ambiance, transports, commerces — mon analyse terrain de chaque secteur pour vous aider à faire le bon choix.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Estimer mon bien</a>
                <a href="/biens" class="btn btn--outline">Voir les annonces</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong><?= count($villes) ?>+</strong><span>quartiers analysés</span></div>
            <div class="blog-hero__metric"><strong>Terrain</strong><span>connaissance locale</span></div>
            <div class="blog-hero__metric"><strong>Gratuit</strong><span>accès illimité</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Bordeaux & métropole</span>
            <h2 class="section-title">Choisissez votre quartier</h2>
            <p class="section-subtitle">Prix au m², cadre de vie, accès, services — mon analyse terrain de chaque secteur.</p>
        </div>

        <div class="villes-grid" data-animate>
            <?php foreach ($villes as $v): ?>
            <a href="/guide-local/<?= e($v['slug']) ?>" class="ville-card">
                <img src="<?= e($v['img']) ?>" alt="Immobilier <?= e($v['nom']) ?>" loading="lazy" width="400" height="300">
                <div class="ville-card__overlay">
                    <div class="ville-card__name"><?= e($v['nom']) ?></div>
                    <div class="ville-card__count">À partir de <?= e($v['prix']) ?> · <?= $v['biens'] ?> biens</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Tableau comparatif -->
        <div style="margin-top:4rem" data-animate>
            <h2 style="margin-bottom:1.5rem">Comparatif des secteurs</h2>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;background:var(--clr-white);border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--clr-border)">
                    <thead style="background:var(--clr-primary);color:white">
                        <tr>
                            <?php foreach (['Quartier', 'Prix médian', 'Tendance', 'Atout', 'Famille', 'Investissement'] as $col): ?>
                            <th style="padding:.75rem 1rem;text-align:left;font-size:.85rem;font-weight:600"><?= $col ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tableau = [
                            ['Chartrons', '4 600 €/m²', '↗ +2%', 'Vie de quartier', '⭐⭐⭐', '⭐⭐⭐⭐'],
                            ['Centre', '4 800 €/m²', '→ stable', 'Prestige', '⭐⭐', '⭐⭐⭐⭐⭐'],
                            ['Cauderan', '3 900 €/m²', '↗ +3%', 'Calme & vert', '⭐⭐⭐⭐⭐', '⭐⭐⭐'],
                            ['Mérignac', '3 200 €/m²', '↗ +5%', 'Accessibilité', '⭐⭐⭐⭐', '⭐⭐⭐⭐'],
                            ['Pessac', '2 900 €/m²', '↗ +4%', 'Université', '⭐⭐⭐', '⭐⭐⭐⭐⭐'],
                            ['Talence', '3 100 €/m²', '→ +1%', 'Résidentiel', '⭐⭐⭐⭐', '⭐⭐⭐'],
                        ];
                        foreach ($tableau as $i => $row): ?>
                        <tr style="border-bottom:1px solid var(--clr-border);<?= $i % 2 === 0 ? 'background:var(--clr-bg)' : '' ?>">
                            <?php foreach ($row as $j => $cell): ?>
                            <td style="padding:.75rem 1rem;font-size:.875rem;<?= $j === 0 ? 'font-weight:600;color:var(--clr-primary)' : '' ?>"><?= $cell ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
