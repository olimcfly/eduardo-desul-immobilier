<?php
// Définition des métadonnées pour la page
$pageTitle = "Immobilier Bouc-Bel-Air - Expert immobilier indépendant";
$pageDescription = "Découvrez l'immobilier à Bouc-Bel-Air avec ' . ADVISOR_NAME . ', expert immobilier indépendant. Estimation gratuite, vente et achat d'appartements et maisons.";
$metaKeywords = 'immobilier Bouc-Bel-Air, expert immobilier Bouc-Bel-Air, estimation immobilière Bouc-Bel-Air, achat immobilier Bouc-Bel-Air, vente immobilière Bouc-Bel-Air, conseiller immobilier indépendant Bouc-Bel-Air';

// CSS supplémentaire pour cette page
$extraCss = ['/assets/css/villes.css'];

// Contenu de la page
$pageContent = '
<!-- Hero section -->
<section class="hero hero--premium" aria-labelledby="bouc-bel-air-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/bouc-bel-air-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Bouc-Bel-Air</span>
            <h1 id="bouc-bel-air-hero-title">Vendre, acheter et estimer sereinement à Bouc-Bel-Air</h1>
            <p class="hero__subtitle" data-animate>Expert immobilier indépendant, je vous accompagne dans tous vos projets immobiliers à Bouc-Bel-Air et ses alentours.</p>
            <div class="hero__actions" data-animate>
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- Introduction section -->
<section class="section section--alt" id="bouc-bel-air-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Bouc-Bel-Air</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">Spécialiste de l\'immobilier à Bouc-Bel-Air, je mets mon expertise et mon réseau à votre service pour concrétiser vos projets immobiliers.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Expertise locale</h3>
                <p class="card__text">Connaissance approfondie des spécificités du marché immobilier de Bouc-Bel-Air.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h20c1.1 0 2 .9 2 2v10z"></path>
                        <path d="M16 9h4l-4 11-4-11z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Accompagnement personnalisé</h3>
                <p class="card__text">Service sur mesure pour répondre à vos besoins spécifiques et vos attentes.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Réseau solide</h3>
                <p class="card__text">Accès à un réseau d\'acheteurs et de vendeurs qualifiés pour des transactions rapides.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Transparence</h3>
                <p class="card__text">Processus clair et transparent pour une relation de confiance tout au long de la transaction.</p>
            </div>
        </div>
    </div>
</section>

<!-- Marché immobilier section -->
<section class="section" id="bouc-bel-air-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Bouc-Bel-Air</h2>
            <p class="section-subtitle">Analyse des tendances et des prix dans la commune de Bouc-Bel-Air.</p>
        </div>
        <div class="grid-2">
            <div class="card" data-animate>
                <h3 class="card__title">Prix au m²</h3>
                <p class="card__text">Les prix varient selon les quartiers, de 2 800 €/m² dans les zones plus éloignées à plus de 3 800 €/m² dans les zones résidentielles prisées.</p>
                <div class="trust-item">
                    <div class="trust-item__value">+3,2%</div>
                    <div class="trust-item__label">Évolution annuelle</div>
                </div>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Temps de vente</h3>
                <p class="card__text">En moyenne, un bien immobilier se vend en 45 jours à Bouc-Bel-Air, avec des variations selon le type de bien et sa localisation.</p>
                <div class="trust-item">
                    <div class="trust-item__value">45 jours</div>
                    <div class="trust-item__label">Temps moyen de vente</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services section -->
<section class="section section--alt" id="bouc-bel-air-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Bouc-Bel-Air</h2>
            <p class="section-subtitle">Un accompagnement complet pour tous vos projets immobiliers.</p>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Une évaluation précise et gratuite de votre bien immobilier à Bouc-Bel-Air.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h20c1.1 0 2 .9 2 2v10z"></path>
                        <path d="M16 9h4l-4 11-4-11z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Un accompagnement personnalisé pour vendre votre bien au meilleur prix.</p>
                <a href="/vente-immobiliere" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Trouvez le bien idéal à Bouc-Bel-Air avec notre accompagnement expert.</p>
                <a href="/achat-immobilier" class="btn btn--outline">Acheter un bien</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="cta-banner" id="bouc-bel-air-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Prêt à concrétiser votre projet immobilier à Bouc-Bel-Air ?</h2>
            <p class="cta-banner__text">Contactez-moi dès aujourd\'hui pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ section -->
<section class="section section--alt" id="bouc-bel-air-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Bouc-Bel-Air</h2>
            <p class="section-subtitle">Trouvez des réponses aux questions les plus courantes sur le marché immobilier de Bouc-Bel-Air.</p>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels sont les prix moyens au m² à Bouc-Bel-Air ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Les prix varient de 2 800 €/m² dans les zones plus éloignées à plus de 3 800 €/m² dans les zones résidentielles prisées. Les prix moyens se situent autour de 3 300 €/m².</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Combien de temps faut-il pour vendre un bien à Bouc-Bel-Air ?</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>En moyenne, il faut environ 45 jours pour vendre un bien à Bouc-Bel-Air. Ce délai peut varier en fonction du type de bien, de sa localisation et de son prix.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button">
                    <span class="accordion__title">Quels sont les frais de notaire pour un achat immobilier à Bouc-Bel-Air ?</span>
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
                    <span class="accordion__title">Quels sont les quartiers les plus recherchés à Bouc-Bel-Air ?</span>
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
