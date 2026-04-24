<?php
$pageTitle    = 'Immobilier Bordeaux (33000) - Conseiller immobilier | Eduardo Desul';
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Bordeaux avec Eduardo Desul, conseiller immobilier. Capitale de la Gironde, ville dynamique et attractive au cœur de la métropole.';
$metaKeywords = 'immobilier Bordeaux, conseiller immobilier Bordeaux, estimation immobilière Bordeaux 33, achat vente appartement maison Bordeaux, Bordeaux 33000';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium hero--inner" aria-labelledby="bordeaux-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/bordeaux-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Bordeaux</span>
            <h1 id="bordeaux-hero-title">Vendre, acheter et estimer sereinement à Bordeaux</h1>
            <p class="hero__subtitle">Capitale mondiale du vin et ville d\'art et d\'histoire, Bordeaux séduit par son architecture classée, sa scène culturelle vivante, ses quartiers variés et son dynamisme économique au cœur de la Gironde.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="bordeaux-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Bordeaux</span>
            <h2 class="section-title">Une métropole attractive et en plein essor</h2>
            <p class="section-subtitle">Bordeaux s\'impose comme l\'une des villes les plus prisées de France. Entre son centre historique inscrit au patrimoine UNESCO, ses quais animés, ses quartiers tendance (Chartrons, Saint-Michel, Bacalan) et son bassin d\'emploi en forte croissance, la ville attire chaque année de nouveaux habitants et investisseurs.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Patrimoine et qualité de vie</h3>
                <p class="card__text">Centre historique classé UNESCO, quais de la Garonne, parcs et jardins — Bordeaux offre un cadre de vie exceptionnel alliant patrimoine, gastronomie et art de vivre à la bordelaise.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Accessibilité et rayonnement</h3>
                <p class="card__text">À 2h de Paris en TGV, desservie par un réseau de trams performant, proche de l\'océan Atlantique et du vignoble bordelais — Bordeaux cumule les atouts pour y vivre et investir.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="bordeaux-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Bordeaux</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 050 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">-2,1 %</div>
                <div class="stat-card__label">Évolution sur 12 mois</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">58 jours</div>
                <div class="stat-card__label">Délai moyen de vente</div>
            </div>
        </div>

        <div class="market-details grid-2" style="margin-top:2.5rem">
            <div class="card" data-animate>
                <h3 class="card__title">Appartements</h3>
                <p class="card__text">Le marché des appartements à Bordeaux est très actif, notamment dans les quartiers Chartrons, Saint-Pierre, Nansouty et les secteurs en mutation comme Bacalan et les Bassins à Flot. Les studios et T2 sont très recherchés par les étudiants et investisseurs locatifs.</p>
                <ul class="card__list">
                    <li>Studio / T1 : <strong>4 500 – 5 500 €/m²</strong></li>
                    <li>T2 / T3 : <strong>3 800 – 4 800 €/m²</strong></li>
                    <li>T4 et + : <strong>3 500 – 4 500 €/m²</strong></li>
                </ul>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Maisons</h3>
                <p class="card__text">Les maisons bordelaises (chartreuses, maisons de ville avec jardin) sont très convoitées, notamment dans les quartiers Caudéran, Saint-Augustin, Le Bouscat et le secteur des Aubiers. L\'offre reste limitée face à une demande soutenue.</p>
                <ul class="card__list">
                    <li>Maison 4-5 pièces : <strong>4 800 – 6 500 €/m²</strong></li>
                    <li>Maison avec jardin : <strong>5 000 – 7 000 €/m²</strong></li>
                    <li>Chartreuse / prestige : <strong>sur estimation</strong></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="bordeaux-quartiers">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Quartiers</span>
            <h2 class="section-title">Les quartiers de Bordeaux</h2>
            <p class="section-subtitle">Bordeaux est une ville aux multiples visages. Chaque quartier a son identité, son marché et ses opportunités.</p>
        </div>
        <div class="grid-3">
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Chartrons / Fondaudège</h3>
                <p class="card__text">Le quartier bobo par excellence. Galeries d\'art, marchés vintage, appartements haussmanniens et une vie de quartier intense. Très prisé des cadres et des familles aisées.</p>
            </div>
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Saint-Pierre / Saint-Michel</h3>
                <p class="card__text">Le cœur historique de Bordeaux. Architecture classée, ruelles animées, marché des Capucins. Idéal pour un investissement locatif ou une résidence principale de caractère.</p>
            </div>
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Bacalan / Bassins à Flot</h3>
                <p class="card__text">Quartier en pleine mutation, symbole du renouveau bordelais. Lofts, résidences neuves, Cité du Vin, Darwin — une valeur montante pour les investisseurs avisés.</p>
            </div>
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Caudéran</h3>
                <p class="card__text">Village dans la ville. Maisons avec jardins, écoles réputées, calme et verdure à deux pas du centre. Le quartier idéal pour les familles cherchant espace et sérénité.</p>
            </div>
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Nansouty / Saint-Genès</h3>
                <p class="card__text">Quartier résidentiel et bien desservi, apprécié des familles et des étudiants de l\'université. Marché immobilier stable, bon rapport qualité-prix.</p>
            </div>
            <div class="card card--alt" data-animate>
                <h3 class="card__title">Mériadeck / Victoire</h3>
                <p class="card__text">Quartier estudiantin et administratif en plein cœur de Bordeaux. Forte demande locative, idéal pour les investisseurs en quête de rendement régulier.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="bordeaux-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Mes services</span>
            <h2 class="section-title">Mes services immobiliers à Bordeaux</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre bien basée sur ma connaissance approfondie des quartiers bordelais et des références de vente récentes.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Stratégie de commercialisation sur mesure pour vendre votre bien bordelais au meilleur prix, avec une mise en valeur optimale et un réseau d\'acheteurs qualifiés.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Accédez aux meilleures opportunités à Bordeaux grâce à mon réseau local, ma réactivité et ma maîtrise des spécificités de chaque quartier.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="bordeaux-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Bordeaux</h2>
            <p class="cta-banner__text">Je connais Bordeaux et ses quartiers dans leurs moindres détails. Contactez-moi pour un accompagnement local, personnalisé et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="bordeaux-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Bordeaux</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels types de biens trouve-t-on à Bordeaux ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Bordeaux offre une grande diversité : appartements haussmanniens et contemporains dans le centre, maisons de ville et chartreuses dans les quartiers résidentiels, lofts et logements neufs dans les secteurs en reconversion (Bacalan, Bassins à Flot), et pavillons avec jardins à Caudéran et Saint-Augustin.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Bordeaux est-elle bien desservie par les transports ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Bordeaux dispose d\'un réseau de tramway (4 lignes A, B, C, D) très efficace couvrant toute la métropole, complété par des lignes de bus et un service de vélos en libre-service (V3). La gare Saint-Jean relie Paris en 2h05 par TGV. L\'aéroport de Bordeaux-Mérignac dessert de nombreuses destinations nationales et internationales.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Est-ce le bon moment pour acheter à Bordeaux ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Après une forte hausse entre 2015 et 2022, les prix bordelais ont connu une légère correction (-2,1 % sur 12 mois). Cette stabilisation crée de réelles opportunités pour les acheteurs, notamment dans les quartiers en développement. Bordeaux reste une valeur sûre à moyen et long terme grâce à son attractivité économique et culturelle.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quel budget prévoir pour acheter à Bordeaux ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Le budget varie selon les quartiers et le type de bien. Pour un T2 en centre-ville comptez entre 200 000 € et 300 000 €. Une maison avec jardin à Caudéran se négocie entre 450 000 € et 700 000 €. Les secteurs comme Bacalan ou Belcier offrent des prix plus accessibles, idéaux pour un premier achat ou un investissement locatif.</p>
                </div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
