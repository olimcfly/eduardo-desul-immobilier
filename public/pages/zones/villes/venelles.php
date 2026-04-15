<?php
$pageTitle    = 'Immobilier Venelles - Expert immobilier indépendant | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Venelles avec <?= ADVISOR_NAME ?>. Commune très prisée au nord d\'Aix-en-Provence, réputée pour ses écoles et sa qualité de vie.';
$metaKeywords = 'immobilier Venelles, expert immobilier Venelles, estimation immobilière Venelles, achat vente maison Venelles, Venelles résidentiel';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="venelles-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/venelles-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Venelles</span>
            <h1 id="venelles-hero-title">Vendre, acheter et estimer sereinement à Venelles</h1>
            <p class="hero__subtitle">L\'une des communes les plus recherchées du Pays d\'Aix, Venelles offre un environnement résidentiel de qualité avec d\'excellentes infrastructures et un accès rapide à Aix-en-Provence.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="venelles-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Venelles</span>
            <h2 class="section-title">La commune résidentielle la plus prisée du nord d\'Aix</h2>
            <p class="section-subtitle">Venelles est régulièrement classée parmi les communes les plus agréables à vivre. À 8 km au nord d\'Aix-en-Provence, elle offre un cadre résidentiel de grande qualité, des écoles réputées, de nombreux équipements et un marché immobilier très dynamique.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Commune très recherchée</h3>
                <p class="card__text">Demande soutenue, turnover rapide — les biens bien présentés se vendent en moins de 6 semaines.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Excellentes infrastructures</h3>
                <p class="card__text">Écoles de la maternelle au lycée, médiathèque, piscine, commerces — une commune idéale pour les familles.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="venelles-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Venelles</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 000 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+4.5%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">42 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="venelles-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Venelles</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre bien dans l\'un des marchés les plus actifs du secteur.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Commercialisation efficace pour tirer le meilleur prix sur ce marché porteur.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Réactivité et réseau pour accéder aux meilleures opportunités avant tout le monde.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="venelles-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Venelles</h2>
            <p class="cta-banner__text">Le marché de Venelles est très dynamique — contactez-moi rapidement pour ne pas manquer les meilleures opportunités.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="venelles-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Venelles</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Pourquoi les prix sont-ils élevés à Venelles ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>La réputation de Venelles pour ses écoles, son cadre de vie et sa proximité avec Aix-en-Provence génère une forte demande face à une offre limitée. Cette pression sur les prix est structurelle et devrait se maintenir.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels types de biens trouve-t-on à Venelles ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>L\'offre est dominée par les maisons pavillonnaires et les villas avec jardin et piscine. Les appartements restent minoritaires. Les biens de 4 à 6 pièces avec espace extérieur sont les plus recherchés par les familles.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
