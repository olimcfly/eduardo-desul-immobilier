<?php
$pageTitle = 'Guide local Bordeaux Métropole — ' . APP_NAME . '';
$metaDesc  = 'Découvrez les communes de Bordeaux Métropole, les secteurs majeurs bordelais et les villes proches pertinentes pour votre recherche immobilière.';
$extraCss  = ['/assets/css/guide.css'];

$communesRayon10 = [
    ['nom' => 'Mérignac', 'cp' => '33700', 'note' => 'Deuxième ville de la Métropole, proche de l\'aéroport.'],
    ['nom' => 'Pessac', 'cp' => '33600', 'note' => 'Ville universitaire dynamique avec de nombreuses maisons.'],
    ['nom' => 'Talence', 'cp' => '33400', 'note' => 'Secteur résidentiel prisé, proche des universités.'],
    ['nom' => 'Bègles', 'cp' => '33130', 'note' => 'Commune en plein essor, bord de Garonne.'],
    ['nom' => 'Villenave-d\'Ornon', 'cp' => '33140', 'note' => 'Secteur calme au sud de Bordeaux, très demandé par les familles.'],
    ['nom' => 'Bruges', 'cp' => '33520', 'note' => 'Commune résidentielle au nord-ouest, cadre verdoyant.'],
    ['nom' => 'Eysines', 'cp' => '33320', 'note' => 'Accès rapide à Bordeaux, offre immobilière variée.'],
    ['nom' => 'Le Bouscat', 'cp' => '33110', 'note' => 'Très recherché pour ses maisons et sa qualité de vie.'],
    ['nom' => 'Floirac', 'cp' => '33270', 'note' => 'Commune en mutation sur la rive droite, prix accessibles.'],
];

$secteursAix = [
    'Chartrons',
    'Caudéran',
    'Saint-Augustin',
    'Bordeaux Maritime',
];

$communesProches = [
    ['nom' => 'Mérignac (secteur Capeyron)', 'cp' => '33700', 'note' => 'Secteur très prisé aux abords de Bordeaux.'],
    ['nom' => 'Saint-Médard-en-Jalles', 'cp' => '33160', 'note' => 'Commune résidentielle cohérente pour élargir la recherche.'],
    ['nom' => 'Ambès', 'cp' => '33810', 'note' => 'Secteur pertinent pour une recherche au nord de la Métropole.'],
    ['nom' => 'Léognan', 'cp' => '33850', 'note' => 'Village viticole recherché au sud de la Métropole.'],
];
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Guide local</span></nav>
            <span class="section-label">Bordeaux &amp; alentours</span>
            <h1>Guide local des communes de Bordeaux Métropole</h1>
            <p>Retrouvez les localités à privilégier dans un rayon proche de Bordeaux, avec les secteurs bordelais majeurs et les communes voisines pertinentes pour votre projet immobilier.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Estimer mon bien</a>
                <a href="/biens" class="btn btn--outline">Voir les annonces</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong><?= count($communesRayon10) ?></strong><span>communes ~10 km</span></div>
            <div class="blog-hero__metric"><strong><?= count($secteursAix) ?></strong><span>secteurs bordelais majeurs</span></div>
            <div class="blog-hero__metric"><strong><?= count($communesProches) ?></strong><span>communes proches en plus</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Sélection locale</span>
            <h2 class="section-title">📍 Communes de Bordeaux Métropole</h2>
            <p class="section-subtitle">Une base solide pour cibler rapidement les communes les plus cohérentes autour du centre de Bordeaux.</p>
        </div>

        <div class="comparatif-cards" data-animate>
            <?php foreach ($communesRayon10 as $commune): ?>
                <article class="comparatif-card" style="cursor:default">
                    <div class="comparatif-card__nom"><?= e($commune['nom']) ?> (<?= e($commune['cp']) ?>)</div>
                    <div class="comparatif-card__row"><strong><?= e($commune['note']) ?></strong></div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="comparatif-section" data-animate>
            <h2>⭐ Secteurs phares de Bordeaux</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">
                <strong>Chartrons, Caudéran, Saint-Augustin, Bordeaux Maritime</strong> sont des quartiers incontournables de Bordeaux, très recherchés pour leur cadre de vie.
            </p>
            <div class="comparatif-cards">
                <?php foreach ($secteursAix as $secteur): ?>
                    <article class="comparatif-card" style="cursor:default">
                        <div class="comparatif-card__nom"><?= e($secteur) ?></div>
                        <div class="comparatif-card__row"><strong>Secteur majeur de Bordeaux</strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="comparatif-section" data-animate>
            <h2>➕ Communes proches à ajouter (hors Métropole mais pertinentes)</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">Ces communes dépassent légèrement la Métropole, mais restent très cohérentes pour une recherche immobilière autour de Bordeaux.</p>
            <div class="comparatif-cards">
                <?php foreach ($communesProches as $commune): ?>
                    <article class="comparatif-card" style="cursor:default">
                        <div class="comparatif-card__nom"><?= e($commune['nom']) ?> (<?= e($commune['cp']) ?>)</div>
                        <div class="comparatif-card__row"><strong><?= e($commune['note']) ?></strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="blog-cta" data-animate>
            <div>
                <h3>Vous cherchez dans une commune précise ?</h3>
                <p>Parlez de votre projet avec <?= ADVISOR_NAME ?> et obtenez une orientation personnalisée selon votre budget, votre style de vie et vos délais.</p>
            </div>
            <a href="/contact" class="btn btn--accent">Prendre contact</a>
        </div>
    </div>
</section>
