<?php
$pageTitle    = 'Immobilier Luynes Aix-en-Provence - Expert | Pascal Hamm';
$metaDesc     = 'Expert immobilier à Luynes, quartier résidentiel à l\'ouest d\'Aix-en-Provence. Maisons pavillonnaires, résidences et appartements dans un cadre calme et bien desservi.';
$metaKeywords = 'immobilier Luynes, maison Luynes Aix, appartement Luynes, expert immobilier ouest Aix-en-Provence, Luynes résidentiel';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="luynes-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/luynes-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Luynes — Aix-en-Provence</span>
            <h1 id="luynes-hero-title">L\'immobilier à Luynes</h1>
            <p class="hero__subtitle">Quartier résidentiel et commune associée à Aix-en-Provence, Luynes offre un cadre de vie calme avec de bonnes infrastructures et un accès facile aux axes autoroutiers.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="luynes-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Luynes</span>
            <h2 class="section-title">Un quartier résidentiel bien positionné</h2>
            <p class="section-subtitle">Luynes est un secteur résidentiel à l\'ouest d\'Aix-en-Provence qui attire les familles pour ses lotissements de qualité, son école et sa proximité avec les zones commerciales et les axes routiers (A8 et A7).</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Lotissements de qualité</h3>
                <p class="card__text">Maisons pavillonnaires récentes, résidences sécurisées — un cadre de vie moderne et pratique.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Bonne accessibilité</h3>
                <p class="card__text">Accès rapide à l\'A8, aux zones d\'activités et au centre d\'Aix en 15 minutes.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="luynes-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Luynes</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 200 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+2.8%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">52 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="luynes-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Luynes</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre bien au prix du marché local.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Commercialisation efficace de votre bien auprès d\'acheteurs qualifiés.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Sélection de biens correspondant à votre projet à Luynes et dans le secteur ouest d\'Aix.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="luynes-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Luynes</h2>
            <p class="cta-banner__text">Contactez-moi pour un accompagnement personnalisé et sans engagement.</p>
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
