<?php
$pageTitle = 'Guide local Bordeaux — Eduardo Desul Immobilier';
$metaDesc  = 'Découvrez les quartiers de Bordeaux et du Bordelais : prix, ambiance, transports, commerces. Guide local par Eduardo Desul.';
$extraCss  = ['/assets/css/guide.css'];

$villes = [
    ['slug' => 'bordeaux-centre',    'nom' => 'Bordeaux Centre', 'prix' => '4 800 €/m²', 'desc' => 'Le cœur historique classé UNESCO. Prestige, animation et architecture exceptionnelle.', 'biens' => 12, 'img' => '/assets/images/bordeaux-centre.jpg'],
    ['slug' => 'bordeaux-chartrons', 'nom' => 'Chartrons',       'prix' => '4 600 €/m²', 'desc' => 'Quartier bohème et branché, très prisé des jeunes actifs et des familles.', 'biens' => 8,  'img' => '/assets/images/chartrons.jpg'],
    ['slug' => 'bordeaux-cauderan',  'nom' => 'Caudéran',        'prix' => '3 900 €/m²', 'desc' => 'Quartier résidentiel calme, idéal pour les familles avec maisons et jardins.', 'biens' => 6,  'img' => '/assets/images/cauderan.jpg'],
    ['slug' => 'merignac',           'nom' => 'Mérignac',        'prix' => '3 200 €/m²', 'desc' => 'Secteur prioritaire Bordeaux Métropole : habitat familial, tram et proximité aéroport.', 'biens' => 6,  'img' => '/assets/images/merignac.jpg'],
    ['slug' => 'pessac',             'nom' => 'Pessac',          'prix' => '2 900 €/m²', 'desc' => 'Ville universitaire avec une belle diversité de biens et une vraie vie de quartier.', 'biens' => 5,  'img' => '/assets/images/pessac.jpg'],
    ['slug' => 'talence',            'nom' => 'Talence',         'prix' => '3 100 €/m²', 'desc' => 'Résidentielle et verte, proche des campus et bien desservie par le tram.', 'biens' => 4,  'img' => '/assets/images/talence.jpg'],
];

$tableau = [
    ['slug' => 'bordeaux-chartrons', 'nom' => 'Chartrons',  'prix' => '4 600 €/m²', 'tendance' => '↗ +2%',   'atout' => 'Vie de quartier', 'famille' => '⭐⭐⭐',    'invest' => '⭐⭐⭐⭐'],
    ['slug' => 'bordeaux-centre',    'nom' => 'Centre',     'prix' => '4 800 €/m²', 'tendance' => '→ stable', 'atout' => 'Prestige',        'famille' => '⭐⭐',      'invest' => '⭐⭐⭐⭐⭐'],
    ['slug' => 'bordeaux-cauderan',  'nom' => 'Caudéran',   'prix' => '3 900 €/m²', 'tendance' => '↗ +3%',   'atout' => 'Calme & vert',   'famille' => '⭐⭐⭐⭐⭐', 'invest' => '⭐⭐⭐'],
    ['slug' => 'merignac',           'nom' => 'Mérignac',   'prix' => '3 200 €/m²', 'tendance' => '↗ +5%',   'atout' => 'Accessibilité',  'famille' => '⭐⭐⭐⭐',  'invest' => '⭐⭐⭐⭐'],
    ['slug' => 'pessac',             'nom' => 'Pessac',     'prix' => '2 900 €/m²', 'tendance' => '↗ +4%',   'atout' => 'Université',     'famille' => '⭐⭐⭐',    'invest' => '⭐⭐⭐⭐⭐'],
    ['slug' => 'talence',            'nom' => 'Talence',    'prix' => '3 100 €/m²', 'tendance' => '→ +1%',   'atout' => 'Résidentiel',    'famille' => '⭐⭐⭐⭐',  'invest' => '⭐⭐⭐'],
];
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Guide local</span></nav>
            <span class="section-label">Bordeaux &amp; métropole</span>
            <h1>Guide local des quartiers bordelais</h1>
            <p>Prix au m², ambiance, transports, commerces — mon analyse terrain de chaque secteur pour vous aider à faire le bon choix.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Estimer mon bien</a>
                <a href="/biens" class="btn btn--outline">Voir les annonces</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong><?= count($villes) ?></strong><span>quartiers analysés</span></div>
            <div class="blog-hero__metric"><strong>Terrain</strong><span>connaissance locale</span></div>
            <div class="blog-hero__metric"><strong>Gratuit</strong><span>accès illimité</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Bordeaux &amp; métropole</span>
            <h2 class="section-title">Choisissez votre quartier</h2>
            <p class="section-subtitle">Prix au m², cadre de vie, accès, services — mon analyse terrain de chaque secteur.</p>
        </div>

        <div class="villes-grid" data-animate>
            <?php foreach ($villes as $v): ?>
            <a href="/guide-local/<?= e($v['slug']) ?>" class="ville-card">
                <img src="<?= e($v['img']) ?>" alt="Immobilier <?= e($v['nom']) ?>" loading="lazy" width="800" height="533">
                <div class="ville-card__overlay">
                    <div class="ville-card__name"><?= e($v['nom']) ?></div>
                    <div class="ville-card__price"><?= e($v['prix']) ?></div>
                    <div class="ville-card__count"><?= e($v['desc']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Comparatif -->
        <div class="comparatif-section" data-animate>
            <h2>Comparatif des secteurs</h2>
            <p class="section-subtitle" style="margin-bottom:1.5rem">Données issues de l'observation terrain et des transactions récentes sur la métropole bordelaise.</p>

            <!-- Cartes mobiles -->
            <div class="comparatif-cards">
                <?php foreach ($tableau as $row): ?>
                <a href="/guide-local/<?= e($row['slug']) ?>" class="comparatif-card">
                    <div class="comparatif-card__nom"><?= e($row['nom']) ?></div>
                    <div class="comparatif-card__prix"><?= e($row['prix']) ?> <span class="comparatif-card__tendance"><?= e($row['tendance']) ?></span></div>
                    <div class="comparatif-card__row"><span>Atout</span><strong><?= e($row['atout']) ?></strong></div>
                    <div class="comparatif-card__row"><span>Famille</span><span><?= $row['famille'] ?></span></div>
                    <div class="comparatif-card__row"><span>Invest.</span><span><?= $row['invest'] ?></span></div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Tableau desktop -->
            <div class="comparatif-table-wrap">
                <table class="comparatif-table">
                    <thead>
                        <tr>
                            <th>Quartier</th>
                            <th>Prix médian</th>
                            <th>Tendance 12 m</th>
                            <th>Atout principal</th>
                            <th>Famille</th>
                            <th>Investissement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableau as $i => $row): ?>
                        <tr class="<?= $i % 2 === 0 ? 'row-even' : '' ?>">
                            <td><a href="/guide-local/<?= e($row['slug']) ?>" class="comparatif-link"><?= e($row['nom']) ?></a></td>
                            <td><?= e($row['prix']) ?></td>
                            <td><?= e($row['tendance']) ?></td>
                            <td><?= e($row['atout']) ?></td>
                            <td><?= $row['famille'] ?></td>
                            <td><?= $row['invest'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="blog-cta" data-animate>
            <div>
                <h3>Vous cherchez dans un quartier précis ?</h3>
                <p>Parlez de votre projet avec Eduardo Desul et obtenez une analyse personnalisée de votre secteur en moins de 24h.</p>
            </div>
            <a href="/contact" class="btn btn--accent">Prendre contact</a>
        </div>
    </div>
</section>
