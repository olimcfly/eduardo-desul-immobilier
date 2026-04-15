<?php
$pageTitle    = 'Immobilier Lambesc - Expert immobilier indépendant | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Lambesc avec <?= ADVISOR_NAME ?>, expert immobilier indépendant. Village provençal prisé à 25 km d\'Aix-en-Provence.';
$metaKeywords = 'immobilier Lambesc, expert immobilier Lambesc, estimation immobilière Lambesc, achat immobilier Lambesc, vente immobilière Lambesc';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="lambesc-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/lambesc-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Lambesc</span>
            <h1 id="lambesc-hero-title">Vendre, acheter et estimer sereinement à Lambesc</h1>
            <p class="hero__subtitle">Expert immobilier indépendant, je vous accompagne dans tous vos projets immobiliers à Lambesc et ses alentours.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="lambesc-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Lambesc</span>
            <h2 class="section-title">Votre expert immobilier local</h2>
            <p class="section-subtitle">Village provençal authentique à 25 km à l\'ouest d\'Aix-en-Provence, Lambesc séduit par son marché animé, ses ruelles historiques et sa qualité de vie préservée.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
                <h3 class="card__title">Expertise locale</h3>
                <p class="card__text">Connaissance approfondie du marché immobilier de Lambesc et du secteur Vallée de la Durance.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Réseau solide</h3>
                <p class="card__text">Accès à un réseau d\'acheteurs qualifiés à la recherche de biens provençaux authentiques.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="lambesc-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Lambesc</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">2 700 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.5%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">55 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="lambesc-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Lambesc</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise de votre mas, maison de village ou appartement à Lambesc.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Valorisation optimale de votre bien sur le marché provençal.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Accédez aux meilleures opportunités du marché lambescain.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="lambesc-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Prêt à concrétiser votre projet immobilier à Lambesc ?</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="lambesc-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Lambesc</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels sont les prix moyens au m² à Lambesc ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Les prix se situent entre 2 400 €/m² et 3 000 €/m² selon le type de bien et sa localisation. Les maisons avec terrain et les mas provençaux sont les plus recherchés.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Lambesc est-elle bien reliée à Aix-en-Provence ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Lambesc se trouve à environ 30 minutes d\'Aix-en-Provence via la D7n. Des liaisons par car sont disponibles. La commune est aussi proche de l\'A7 et de l\'A8 pour rejoindre Marseille et la Côte d\'Azur.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels types de biens trouve-t-on à Lambesc ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>On trouve principalement des maisons de village, des villas avec piscine, des mas provençaux et des appartements en centre-bourg. Les biens avec jardin et piscine sont particulièrement prisés.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
