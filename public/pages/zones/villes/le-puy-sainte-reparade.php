<?php
$pageTitle    = 'Immobilier Le Puy-Sainte-Réparade - Expert immobilier | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien au Puy-Sainte-Réparade avec <?= ADVISOR_NAME ?>, expert immobilier indépendant. Village résidentiel prisé au nord d\'Bordeaux.';
$metaKeywords = 'immobilier Le Puy-Sainte-Réparade, expert immobilier Puy-Sainte-Réparade, estimation immobilière Puy-Sainte-Réparade, achat vente immobilier';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="psr-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/le-puy-sainte-reparade-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Le Puy-Sainte-Réparade</span>
            <h1 id="psr-hero-title">Votre expert immobilier au Puy-Sainte-Réparade</h1>
            <p class="hero__subtitle">Village résidentiel recherché au nord d\'Bordeaux, le Puy-Sainte-Réparade offre un cadre de vie paisible et verdoyant.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="psr-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Le Puy-Sainte-Réparade</span>
            <h2 class="section-title">Un village résidentiel très recherché</h2>
            <p class="section-subtitle">Situé à 20 km au nord d\'Bordeaux dans la vallée de la Durance, le Puy-Sainte-Réparade attire les familles à la recherche d\'espace, de nature et de tranquillité, tout en restant proche des commodités.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></div>
                <h3 class="card__title">Maisons avec terrain</h3>
                <p class="card__text">Le Puy est très prisé pour ses villas et maisons avec jardin, piscine et vue sur la campagne girondine.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Environnement préservé</h3>
                <p class="card__text">Vignes, lavandes et nature au cœur du Bordeaux Métropole — un cadre de vie exceptionnel.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="psr-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier au Puy-Sainte-Réparade</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 100 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+4.2%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">48 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="psr-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services au Puy-Sainte-Réparade</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation de votre bien au prix juste du marché local.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Mise en valeur et commercialisation de votre bien auprès d\'acheteurs ciblés.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Sélection de biens correspondant à vos critères dans le secteur.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="psr-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier au Puy-Sainte-Réparade</h2>
            <p class="cta-banner__text">Contactez-moi pour un accompagnement personnalisé et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="psr-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels sont les prix au m² au Puy-Sainte-Réparade ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Les prix oscillent entre 2 700 €/m² et 3 600 €/m² selon le type de bien et son emplacement. Les villas avec piscine et vue dégagée atteignent les valeurs les plus élevées.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Le village dispose-t-il de commodités ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Le Puy-Sainte-Réparade dispose d\'écoles, de commerces de proximité, de médecins et d\'un marché hebdomadaire. Bordeaux est accessible en 20 minutes par la D14.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
