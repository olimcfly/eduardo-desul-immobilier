<?php
$pageTitle    = 'Immobilier Centre-Ville Aix-en-Provence - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier au centre-ville d\'Aix-en-Provence. Appartements anciens, biens rénovés, commerces — <?= ADVISOR_NAME ?> vous accompagne pour votre projet en hyper-centre.';
$metaKeywords = 'immobilier centre-ville Aix-en-Provence, appartement centre Aix, achat vente immobilier hyper-centre Aix, expert immobilier Aix centre';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="centre-ville-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/centre-ville-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Centre-Ville — Aix-en-Provence</span>
            <h1 id="centre-ville-hero-title">L\'immobilier au cœur d\'Aix-en-Provence</h1>
            <p class="hero__subtitle">Le centre historique d\'Aix-en-Provence concentre les biens les plus recherchés : appartements anciens, rez-de-chaussée commerciaux et immeubles de caractère. Un marché exigeant qui nécessite un expert local.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="centre-ville-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Centre-Ville</span>
            <h2 class="section-title">Au cœur de la vie aixoise</h2>
            <p class="section-subtitle">Le centre-ville d\'Aix-en-Provence regroupe le Cours Mirabeau, les rues commerçantes, les places animées et un patrimoine architectural exceptionnel. Son immobilier est l\'un des plus recherchés de Provence.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Immobilier de caractère</h3>
                <p class="card__text">Appartements anciens avec moulures, parquet, hauteurs sous plafond — un charme architectural incomparable.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Marché dynamique</h3>
                <p class="card__text">Forte demande, biens bien présentés vendus rapidement — la réactivité est primordiale.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="centre-ville-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier au centre-ville</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">5 500 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+4.2%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">32 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="centre-ville-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services au centre-ville d\'Aix</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre appartement ou local en hyper-centre.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Mise en valeur et commercialisation efficace dans ce marché compétitif.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Réactivité et réseau pour accéder aux meilleures opportunités du centre.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="centre-ville-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet au centre-ville d\'Aix-en-Provence</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation personnalisée sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
