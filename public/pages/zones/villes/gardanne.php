<?php
$pageTitle    = 'Immobilier Gardanne - Expert immobilier indépendant | Pascal Hamm';
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Gardanne avec Pascal Hamm, expert immobilier indépendant. Connaissance approfondie du marché local et accompagnement personnalisé.';
$metaKeywords = 'immobilier Gardanne, expert immobilier Gardanne, estimation immobilière Gardanne, achat immobilier Gardanne, vente immobilière Gardanne';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="gardanne-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/gardanne-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Gardanne</span>
            <h1 id="gardanne-hero-title">Vendre, acheter et estimer sereinement à Gardanne</h1>
            <p class="hero__subtitle">Expert immobilier indépendant, je vous accompagne dans tous vos projets immobiliers à Gardanne et ses alentours.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="gardanne-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Gardanne</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">Commune dynamique à 15 km au sud-est d\'Aix-en-Provence, Gardanne offre un cadre de vie agréable avec des prix encore accessibles et un bassin d\'emploi solide.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
                <h3 class="card__title">Expertise locale</h3>
                <p class="card__text">Connaissance approfondie du marché immobilier de Gardanne et de ses quartiers.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Accompagnement personnalisé</h3>
                <p class="card__text">Service sur mesure adapté à vos besoins pour vendre, acheter ou investir à Gardanne.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="gardanne-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Gardanne</h2>
            <p class="section-subtitle">Analyse des tendances et des prix dans la commune de Gardanne.</p>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">2 200 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+2.1%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">60 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="gardanne-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Gardanne</h2>
            <p class="section-subtitle">Un accompagnement complet pour tous vos projets immobiliers.</p>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise et gratuite de votre bien immobilier à Gardanne.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Accompagnement personnalisé pour vendre votre bien au meilleur prix.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Trouvez le bien idéal à Gardanne avec notre expertise locale.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="gardanne-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Prêt à concrétiser votre projet immobilier à Gardanne ?</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="gardanne-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Gardanne</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels sont les prix moyens au m² à Gardanne ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Les prix varient de 1 900 €/m² pour les biens les plus abordables à 2 600 €/m² pour les maisons bien situées. La moyenne se situe autour de 2 200 €/m², ce qui reste attractif par rapport à Aix-en-Provence.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Combien de temps faut-il pour vendre un bien à Gardanne ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>En moyenne, il faut environ 60 jours pour vendre un bien à Gardanne. Un prix cohérent avec le marché et une bonne mise en valeur permettent de réduire ce délai.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels sont les avantages d\'habiter à Gardanne ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Gardanne bénéficie d\'une bonne desserte en transports (TER vers Marseille et Aix), d\'un bassin d\'emploi diversifié, de commerces de proximité et d\'un cadre naturel agréable en bordure du massif de l\'Étoile.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
