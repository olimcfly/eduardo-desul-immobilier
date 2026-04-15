<?php
$pageTitle    = 'Immobilier Meyreuil - Expert immobilier indépendant | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Meyreuil avec Pascal Hamm. Commune résidentielle à 10 km d\'Aix-en-Provence avec une belle qualité de vie.';
$metaKeywords = 'immobilier Meyreuil, expert immobilier Meyreuil, estimation immobilière Meyreuil, achat vente immobilier Meyreuil';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="meyreuil-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/meyreuil-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Meyreuil</span>
            <h1 id="meyreuil-hero-title">Vendre, acheter et estimer sereinement à Meyreuil</h1>
            <p class="hero__subtitle">Commune résidentielle prisée à l\'est d\'Aix-en-Provence, Meyreuil offre un cadre de vie calme avec un accès facile aux axes routiers et aux services.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="meyreuil-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Meyreuil</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">À 10 km à l\'est d\'Aix-en-Provence, Meyreuil est une commune résidentielle qui séduit par la qualité de ses lotissements, ses espaces verts et sa situation idéale entre Aix et la plaine de Berre.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Maisons pavillonnaires</h3>
                <p class="card__text">Meyreuil est réputée pour ses quartiers résidentiels de qualité avec jardins et espaces verts.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Proximité d\'Aix</h3>
                <p class="card__text">10 minutes d\'Aix-en-Provence, accès direct à l\'A8 — idéal pour les actifs et les familles.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="meyreuil-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Meyreuil</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 000 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.8%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">50 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="meyreuil-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Meyreuil</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre bien au prix du marché local.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Valorisation et commercialisation de votre bien auprès d\'acheteurs motivés.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Accès aux meilleures offres du marché de Meyreuil et du secteur est d\'Aix.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="meyreuil-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Prêt à concrétiser votre projet à Meyreuil ?</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="meyreuil-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Meyreuil</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels sont les prix au m² à Meyreuil ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Les prix varient entre 2 600 €/m² et 3 500 €/m² selon le type de bien. Les maisons avec piscine dans les quartiers résidentiels se situent dans la tranche haute, tandis que les maisons nécessitant des travaux sont plus accessibles.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Meyreuil est-elle bien desservie ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Meyreuil dispose d\'un accès direct à l\'A8 (Aix-en-Provence Est) et est relié à Aix par des lignes de bus régulières. La commune dispose de commerces de proximité, d\'écoles primaires et d\'équipements sportifs.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
