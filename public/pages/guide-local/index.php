<?php
$pageTitle = 'Guide local Aix-en-Provence — Pascal Hamm Immobilier';
$metaDesc  = 'Découvrez les communes autour d’Aix-en-Provence, les secteurs majeurs aixois et les villes proches pertinentes pour votre recherche immobilière.';
$extraCss  = ['/assets/css/guide.css'];

$communesRayon10 = [
    ['nom' => 'Le Tholonet', 'cp' => '13100', 'note' => 'Commune résidentielle aux portes de la Sainte-Victoire.'],
    ['nom' => 'Beaurecueil', 'cp' => '13100', 'note' => 'Cadre naturel recherché, faible densité urbaine.'],
    ['nom' => 'Saint-Marc-Jaumegarde', 'cp' => '13100', 'note' => 'Village prisé pour son calme et sa proximité immédiate d’Aix.'],
    ['nom' => 'Meyreuil', 'cp' => '13590', 'note' => 'Accès rapide à Aix et aux axes vers Marseille.'],
    ['nom' => 'Éguilles', 'cp' => '13510', 'note' => 'Village provençal très demandé par les familles.'],
    ['nom' => 'Venelles', 'cp' => '13770', 'note' => 'Secteur dynamique au nord d’Aix, apprécié pour sa qualité de vie.'],
    ['nom' => 'Gardanne', 'cp' => '13120', 'note' => 'Environ 9,5 km du centre d’Aix-en-Provence.'],
    ['nom' => 'Bouc-Bel-Air', 'cp' => '13320', 'note' => 'En limite du rayon d’environ 10 km.'],
    ['nom' => 'Simiane-Collongue', 'cp' => '13109', 'note' => 'Certaines zones se situent autour des 10 km d’Aix.'],
];

$secteursAix = [
    'Puyricard',
    'Les Milles',
    'Luynes',
    'Jas-de-Bouffan',
];

$communesProches = [
    ['nom' => 'Le Puy-Sainte-Réparade', 'cp' => '13610', 'note' => 'Ville voisine agréable, réputée pour ses domaines viticoles et son cadre recherché.'],
    ['nom' => 'Saint-Cannat', 'cp' => '13760', 'note' => 'Commune résidentielle cohérente dans une recherche autour d’Aix.'],
    ['nom' => 'Rognes', 'cp' => '13840', 'note' => 'Village provençal recherché, légèrement hors rayon strict.'],
    ['nom' => 'Lambesc', 'cp' => '13410', 'note' => 'Secteur pertinent pour élargir la recherche au nord-ouest d’Aix.'],
];
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Guide local</span></nav>
            <span class="section-label">Aix-en-Provence &amp; alentours</span>
            <h1>Guide local des communes autour d’Aix-en-Provence</h1>
            <p>Retrouvez les localités à privilégier dans un rayon proche d’Aix, avec les secteurs aixois majeurs et les communes voisines pertinentes pour votre projet immobilier.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Estimer mon bien</a>
                <a href="/biens" class="btn btn--outline">Voir les annonces</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong><?= count($communesRayon10) ?></strong><span>communes ~10 km</span></div>
            <div class="blog-hero__metric"><strong><?= count($secteursAix) ?></strong><span>secteurs aixois majeurs</span></div>
            <div class="blog-hero__metric"><strong><?= count($communesProches) ?></strong><span>communes proches en plus</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Sélection locale</span>
            <h2 class="section-title">📍 Communes dans un rayon d’environ 10 km autour d’Aix-en-Provence</h2>
            <p class="section-subtitle">Une base solide pour cibler rapidement les communes les plus cohérentes autour du centre d’Aix.</p>
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
            <h2>⭐ Particularités importantes</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">
                <strong>Puyricard, Les Milles, Luynes, Jas-de-Bouffan</strong> sont des quartiers d’Aix-en-Provence (et non des communes indépendantes), mais ils restent incontournables car très recherchés.
            </p>
            <div class="comparatif-cards">
                <?php foreach ($secteursAix as $secteur): ?>
                    <article class="comparatif-card" style="cursor:default">
                        <div class="comparatif-card__nom"><?= e($secteur) ?></div>
                        <div class="comparatif-card__row"><strong>Secteur majeur d’Aix-en-Provence</strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="comparatif-section" data-animate>
            <h2>➕ Communes proches à ajouter (hors rayon strict mais pertinentes)</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">Ces communes dépassent légèrement les ~10 km, mais restent très cohérentes pour une recherche immobilière autour d’Aix-en-Provence.</p>
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
                <p>Parlez de votre projet avec Pascal Hamm et obtenez une orientation personnalisée selon votre budget, votre style de vie et vos délais.</p>
            </div>
            <a href="/contact" class="btn btn--accent">Prendre contact</a>
        </div>
    </div>
</section>
