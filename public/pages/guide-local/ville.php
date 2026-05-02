<?php
/* ============================================================
   PAGE : Guide local — détail secteur
   /guide-local/[slug]
   ============================================================ */

$slug = $slug ?? 'bordeaux-centre';

$secteurs = [
    'bordeaux-centre' => [
        'nom'        => 'Bordeaux Centre',
        'prix'       => '5 200',
        'tendance'   => '+3%',
        'delai'      => '38 jours',
        'biens'      => 14,
        'img'        => '/assets/images/bordeaux-centre.jpg',
        'img_credit' => '',
        'desc'       => 'Le cœur historique de Bordeaux concentre patrimoine, commerces, transports et forte demande pour les appartements de caractère.',
        'marche'     => 'Le centre de Bordeaux reste un marché recherché, avec une demande soutenue pour les biens bien situés, lumineux et correctement valorisés.',
        'transports' => 'Tramways, bus, gare Saint-Jean et mobilités douces rendent le centre facilement accessible sans voiture.',
        'commerces'  => 'Commerces de proximité, marchés, restaurants, écoles et services structurent un cadre de vie urbain très complet.',
        'habitat_pros' => [],
    ],
    'chartrons' => [
        'nom'        => 'Chartrons',
        'prix'       => '4 800',
        'tendance'   => '+3%',
        'delai'      => '32 jours',
        'biens'      => 9,
        'img'        => '/assets/images/chartrons.jpg',
        'img_credit' => '',
        'desc'       => 'Quartier historique et vivant, les Chartrons attirent acheteurs, familles et investisseurs par leur ambiance de village en ville.',
        'marche'     => 'Les appartements anciens rénovés, les biens avec cachet et les adresses proches des quais sont particulièrement recherchés.',
        'transports' => 'Le tramway, les lignes de bus et les pistes cyclables assurent une connexion directe avec le centre et les bassins à flot.',
        'commerces'  => 'Brocanteurs, restaurants, commerces de bouche et services de quartier créent une forte attractivité résidentielle.',
        'habitat_pros' => [],
    ],
    'cauderan' => [
        'nom'        => 'Caudéran',
        'prix'       => '3 500',
        'tendance'   => '+2%',
        'delai'      => '37 jours',
        'biens'      => 8,
        'img'        => '/assets/images/cauderan.jpg',
        'img_credit' => '',
        'desc'       => 'Caudéran offre un cadre résidentiel verdoyant, recherché par les familles pour ses maisons, ses écoles et sa proximité avec le centre.',
        'marche'     => 'Le marché est porté par les maisons avec jardin et les appartements familiaux bien desservis.',
        'transports' => 'Le tram D, les bus et les axes vers les boulevards facilitent les déplacements quotidiens.',
        'commerces'  => 'Commerces de quartier, établissements scolaires et équipements sportifs renforcent l’attractivité du secteur.',
        'habitat_pros' => [],
    ],
    'merignac' => [
        'nom'        => 'Mérignac',
        'prix'       => '3 700',
        'tendance'   => '+2%',
        'delai'      => '40 jours',
        'biens'      => 10,
        'img'        => '/assets/images/merignac.jpg',
        'img_credit' => '',
        'desc'       => 'Mérignac combine bassins d’emploi, quartiers résidentiels et accès rapide à Bordeaux.',
        'marche'     => 'La demande reste solide pour les maisons familiales et les appartements bien reliés au tram.',
        'transports' => 'Tram A, bus, rocade et aéroport assurent une excellente accessibilité.',
        'commerces'  => 'Centres commerciaux, commerces de proximité et équipements publics structurent une ville très complète.',
        'habitat_pros' => [],
    ],
    'pessac' => [
        'nom'        => 'Pessac',
        'prix'       => '3 600',
        'tendance'   => '+2%',
        'delai'      => '42 jours',
        'biens'      => 8,
        'img'        => '/assets/images/pessac.jpg',
        'img_credit' => '',
        'desc'       => 'Pessac séduit par son équilibre entre vie résidentielle, campus, vignes urbaines et proximité de Bordeaux.',
        'marche'     => 'Les maisons, appartements récents et biens proches du tram restent les plus recherchés.',
        'transports' => 'Tram B, TER, bus et rocade offrent une desserte complète vers Bordeaux et le bassin d’emploi.',
        'commerces'  => 'Commerces, écoles, équipements universitaires et espaces verts soutiennent la qualité de vie.',
        'habitat_pros' => [],
    ],
    'talence' => [
        'nom'        => 'Talence',
        'prix'       => '3 900',
        'tendance'   => '+3%',
        'delai'      => '39 jours',
        'biens'      => 7,
        'img'        => '/assets/images/talence.jpg',
        'img_credit' => '',
        'desc'       => 'Talence est une ville universitaire et résidentielle très connectée au centre de Bordeaux.',
        'marche'     => 'Le marché est actif sur les petites surfaces, les logements familiaux et les biens proches du tram.',
        'transports' => 'Le tram B, les bus et les pistes cyclables facilitent les déplacements vers Bordeaux et le campus.',
        'commerces'  => 'Commerces, équipements universitaires, services et espaces verts composent un cadre recherché.',
        'habitat_pros' => [],
    ],
];

/* ── Validation slug ──────────────────────────────────────── */
$s = $secteurs[$slug] ?? null;

if (!$s) {
    http_response_code(404);
    echo '<div style="padding:4rem 2rem;text-align:center">
            <h1>Secteur introuvable</h1>
            <p><a href="/guide-local">Voir tous les secteurs</a></p>
          </div>';
    return;
}

/* ── Meta dynamiques ──────────────────────────────────────── */
$pageTitle = 'Immobilier ' . $s['nom'] . ' — Prix, marché & conseils | ' . ADVISOR_NAME;
$metaDesc  = 'Prix au m², tendances et analyse du marché immobilier à ' . $s['nom']
           . '. Conseils terrain d’Eduardo Desul dans la métropole bordelaise.';

$autresSecteurs = array_filter($secteurs, fn($k) => $k !== $slug, ARRAY_FILTER_USE_KEY);
?>

<div class="container guide-detail">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Fil d'Ariane" style="margin-top:1.5rem">
        <a href="/">Accueil</a>
        <a href="/guide-local">Guide local</a>
        <span><?= e($s['nom']) ?></span>
    </nav>

    <!-- En-tête -->
    <header class="guide-header">
        <h1><?= e($s['nom']) ?></h1>
        <p class="guide-header__desc"><?= e($s['desc']) ?></p>

        <div class="guide-metrics">
            <div class="guide-metric">
                <span class="guide-metric__val"><?= e($s['prix']) ?> €/m²</span>
                <span class="guide-metric__lbl">Prix médian</span>
            </div>
            <div class="guide-metric">
                <span class="guide-metric__val"><?= e($s['tendance']) ?></span>
                <span class="guide-metric__lbl">Tendance 12 mois</span>
            </div>
            <div class="guide-metric">
                <span class="guide-metric__val"><?= e($s['delai']) ?></span>
                <span class="guide-metric__lbl">Délai moyen de vente</span>
            </div>
            <div class="guide-metric">
                <span class="guide-metric__val"><?= $s['biens'] ?></span>
                <span class="guide-metric__lbl">Biens disponibles</span>
            </div>
        </div>
    </header>

    <!-- Image principale -->
    <figure class="guide-figure">
        <img src="<?= e($s['img']) ?>"
             alt="Vue de <?= e($s['nom']) ?>"
             width="1200" height="600"
             loading="eager">
        <?php if (!empty($s['img_credit'])): ?>
            <figcaption><?= $s['img_credit'] ?></figcaption>
        <?php endif; ?>
    </figure>

    <!-- Contenu + Sidebar -->
    <div class="article-layout">

        <article class="guide-article">

            <section>
                <h2>Présentation du secteur</h2>
                <p><?= e($s['desc']) ?></p>
                <p><?= e($s['marche']) ?></p>
            </section>

            <section>
                <h2>Transports &amp; accessibilité</h2>
                <p><?= e($s['transports']) ?></p>
            </section>

            <section>
                <h2>Commerces &amp; services</h2>
                <p><?= e($s['commerces']) ?></p>
            </section>

            <?php if (!empty($s['habitat_pros'])): ?>
            <section>
                <h2>Professionnels de l'habitat à proximité</h2>
                <div class="habitat-pros">
                    <?php foreach ($s['habitat_pros'] as $pro): ?>
                    <div class="habitat-pro">
                        <div class="habitat-pro__header">
                            <strong><?= e($pro['nom']) ?></strong>
                            <span class="habitat-pro__cat"><?= e($pro['categorie']) ?></span>
                        </div>
                        <div class="habitat-pro__zone">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <?= e($pro['zone']) ?>
                        </div>
                        <p class="habitat-pro__note"><?= e($pro['note']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- CTA intermédiaire -->
            <div class="guide-cta-inline">
                <p>Vous avez un projet à <?= e($s['nom']) ?> ?</p>
                <a href="/estimation-gratuite" class="btn btn--accent">
                    Estimer mon bien gratuitement
                </a>
                <a href="/financement" class="btn btn--outline">
                    Étudier mon financement
                </a>
            </div>

        </article>

        <!-- Sidebar -->
        <aside class="blog-sidebar">

            <div class="sidebar-card">
                <h3>Estimer votre bien à <?= e($s['nom']) ?></h3>
                <p>Obtenez une estimation gratuite et personnalisée par <?= ADVISOR_NAME ?>, expert de ce secteur.</p>
                <a href="/estimation-gratuite" class="btn btn--accent btn--full">
                    Estimation gratuite
                </a>
            </div>

            <div class="sidebar-card">
                <h3>Financement</h3>
                <p>Votre projet bloqué par le financement ? Anticipez avant de chercher.</p>
                <a href="/financement" class="btn btn--outline btn--full">
                    Étudier mon financement
                </a>
            </div>

            <div class="sidebar-card">
                <h3>Autres secteurs</h3>
                <ul class="sidebar-links">
                    <?php foreach ($autresSecteurs as $k => $autre): ?>
                    <li>
                        <a href="/guide-local/<?= e($k) ?>">
                            <span><?= e($autre['nom']) ?></span>
                            <span class="sidebar-price"><?= e($autre['prix']) ?> €/m²</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-card sidebar-card--contact">
                <h3>Une question ?</h3>
                <p><?= ADVISOR_NAME ?> répond sous 24h.</p>
                <a href="/contact" class="btn btn--outline btn--full">
                    Contacter <?= ADVISOR_NAME ?>
                </a>
            </div>

        </aside>

    </div><!-- /.article-layout -->
</div><!-- /.container -->
