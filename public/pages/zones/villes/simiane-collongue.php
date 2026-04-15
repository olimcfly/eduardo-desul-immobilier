<?php
// Définition des métadonnées pour la page
$pageTitle = "Immobilier Simiane-Collongue - Expert immobilier indépendant";
$pageDescription = "Découvrez l'immobilier à Simiane-Collongue avec ' . ADVISOR_NAME . ', expert immobilier indépendant. Estimation gratuite, vente et achat d'appartements et maisons.";
$metaKeywords = 'immobilier Simiane-Collongue, expert immobilier Simiane-Collongue, estimation immobilière Simiane-Collongue, achat immobilier Simiane-Collongue, vente immobilière Simiane-Collongue, conseiller immobilier indépendant Simiane-Collongue';

// CSS supplémentaire pour cette page
$extraCss = ['/assets/css/villes.css'];

// Contenu de la page
$pageContent = '
<!-- Hero section -->
<section class="hero hero--premium" aria-labelledby="simiane-collongue-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/simiane-collongue-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Simiane-Collongue</span>
            <h1 id="simiane-collongue-hero-title">Vendre, acheter et estimer sereinement à Simiane-Collongue</h1>
            <p class="hero__subtitle" data-animate>Expert immobilier indépendant, je vous accompagne dans tous vos projets immobiliers à Simiane-Collongue et ses alentours.</p>
            <div class="hero__actions" data-animate>
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- Introduction section -->
<section class="section section--intro">
    <div class="container">
        <div class="section__content">
            <h2 class="section__title">Votre expert immobilier à Simiane-Collongue</h2>
            <p class="section__text">Spécialiste de l\'immobilier à Simiane-Collongue, je vous propose mes services pour tous vos projets : estimation gratuite, vente, achat, location et gestion locative.</p>
            <p class="section__text">Avec une connaissance approfondie du marché local et une approche personnalisée, je vous accompagne pour concrétiser vos projets immobiliers dans les meilleures conditions.</p>
        </div>
    </div>
</section>

<!-- Market section -->
<section class="section section--market">
    <div class="container">
        <div class="section__content">
            <h2 class="section__title">Le marché immobilier à Simiane-Collongue</h2>
            <div class="market__stats">
                <div class="market__stat">
                    <h3 class="market__stat-title">Prix moyen au m²</h3>
                    <p class="market__stat-value">3 200 €</p>
                    <p class="market__stat-text">Prix moyen pour un appartement</p>
                </div>
                <div class="market__stat">
                    <h3 class="market__stat-title">Temps de vente moyen</h3>
                    <p class="market__stat-value">45 jours</p>
                    <p class="market__stat-text">Durée moyenne de vente</p>
                </div>
                <div class="market__stat">
                    <h3 class="market__stat-title">Demande locale</h3>
                    <p class="market__stat-value">Élevée</p>
                    <p class="market__stat-text">Demande importante pour les biens bien situés</p>
                </div>
            </div>
            <p class="section__text">Le marché immobilier à Simiane-Collongue est dynamique, avec une forte demande pour les biens bien situés et en bon état. Les prix ont augmenté de 5% sur les 12 derniers mois.</p>
        </div>
    </div>
</section>

<!-- Services section -->
<section class="section section--services">
    <div class="container">
        <div class="section__content">
            <h2 class="section__title">Nos services à Simiane-Collongue</h2>
            <div class="services__grid">
                <div class="service__card">
                    <div class="service__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M12 4h9"></path>
                            <path d="M3 9h2m13 0h2M3 15h2m13 0h2"></path>
                            <path d="M4 22l-2 -2"></path>
                            <path d="M20 22l2 -2"></path>
                            <path d="M12 12l0 12"></path>
                        </svg>
                    </div>
                    <h3 class="service__title">Estimation gratuite</h3>
                    <p class="service__text">Obtenez une estimation précise et gratuite de votre bien immobilier à Simiane-Collongue.</p>
                </div>
                <div class="service__card">
                    <div class="service__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.3l-2.1-.7l-5.7 4.5-2.1-2L10.5 18l-5.7-4.5 2.1-.7 4.5 5.7 4.5-5.7zm-8.4.1l-4.5 5.7 4.5 5.7 5.7-4.5-5.7-4.5z"></path>
                        </svg>
                    </div>
                    <h3 class="service__title">Vente immobilière</h3>
                    <p class="service__text">Bénéficiez d\'un accompagnement complet pour la vente de votre maison ou appartement à Simiane-Collongue.</p>
                </div>
                <div class="service__card">
                    <div class="service__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <path d="M3.5 15.5a4 4 0 0 1 4 4l7 0 7 0a4 4 0 0 1 4-4l0-7-7 0-7 0 0 7z"></path>
                            <path d="M15 6h6m-6 6h6"></path>
                        </svg>
                    </div>
                    <h3 class="service__title">Achat immobilier</h3>
                    <p class="service__text">Trouvez le bien immobilier idéal à Simiane-Collongue avec l\'aide d\'un expert local.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="section section--cta">
    <div class="container">
        <div class="section__content">
            <h2 class="section__title">Prêt à concrétiser votre projet immobilier à Simiane-Collongue ?</h2>
            <p class="section__text">Contactez-moi dès aujourd\'hui pour une consultation gratuite et sans engagement.</p>
            <a href="/contact" class="btn btn--primary">Contactez-moi</a>
        </div>
    </div>
</section>

<!-- FAQ section -->
<section class="section section--faq">
    <div class="container">
        <div class="section__content">
            <h2 class="section__title">Foire aux questions</h2>
            <div class="accordion">
                <div class="accordion__item">
                    <button class="accordion__button">
                        <span class="accordion__title">Quels sont les prix moyens à Simiane-Collongue ?</span>
                        <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                    <div class="accordion__content">
                        <p>Les prix moyens à Simiane-Collongue sont d\'environ 3 200 €/m² pour les appartements et 3 500 €/m² pour les maisons. Ces prix peuvent varier en fonction de la localisation, de l\'état du bien et des prestations.</p>
                    </div>
                </div>
                <div class="accordion__item">
                    <button class="accordion__button">
                        <span class="accordion__title">Quels sont les quartiers les plus recherchés à Simiane-Collongue ?</span>
                        <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                    <div class="accordion__content">
                        <p>Les zones résidentielles proches des commodités et des écoles sont les plus recherchées. Les biens avec des espaces extérieurs et une bonne exposition sont particulièrement prisés.</p>
                    </div>
                </div>
                <div class="accordion__item">
                    <button class="accordion__button">
                        <span class="accordion__title">Combien de temps faut-il pour vendre un bien à Simiane-Collongue ?</span>
                        <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                    <div class="accordion__content">
                        <p>Le temps de vente moyen à Simiane-Collongue est d\'environ 45 jours pour les biens bien présentés et correctement estimés. Ce délai peut varier en fonction du marché et des spécificités du bien.</p>
                    </div>
                </div>
                <div class="accordion__item">
                    <button class="accordion__button">
                        <span class="accordion__title">Quels sont les atouts de Simiane-Collongue ?</span>
                        <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                    <div class="accordion__content">
                        <p>Simiane-Collongue offre un cadre de vie agréable avec des paysages naturels préservés, des commodités et une bonne accessibilité. La commune est également réputée pour son dynamisme associatif et sa qualité de vie.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
