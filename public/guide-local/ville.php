<?php
$slug      = $slug ?? 'merignac';
$pageTitle = 'Guide du quartier — Eduardo Desul Immobilier';
$metaDesc  = '';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];

$secteurs = [
    'merignac' => [
        'nom'      => 'Mérignac',
        'prix'     => '3 200',
        'tendance' => '↗ +5%',
        'delai'    => '49 jours',
        'biens'    => 6,
        'img'      => '/assets/images/merignac.jpg',
        'img_credit' => 'Parc du Bourran — © <a href="https://commons.wikimedia.org/wiki/File:Cascade_de_Bourran,_M%C3%A9rignac,_France.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 4.0)',
        'desc'     => 'Mérignac combine quartiers résidentiels, zones d\'activité et accès rapide à Bordeaux centre. Deuxième ville de la Gironde, elle attire les familles et les actifs cherchant espace et accessibilité.',
        'marche'   => 'Le marché à Mérignac reste dynamique, porté par la demande de maisons familiales avec jardin. Les prix progressent de 4 à 5 % par an, tirés par la rénovation urbaine du secteur Mérignac Soleil et la bonne desserte en transports. Les biens de 100 à 150 m² partent rapidement, souvent en moins de 45 jours.',
        'transports'=> 'La ligne A du tramway dessert Mérignac jusqu\'au Meriadeck. Les lignes TBM 10, 11 et 35 complètent la couverture. La rocade A630 offre un accès direct au périphérique. L\'aéroport Bordeaux-Mérignac est à 5 minutes en voiture.',
        'commerces' => 'Le secteur dispose de nombreux commerces de proximité : supermarchés, boulangeries, pharmacies. Le centre commercial Mérignac Soleil regroupe plus de 80 enseignes. Le marché de plein air a lieu chaque mercredi et samedi matin.',
        'habitat_pros' => [
            ['nom' => 'Leroy Merlin Mérignac',   'categorie' => 'Bricolage & rénovation', 'zone' => 'Mérignac Soleil', 'note' => 'Large choix matériaux, outillage, aménagement intérieur.'],
            ['nom' => 'Saint-Maclou Mérignac',   'categorie' => 'Revêtements de sol',     'zone' => 'Zone commerciale', 'note' => 'Parquet, carrelage, moquette sur mesure.'],
            ['nom' => 'Cuisinella Mérignac',      'categorie' => 'Cuisines',               'zone' => 'Centre-ville',     'note' => 'Conception et installation cuisines équipées.'],
            ['nom' => 'Point P Mérignac',         'categorie' => 'Matériaux de construction','zone' => 'Av. Pasteur',   'note' => 'Fournitures professionnelles et particuliers.'],
        ],
    ],
    'bordeaux-centre' => [
        'nom'      => 'Bordeaux Centre',
        'prix'     => '4 800',
        'tendance' => '→ stable',
        'delai'    => '38 jours',
        'biens'    => 12,
        'img'      => '/assets/images/bordeaux-centre.jpg',
        'img_credit' => 'Place de la Bourse — © <a href="https://commons.wikimedia.org/wiki/File:139_-_Place_de_la_Bourse_et_le_miroir_d%27eau_-_Bordeaux.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 3.0)',
        'desc'     => 'Le cœur historique de Bordeaux, inscrit au Patrimoine Mondial de l\'UNESCO depuis 2007. Entre la Garonne et les grandes artères commerçantes, il conjugue prestige architectural et intense vie urbaine.',
        'marche'   => 'Le centre de Bordeaux affiche les prix les plus élevés de la métropole, stabilisés autour de 4 800 €/m² après les hausses des années 2018-2022. La demande reste soutenue pour les appartements Haussmannien avec balcon. Les investisseurs ciblent les studios et T2 pour la location meublée, portée par le tourisme et les étudiants des grandes écoles.',
        'transports'=> 'Le tramway A, B et C converge dans le centre. La gare Saint-Jean est à 15 minutes à pied. Bordeaux est à 2h05 de Paris en TGV. Le réseau de pistes cyclables est très développé, avec le vélo en libre-service V3 disponible partout.',
        'commerces' => 'La rue Sainte-Catherine, l\'une des plus longues rues piétonnes de France, concentre les grandes enseignes. Le marché des Capucins, surnommé « le ventre de Bordeaux », propose produits frais et spécialités locales tous les matins. Les restaurants gastronomiques et caves à vin sont nombreux dans le Triangle d\'Or.',
        'habitat_pros' => [
            ['nom' => 'Castorama Bordeaux Lac',  'categorie' => 'Bricolage & décoration', 'zone' => 'Bordeaux Lac', 'note' => 'Grand magasin accessible en tramway ligne C.'],
            ['nom' => 'Schmidt Bordeaux Centre', 'categorie' => 'Cuisines & dressing',    'zone' => 'Cours Portal',  'note' => 'Conception sur mesure, showroom en centre-ville.'],
        ],
    ],
    'bordeaux-cauderan' => [
        'nom'      => 'Caudéran',
        'prix'     => '3 900',
        'tendance' => '↗ +3%',
        'delai'    => '44 jours',
        'biens'    => 6,
        'img'      => '/assets/images/cauderan.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:Caud%C3%A9ran_Montage.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 3.0)',
        'desc'     => 'Quartier résidentiel calme et verdoyant, Caudéran est l\'un des secteurs les plus recherchés par les familles bordelaises. Ses maisons avec jardin et ses écoles réputées en font un choix de référence.',
        'marche'   => 'Caudéran connaît une progression régulière des prix (+3 % sur 12 mois), soutenue par la pénurie de maisons individuelles à l\'intérieur du périphérique. Les T4 et T5 avec jardin partent souvent au-dessus du prix demandé. Le marché est peu tendu en appartements, ce qui crée des opportunités pour les investisseurs.',
        'transports'=> 'La ligne D du tramway dessert Caudéran via la Barrière du Médoc. Les lignes de bus 8, 30 et 32 complètent la couverture vers le centre. Le quartier est bien adapté aux déplacements à vélo grâce au réseau de pistes cyclables.',
        'commerces' => 'Le quartier possède ses propres commerces de proximité, notamment autour de la place Caudéran et de la rue du Palais Gallien. Plusieurs marchés de quartier ont lieu en semaine. Les grandes surfaces se trouvent à 10 minutes en voiture.',
        'habitat_pros' => [
            ['nom' => 'Mr. Bricolage Caudéran', 'categorie' => 'Bricolage',              'zone' => 'Route du Médoc', 'note' => 'Bricolage, peinture, jardinage.'],
            ['nom' => 'Atelier Bois & Maison',  'categorie' => 'Menuiserie sur mesure',  'zone' => 'Caudéran',       'note' => 'Menuisier local spécialisé portes et fenêtres.'],
        ],
    ],
    'bordeaux-chartrons' => [
        'nom'      => 'Les Chartrons',
        'prix'     => '4 600',
        'tendance' => '↗ +2%',
        'delai'    => '35 jours',
        'biens'    => 8,
        'img'      => '/assets/images/chartrons.jpg',
        'img_credit' => '© <a href="https://commons.wikimedia.org/wiki/File:Chartrons,_Bordeaux_(26015264853).jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC0 1.0 — domaine public)',
        'desc'     => 'Ancien quartier des négociants en vin, les Chartrons ont muté en l\'un des secteurs les plus tendance de Bordeaux. Brocanteurs, galeries, restaurants et bars branchés côtoient de belles maisons de maître.',
        'marche'   => 'Les Chartrons restent l\'un des secteurs les plus actifs de Bordeaux. La demande est forte pour les grands appartements et les maisons de ville. Les prix se maintiennent à un niveau élevé, soutenus par l\'attractivité du secteur auprès des cadres et des expatriés. Les petites surfaces à rénover représentent de bonnes opportunités d\'investissement.',
        'transports'=> 'Le tramway B longe les quais des Chartrons. La ligne C dessert la rive droite via le Pont de Pierre. Le quartier est très cyclable, avec la promenade des quais aménagée. Le centre est accessible à pied en 15 minutes.',
        'commerces' => 'Le marché des Chartrons, tous les dimanches matin, est l\'un des plus animés de Bordeaux. Le quai des Chartrons regroupe antiquaires, brocanteurs et galeries d\'art. La rue Notre-Dame concentre restaurants et commerces de bouche de qualité.',
        'habitat_pros' => [],
    ],
    'pessac' => [
        'nom'      => 'Pessac',
        'prix'     => '2 900',
        'tendance' => '↗ +4%',
        'delai'    => '52 jours',
        'biens'    => 5,
        'img'      => '/assets/images/pessac.jpg',
        'img_credit' => 'Parc de Cap de Bos — © <a href="https://commons.wikimedia.org/wiki/File:Lac_du_parc_de_Cap_de_Bos_%C3%A0_Pessac.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 4.0)',
        'desc'     => 'Ville universitaire de 63 000 habitants, Pessac accueille le campus de Bordeaux et offre une grande diversité de biens. Espaces verts généreux, prix accessibles et vraie vie de quartier en font un secteur attractif.',
        'marche'   => 'Pessac présente l\'un des meilleurs rapports qualité-prix de la métropole. Le marché est particulièrement actif sur les maisons de 80 à 120 m² avec jardin. La proximité des universités génère une forte demande locative étudiante pour les studios et T2. Les prix progressent de 4 % par an, avec un potentiel de plus-value important.',
        'transports'=> 'La ligne B du tramway relie Pessac au cœur de Bordeaux en 25 minutes. La gare de Pessac-Centre permet de rejoindre la gare Saint-Jean en 10 minutes. Plusieurs lignes de bus TBM desservent les différents quartiers. L\'autoroute A63 est accessible en 5 minutes.',
        'commerces' => 'Le centre-ville de Pessac dispose d\'un centre commercial et de commerces de proximité. Le marché alimentaire se tient chaque mardi et samedi matin. Plusieurs supermarchés sont répartis dans les différents quartiers. La zone commerciale de Mérignac est accessible en 10 minutes.',
        'habitat_pros' => [
            ['nom' => 'Brico Dépôt Pessac',     'categorie' => 'Bricolage',          'zone' => 'Av. de Canéjan',   'note' => 'Prix discount sur les matériaux et outillage.'],
            ['nom' => 'Tollens Pessac',          'categorie' => 'Peinture & revêt.', 'zone' => 'Zone industrielle', 'note' => 'Peintures professionnelles toutes surfaces.'],
        ],
    ],
    'talence' => [
        'nom'      => 'Talence',
        'prix'     => '3 100',
        'tendance' => '→ +1%',
        'delai'    => '55 jours',
        'biens'    => 4,
        'img'      => '/assets/images/talence.jpg',
        'img_credit' => 'Stade de Talence — © <a href="https://commons.wikimedia.org/wiki/File:Stade_de_Talence_athl%C3%A9tisme.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 4.0)',
        'desc'     => 'Commune résidentielle et verte au sud de Bordeaux, Talence abrite une partie du campus universitaire et de nombreuses maisons familiales dans un environnement calme et arboré.',
        'marche'   => 'Le marché de Talence est stable, avec une légère progression des prix portée par la demande de maisons familiales. Les appartements proches du campus trouvent rapidement preneurs pour la location étudiante. Les délais de vente sont un peu plus longs qu\'en centre-ville, ce qui laisse de la marge pour la négociation.',
        'transports'=> 'La ligne B du tramway traverse Talence du nord au sud. La commune est très bien reliée à Bordeaux et Pessac. Les pistes cyclables permettent d\'atteindre le campus à vélo. L\'accès en voiture est facile via la rocade.',
        'commerces' => 'Talence dispose de commerces de quartier et d\'un marché hebdomadaire. Le campus universitaire génère une offre commerciale variée (restaurants, cafés, librairies). Les grandes surfaces de Pessac et Mérignac sont accessibles en quelques minutes.',
        'habitat_pros' => [],
    ],
];

$s = $secteurs[$slug] ?? null;

if (!$s) {
    http_response_code(404);
    $pageTitle = '404 — Quartier introuvable';
    echo '<div style="padding:4rem 2rem;text-align:center"><h1>Quartier introuvable</h1><p><a href="/guide-local">Voir tous les quartiers</a></p></div>';
    return;
}

$pageTitle = 'Immobilier ' . $s['nom'] . ' — Prix, marché & conseils | Eduardo Desul';
$metaDesc  = 'Prix au m², tendances et analyse du marché immobilier à ' . $s['nom'] . '. Conseils terrain d\'Eduardo Desul, conseiller immobilier à Bordeaux.';

$autresSecteurs = array_filter($secteurs, fn($k) => $k !== $slug, ARRAY_FILTER_USE_KEY);
?>

<div class="container guide-detail">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" style="margin-top:1.5rem">
        <a href="/">Accueil</a>
        <a href="/guide-local">Guide local</a>
        <span><?= e($s['nom']) ?></span>
    </nav>

    <!-- En-tête -->
    <header class="guide-header">
        <h1><?= e($s['nom']) ?></h1>
        <p class="guide-header__desc"><?= e($s['desc']) ?></p>

        <!-- Métriques -->
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
        <img src="<?= e($s['img']) ?>" alt="Vue de <?= e($s['nom']) ?>" width="1200" height="600" loading="eager">
        <?php if (!empty($s['img_credit'])): ?>
        <figcaption><?= $s['img_credit'] ?></figcaption>
        <?php endif; ?>
    </figure>

    <!-- Contenu + Sidebar -->
    <div class="article-layout">

        <article class="guide-article">
            <section>
                <h2>Présentation du quartier</h2>
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
                        <div class="habitat-pro__zone"><i class="fas fa-map-marker-alt"></i> <?= e($pro['zone']) ?></div>
                        <p class="habitat-pro__note"><?= e($pro['note']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </article>

        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <div class="sidebar-card">
                <h3>Estimer votre bien à <?= e($s['nom']) ?></h3>
                <p>Obtenez une estimation gratuite et personnalisée par Eduardo Desul, expert de ce secteur.</p>
                <a href="/estimation-gratuite" class="btn btn--accent btn--full">Estimation gratuite</a>
            </div>

            <div class="sidebar-card">
                <h3>Autres quartiers</h3>
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
                <p>Eduardo répond sous 24h.</p>
                <a href="/contact" class="btn btn--outline btn--full">Contacter Eduardo</a>
            </div>
        </aside>

    </div>
</div>
