<?php
/* ============================================================
   PAGE : Guide local — détail secteur
   /guide-local/[slug]
   ============================================================ */

$slug = $slug ?? 'aix-en-provence-centre';

$secteurs = [

    /* ── CENTRE-VILLE ──────────────────────────────────────── */
    'aix-en-provence-centre' => [
        'nom'        => 'Aix-en-Provence Centre',
        'prix'       => '5 200',
        'tendance'   => '↗ +3%',
        'delai'      => '38 jours',
        'biens'      => 14,
        'img'        => '/assets/images/aix-centre.jpg',
        'img_credit' => 'Cours Mirabeau — © <a href="https://commons.wikimedia.org/wiki/File:Cours_Mirabeau_Aix-en-Provence.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 4.0)',
        'desc'       => 'Le cœur historique d\'Aix-en-Provence réunit le Cours Mirabeau, la vieille ville et ses hôtels particuliers du XVIIe siècle. Un secteur de prestige, plébiscité par les acquéreurs nationaux et internationaux.',
        'marche'     => 'Les prix atteignent 5 200 €/m² en moyenne, avec des pointes à 7 000 €/m² pour les hôtels particuliers avec jardin privatif. La demande est soutenue malgré la tension des taux, notamment pour les appartements de caractère avec parquet et moulures. Les biens correctement estimés partent en moins de 40 jours.',
        'transports' => 'Toutes les lignes de bus Aix en Bus convergent vers le centre. La gare routière La Rotonde dessert Marseille, l\'aéroport Marseille-Provence et Avignon. La gare TGV Aix-en-Provence est à 10 minutes en navette. L\'accès en voiture est limité en hypercentre (zone piétonne).',
        'commerces'  => 'Le marché provençal, trois fois par semaine place Richelme et place des Prêcheurs, est l\'un des plus réputés de la région. La rue d\'Italie et le Cours Mirabeau concentrent les commerces haut de gamme, librairies et cafés historiques. Les halles couvertes proposent produits frais et artisanat local.',
        'habitat_pros' => [
            ['nom' => 'Saint-Maclou Aix',      'categorie' => 'Revêtements de sol',    'zone' => 'Zone Les Milles',  'note' => 'Parquet, carrelage, moquette sur mesure.'],
            ['nom' => 'Cuisinella Aix-Centre',  'categorie' => 'Cuisines équipées',     'zone' => 'Av. des Belges',   'note' => 'Conception et pose cuisines haut de gamme.'],
        ],
    ],

    /* ── MAZARIN ────────────────────────────────────────────── */
    'aix-mazarin' => [
        'nom'        => 'Quartier Mazarin',
        'prix'       => '5 800',
        'tendance'   => '→ stable',
        'delai'      => '45 jours',
        'biens'      => 7,
        'img'        => '/assets/images/aix-mazarin.jpg',
        'img_credit' => 'Fontaine des Quatre-Dauphins — © <a href="https://commons.wikimedia.org/wiki/File:Fontaine_des_Quatre-Dauphins_Aix-en-Provence.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 3.0)',
        'desc'       => 'Construit au XVIIe siècle selon un plan en damier, le quartier Mazarin est le secteur le plus prisé d\'Aix. Ses hôtels particuliers, ses musées et son architecture classique en font un lieu de vie d\'exception.',
        'marche'     => 'Mazarin affiche les prix les plus élevés de l\'agglomération, souvent au-delà de 6 000 €/m² pour les biens d\'exception. Le marché est peu liquide mais très qualitatif. Les acheteurs sont souvent des profils patrimoniaux, cadres supérieurs ou retraités aisés. Les négociations restent limitées sur les biens rares.',
        'transports' => 'Le quartier est desservi par plusieurs lignes de bus Aix en Bus et est entièrement accessible à pied depuis le Cours Mirabeau. La proximité du centre permet de se passer de voiture au quotidien. La gare TGV est à 15 minutes en navette.',
        'commerces'  => 'Restaurants gastronomiques, galeries d\'art et commerces de luxe jalonnent le quartier. Le marché du dimanche matin est à deux pas. Toute l\'offre du centre-ville est accessible à pied.',
        'habitat_pros' => [],
    ],

    /* ── LES MILLES ─────────────────────────────────────────── */
    'aix-les-milles' => [
        'nom'        => 'Les Milles',
        'prix'       => '3 600',
        'tendance'   => '↗ +4%',
        'delai'      => '52 jours',
        'biens'      => 9,
        'img'        => '/assets/images/aix-les-milles.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:Les_Milles_-_panoramio.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY 3.0)',
        'desc'       => 'Zone résidentielle et économique dynamique à l\'ouest d\'Aix, Les Milles accueille de nombreuses entreprises high-tech et des quartiers pavillonnaires récents. Idéal pour les actifs cherchant espace et accessibilité.',
        'marche'     => 'Les Milles offre un bon rapport qualité-prix avec des maisons de 100 à 150 m² accessibles autour de 400 000 à 550 000 €. La demande est portée par les salariés des entreprises de la zone d\'activité et les familles fuyant les prix du centre. Les villas avec piscine partent rapidement en été.',
        'transports' => 'Plusieurs lignes de bus relient Les Milles au centre d\'Aix en 20 minutes. L\'accès autoroutier A51 est immédiat. L\'aéroport Marseille-Provence est à 20 minutes. Le secteur est essentiellement voiturier.',
        'commerces'  => 'La zone commerciale des Milles concentre grandes surfaces, restaurants et services. Les commerces de proximité sont présents dans les lotissements récents. Le centre d\'Aix reste accessible facilement pour les marchés et commerces spécialisés.',
        'habitat_pros' => [
            ['nom' => 'Leroy Merlin Les Milles', 'categorie' => 'Bricolage & rénovation',    'zone' => 'Zone commerciale', 'note' => 'Grand choix matériaux, carrelage, peinture.'],
            ['nom' => 'Point P Les Milles',      'categorie' => 'Matériaux construction',    'zone' => 'Rte de Berre',     'note' => 'Fournitures pros et particuliers.'],
            ['nom' => 'Brico Dépôt Aix',         'categorie' => 'Bricolage prix discount',   'zone' => 'Les Milles',       'note' => 'Outillage et matériaux à prix cassés.'],
        ],
    ],

    /* ── EGUILLES ────────────────────────────────────────────── */
    'eguilles' => [
        'nom'        => 'Éguilles',
        'prix'       => '4 100',
        'tendance'   => '↗ +5%',
        'delai'      => '48 jours',
        'biens'      => 5,
        'img'        => '/assets/images/eguilles.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:%C3%89guilles_-_panoramio.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY 3.0)',
        'desc'       => 'Village perché dominant la plaine de la Durance, Éguilles séduit par son cadre provençal authentique, ses bastides et ses panoramas sur la Sainte-Victoire. Un des villages les plus recherchés du Pays d\'Aix.',
        'marche'     => 'Le marché à Éguilles est très tendu sur les maisons avec terrain et piscine. La rareté du foncier constructible tire les prix vers le haut. Les bastides rénovées avec jardin dépassent souvent 800 000 €. Les acquéreurs sont majoritairement des familles et des profils retraités aisés cherchant le calme à proximité d\'Aix.',
        'transports' => 'Éguilles est accessible en bus depuis Aix (ligne 200). La voiture reste indispensable. L\'accès à l\'A8 se fait en 10 minutes. La gare TGV Aix est à 20 minutes.',
        'commerces'  => 'Le village dispose de commerces de première nécessité, boulangerie, épicerie et pharmacie. Les marchés provençaux animent la place du village en saison. Aix-en-Provence est à 15 minutes pour toute l\'offre commerciale.',
        'habitat_pros' => [],
    ],

    /* ── VENELLES ────────────────────────────────────────────── */
    'venelles' => [
        'nom'        => 'Venelles',
        'prix'       => '3 900',
        'tendance'   => '↗ +4%',
        'delai'      => '50 jours',
        'biens'      => 6,
        'img'        => '/assets/images/venelles.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:Venelles_-_panoramio.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY 3.0)',
        'desc'       => 'Commune résidentielle au nord d\'Aix, Venelles conjugue cadre verdoyant, écoles réputées et accès rapide à la métropole. Très prisée des familles pour la qualité de vie qu\'elle offre.',
        'marche'     => 'Venelles enregistre une progression régulière des prix, tirée par la demande familiale et la faible vacance locative. Les maisons de 120 à 180 m² avec jardin constituent le cœur du marché. Les délais de vente restent raisonnables pour des biens bien présentés.',
        'transports' => 'Plusieurs lignes de bus relient Venelles à Aix-en-Provence. La voiture est recommandée pour les déplacements professionnels. L\'A51 est accessible rapidement.',
        'commerces'  => 'Le village dispose d\'une offre commerciale de proximité complète. Les grandes surfaces et services spécialisés sont à Aix à 15 minutes.',
        'habitat_pros' => [],
    ],

    /* ── PERTUIS ─────────────────────────────────────────────── */
    'pertuis' => [
        'nom'        => 'Pertuis',
        'prix'       => '2 800',
        'tendance'   => '↗ +6%',
        'delai'      => '58 jours',
        'biens'      => 8,
        'img'        => '/assets/images/pertuis.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:Pertuis_-_panoramio.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY 3.0)',
        'desc'       => 'Ville dynamique aux portes du Luberon, Pertuis attire de plus en plus d\'acquéreurs cherchant un cadre de vie provençal à prix maîtrisé, avec toutes les commodités d\'une ville de 20 000 habitants.',
        'marche'     => 'Pertuis enregistre la plus forte progression du Pays d\'Aix, portée par l\'exode des ménages aixois vers des prix plus accessibles. Les maisons avec jardin représentent l\'essentiel des transactions. Le marché locatif est dynamique, porté par les salariés travaillant sur Aix ou Marseille.',
        'transports' => 'Pertuis est desservi par des lignes de cars vers Aix et Marseille. L\'A51 permet de rejoindre Aix en 25 minutes. La gare TGV Aix est à 30 minutes. La voiture est indispensable.',
        'commerces'  => 'Pertuis dispose d\'un centre-ville commerçant complet, d\'une zone commerciale et d\'un marché hebdomadaire réputé. Toutes les enseignes nationales sont présentes.',
        'habitat_pros' => [
            ['nom' => 'Gamm Vert Pertuis',  'categorie' => 'Jardinerie & outdoor', 'zone' => 'RN 96',       'note' => 'Plantes, mobilier de jardin, matériaux naturels.'],
            ['nom' => 'Mr Bricolage Pertuis','categorie' => 'Bricolage',            'zone' => 'Zone com.',   'note' => 'Matériaux, peinture, outillage.'],
        ],
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
           . '. Conseils terrain de <?= ADVISOR_NAME ?>, expert immobilier 360° dans le Pays d\'Aix.';

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
