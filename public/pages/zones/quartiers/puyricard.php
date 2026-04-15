<?php
$pageTitle    = 'Immobilier Puyricard Aix-en-Provence - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier à Puyricard, quartier résidentiel premium au nord d\'Aix-en-Provence. Villas, maisons avec piscine, environnement calme et verdoyant.';
$metaKeywords = 'immobilier Puyricard, maison Puyricard Aix, villa Puyricard, expert immobilier nord Aix-en-Provence, résidentiel Puyricard';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="puyricard-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/puyricard-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Puyricard — Aix-en-Provence</span>
            <h1 id="puyricard-hero-title">L\'immobilier premium à Puyricard</h1>
            <p class="hero__subtitle">Village intégré à Aix-en-Provence au nord de la ville, Puyricard est très prisé pour ses villas avec piscine, ses espaces verts et son ambiance résidentielle calme à seulement 15 minutes du centre.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="puyricard-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Puyricard</span>
            <h2 class="section-title">Le quartier résidentiel premium du nord d\'Aix</h2>
            <p class="section-subtitle">Puyricard est un village-quartier d\'Aix-en-Provence réputé pour ses grandes propriétés, ses espaces arborés et sa qualité de vie. À 10 km du centre-ville d\'Aix, il offre un cadre de vie préservé tout en restant à portée de toutes les commodités.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Grandes propriétés</h3>
                <p class="card__text">Villas, bastides et maisons spacieuses avec jardins, piscines et terrains arborés.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Cadre de vie exceptionnel</h3>
                <p class="card__text">Environnement calme et verdoyant, loin de l\'agitation urbaine mais proche de tout.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="puyricard-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Puyricard</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 400 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+4.1%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">40 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="puyricard-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Puyricard</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre villa ou maison dans ce secteur recherché.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Commercialisation efficace auprès d\'une clientèle ciblée et qualifiée.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Accès aux meilleures propriétés du secteur, y compris off-market.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="puyricard-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Puyricard</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation personnalisée et sans engagement.</p>
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
