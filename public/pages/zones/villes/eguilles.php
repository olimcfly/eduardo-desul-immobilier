<?php
// Définition des métadonnées pour la page
$pageTitle = "Immobilier Eguilles - Expert immobilier indépendant";
$pageDescription = "Découvrez l'immobilier à Eguilles avec <?= ADVISOR_NAME ?>, expert immobilier indépendant. Estimation gratuite, vente et achat d'appartements et maisons.";
$metaKeywords = 'immobilier Eguilles, expert immobilier Eguilles, estimation immobilière Eguilles, achat immobilier Eguilles, vente immobilière Eguilles, conseiller immobilier indépendant Eguilles';

// CSS supplémentaire pour cette page
$extraCss = ['/assets/css/villes.css'];

// Contenu de la page
$pageContent = '
<!-- Hero section -->
<section class="hero hero--premium" aria-labelledby="eguilles-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/eguilles-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Eguilles</span>
            <h1 id="eguilles-hero-title">Vendre, acheter et estimer sereinement à Eguilles</h1>
            <p class="hero__subtitle" data-animate>Expert immobilier indépendant, je vous accompagne dans tous vos projets immobiliers à Eguilles et ses alentours.</p>
            <div class="hero__actions" data-animate>
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- Introduction section -->
<section class="section section--alt" id="eguilles-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Eguilles</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">Spécialiste de l\'immobilier à Eguilles, je mets mon expertise et mon réseau à votre service pour concrétiser vos projets immobiliers.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-21M2 8h20v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"></path>
                        <polyline points="18.5 18 12 22 5.5 18"></polyline>
                    </svg>
                </div>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Obtenez une estimation précise et gratuite de votre bien immobilier à Eguilles.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Bénéficiez d\'un accompagnement personnalisé pour la vente de votre maison ou appartement à Eguilles.</p>
            </div>
        </div>
    </div>
</section>

<!-- Market section -->
<section class="section" id="eguilles-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Eguilles</h2>
            <p class="section-subtitle">Découvrez les tendances actuelles du marché immobilier à Eguilles.</p>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">2 500 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.2%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">90 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
        <div class="market-chart" data-animate>
            <!-- Graphique du marché immobilier -->
            <img src="/assets/images/eguilles-market-chart.png" alt="Évolution des prix immobiliers à Eguilles" width="100%">
        </div>
    </div>
</section>

<!-- Services section -->
<section class="section section--alt" id="eguilles-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Un accompagnement complet</h2>
            <p class="section-subtitle">Je vous accompagne dans tous vos projets immobiliers à Eguilles.</p>
        </div>
        <div class="grid-3">
            <div class="service-card" data-animate>
                <div class="service-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="service-card__title">Vente immobilière</h3>
                <p class="service-card__text">Vente de maisons, appartements, terrains et locaux commerciaux à Eguilles.</p>
            </div>
            <div class="service-card" data-animate>
                <div class="service-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2v2a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V2"></path>
                        <path d="M19 7v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7"></path>
                        <rect x="3" y="11" width="18" height="2"></rect>
                    </svg>
                </div>
                <h3 class="service-card__title">Achat immobilier</h3>
                <p class="service-card__text">Recherche et acquisition de biens immobiliers à Eguilles selon vos critères.</p>
            </div>
            <div class="service-card" data-animate>
                <div class="service-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="service-card__title">Estimation gratuite</h3>
                <p class="service-card__text">Évaluation précise de la valeur de votre bien immobilier à Eguilles.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="section section--cta" id="eguilles-cta">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Contactez-moi</span>
            <h2 class="section-title">Prêt à concrétiser votre projet immobilier à Eguilles ?</h2>
            <p class="section-subtitle">Contactez-moi dès aujourd\'hui pour bénéficier de mon expertise et de mon accompagnement personnalisé.</p>
        </div>
        <div class="cta-actions" data-animate>
            <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
            <a href="/contact" class="btn btn--outline">Nous contacter</a>
        </div>
    </div>
</section>

<!-- FAQ section -->
<section class="section" id="eguilles-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes</h2>
            <p class="section-subtitle">Trouvez les réponses aux questions les plus fréquentes sur l\'immobilier à Eguilles.</p>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels sont les prix moyens au m² à Eguilles ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Les prix moyens au m² à Eguilles varient entre 2 300 € et 2 700 € selon le type de bien et son emplacement. Les maisons se situent généralement entre 2 500 € et 2 700 €, tandis que les appartements sont autour de 2 300 € à 2 500 €.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Combien de temps faut-il pour vendre un bien à Eguilles ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Le temps moyen de vente à Eguilles est d\'environ 90 jours. Ce délai peut varier en fonction du type de bien, de son prix et de sa localisation. Une bonne préparation et une mise en valeur du bien peuvent réduire ce délai.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels sont les frais de notaire pour un achat immobilier à Eguilles ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Les frais de notaire représentent environ 7 à 8% du prix d\'achat pour un bien ancien et environ 2 à 3% pour un bien neuf. Ces frais incluent les droits de mutation, les émoluments du notaire et les frais administratifs.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels sont les quartiers les plus recherchés à Eguilles ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Les zones résidentielles proches des commodités et des écoles sont les plus recherchées. Les biens avec des espaces extérieurs et une bonne exposition sont particulièrement prisés.</p>
                </div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
