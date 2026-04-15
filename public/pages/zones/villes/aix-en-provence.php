<?php
$pageTitle    = 'Immobilier Aix-en-Provence - Expert indépendant | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier indépendant à Aix-en-Provence. Estimation gratuite, vente et achat de maisons et appartements. Connaissance approfondie du marché aixois depuis plus de 20 ans.';
$metaKeywords = 'immobilier Aix-en-Provence, expert immobilier Aix-en-Provence, estimation immobilière Aix-en-Provence, achat maison Aix-en-Provence, vente appartement Aix-en-Provence, conseiller immobilier indépendant Aix-en-Provence';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<!-- Hero Section -->
<section class="hero hero--premium" aria-labelledby="aix-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/aix-en-provence-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Aix-en-Provence</span>
            <h1 id="aix-hero-title">Vendre, acheter et estimer sereinement à Aix-en-Provence</h1>
            <p class="hero__subtitle">Expert immobilier indépendant depuis plus de 20 ans, je vous accompagne dans tous vos projets immobiliers au cœur du Pays d\'Aix.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline-white">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- Introduction -->
<section class="section section--alt" id="aix-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Aix-en-Provence</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">Capitale du Pays d\'Aix, ville universitaire et culturelle de renommée internationale, Aix-en-Provence est l\'un des marchés immobiliers les plus dynamiques et les plus recherchés de Provence.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <h3 class="card__title">Expertise locale depuis 20 ans</h3>
                <p class="card__text">Connaissance approfondie de chaque quartier d\'Aix : centre-ville, Mazarin, Jas de Bouffan, Puyricard, les Milles, Luynes et tout le Pays d\'Aix.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3 class="card__title">Accompagnement personnalisé</h3>
                <p class="card__text">Un suivi individuel de A à Z : de l\'estimation à la signature chez le notaire, je suis présent à chaque étape de votre projet.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3 class="card__title">Réseau d\'acheteurs qualifiés</h3>
                <p class="card__text">Un portefeuille d\'acquéreurs actifs et financés, pour des ventes rapides au prix juste.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3 class="card__title">Honoraires transparents</h3>
                <p class="card__text">Des honoraires clairs et justifiés, sans mauvaise surprise. L\'indépendance au service de votre intérêt.</p>
            </div>
        </div>
    </div>
</section>

<!-- Marché immobilier -->
<section class="section" id="aix-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Aix-en-Provence</h2>
            <p class="section-subtitle">Aix-en-Provence reste l\'un des marchés les plus porteurs de France, avec une demande structurellement supérieure à l\'offre.</p>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 500 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.8%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">35 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
        <div style="margin-top:2.5rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem">
            <div class="card" data-animate>
                <h3 class="card__title">Appartements</h3>
                <p class="card__text">Les appartements en centre-ville et quartier Mazarin se négocient entre 4 500 et 7 000 €/m². La demande locative très soutenue en fait des investissements sûrs.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Maisons</h3>
                <p class="card__text">Les maisons avec jardin dans les quartiers résidentiels (Puyricard, Jas de Bouffan, Luynes) se situent entre 3 800 et 5 500 €/m² selon la localisation et les prestations.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Prestige & Exception</h3>
                <p class="card__text">Bastides, hôtels particuliers et propriétés de caractère dans les secteurs Mazarin, Tholonet ou Puyricard peuvent dépasser 7 000 €/m².</p>
            </div>
        </div>
    </div>
</section>

<!-- Nos services -->
<section class="section section--alt" id="aix-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Aix-en-Provence</h2>
            <p class="section-subtitle">Un accompagnement complet pour tous vos projets, de l\'estimation à la remise des clés.</p>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre bien, basée sur les ventes récentes dans votre quartier et une analyse comparative approfondie.</p>
                <a href="/estimation-gratuite" class="btn btn--outline" style="margin-top:1rem">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Stratégie de vente sur mesure, photos professionnelles, diffusion multicanale et négociation pour obtenir le meilleur prix.</p>
                <a href="/contact" class="btn btn--outline" style="margin-top:1rem">Vendre mon bien</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Recherche sur mesure selon vos critères, accès aux biens off-market et accompagnement de la visite jusqu\'à l\'acte notarié.</p>
                <a href="/biens" class="btn btn--outline" style="margin-top:1rem">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-banner" id="aix-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Prêt à concrétiser votre projet immobilier à Aix ?</h2>
            <p class="cta-banner__text">Contactez-moi dès aujourd\'hui pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--accent">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline-white">Demander une estimation</a>
            </div>
        </div>
    </div>
</section>

<!-- Quartiers d\'Aix -->
<section class="section section--alt" id="aix-quartiers">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Les quartiers</span>
            <h2 class="section-title">Les principaux quartiers d\'Aix-en-Provence</h2>
            <p class="section-subtitle">Chaque quartier a ses spécificités et son marché propre. Voici les secteurs où j\'interviens le plus.</p>
        </div>
        <div class="cities-grid">
            <a href="/secteurs/quartiers/mazarin" class="city-card">
                <img src="/assets/images/mazarin-thumb.jpg" alt="Quartier Mazarin Aix-en-Provence" loading="lazy">
                <h3>Mazarin</h3>
            </a>
            <a href="/secteurs/quartiers/centre-ville" class="city-card">
                <img src="/assets/images/centre-ville-thumb.jpg" alt="Centre-ville Aix-en-Provence" loading="lazy">
                <h3>Centre-Ville</h3>
            </a>
            <a href="/secteurs/quartiers/jas-de-bouffan" class="city-card">
                <img src="/assets/images/jas-de-bouffan-thumb.jpg" alt="Jas de Bouffan Aix-en-Provence" loading="lazy">
                <h3>Jas de Bouffan</h3>
            </a>
            <a href="/secteurs/quartiers/puyricard" class="city-card">
                <img src="/assets/images/puyricard-thumb.jpg" alt="Puyricard Aix-en-Provence" loading="lazy">
                <h3>Puyricard</h3>
            </a>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" id="aix-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Aix-en-Provence</h2>
            <p class="section-subtitle">Tout ce que vous devez savoir avant de vendre ou d\'acheter à Aix.</p>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Quel est le prix moyen d\'un appartement à Aix-en-Provence ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="accordion__content">
                    <p>Le prix moyen varie fortement selon le quartier. En hyper-centre et quartier Mazarin, comptez entre 4 500 et 7 000 €/m². Dans des quartiers comme le Jas de Bouffan ou les Milles, les prix descendent entre 3 200 et 4 200 €/m². La moyenne toutes zones confondues se situe autour de 4 500 €/m².</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Combien de temps faut-il pour vendre à Aix-en-Provence ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="accordion__content">
                    <p>Un bien bien estimé et bien présenté se vend en moyenne en 35 jours à Aix-en-Provence. Les biens premium dans les quartiers recherchés (Mazarin, centre-ville, Puyricard) peuvent se vendre en moins de 3 semaines. Un prix trop élevé peut au contraire immobiliser un bien pendant plusieurs mois.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Quels sont les quartiers les plus recherchés à Aix-en-Provence ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="accordion__content">
                    <p>Le quartier Mazarin et le centre historique sont les plus prisés pour les appartements de caractère. Puyricard et les hauteurs nord sont très recherchés pour les maisons familiales. Venelles (commune limitrophe) est particulièrement appréciée pour les familles avec enfants grâce à ses écoles réputées.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Faut-il passer par un agent immobilier pour vendre à Aix ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="accordion__content">
                    <p>La vente entre particuliers est possible mais le marché aixois est très concurrentiel. Un prix mal calibré ou une présentation insuffisante peut entraîner des mois de délai. Un expert local apporte une estimation fiable, un réseau d\'acheteurs qualifiés et la sécurisation juridique de la transaction.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Quelle est la tendance du marché immobilier aixois ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <div class="accordion__content">
                    <p>Aix-en-Provence bénéficie d\'une demande structurellement forte grâce à son attractivité nationale et internationale (université, festivals, cadre de vie). La progression annuelle des prix est de l\'ordre de +3 à +5% selon les secteurs. Le marché est résilient même en période de hausse des taux.</p>
                </div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
