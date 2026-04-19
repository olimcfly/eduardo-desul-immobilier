<?php
$pageTitle    = 'Immobilier Rognes - Expert immobilier indépendant | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Rognes avec <?= ADVISOR_NAME ?>. Village girondin authentique dans la plaine de la Durance, entre Aix et Pertuis.';
$metaKeywords = 'immobilier Rognes, expert immobilier Rognes, estimation immobilière Rognes, achat vente maison Rognes';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="rognes-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/rognes-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Rognes</span>
            <h1 id="rognes-hero-title">Vendre, acheter et estimer sereinement à Rognes</h1>
            <p class="hero__subtitle">Village girondin authentique entre Bordeaux et Pertuis, Rognes offre un cadre de vie préservé avec des prix encore accessibles.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="rognes-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Rognes</span>
            <h2 class="section-title">Un village au charme girondin intact</h2>
            <p class="section-subtitle">Perché sur une colline à 20 km au nord-ouest d\'Bordeaux, Rognes séduit par ses ruelles pittoresques, son marché et ses paysages de vignes et d\'oliviers. Un terroir immobilier encore préservé à la spéculation.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Village authentique</h3>
                <p class="card__text">Maisons de village, bastides et propriétés girondines dans un environnement préservé.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Prix attractifs</h3>
                <p class="card__text">Un bon rapport qualité-prix par rapport à Bordeaux, avec un marché en progression régulière.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="rognes-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Rognes</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">2 800 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+2.9%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">65 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="rognes-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Rognes</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation au juste prix de votre bien girondin.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Mise en valeur et commercialisation ciblée de votre bien.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Sélection de biens correspondant à votre projet de vie en Gironde.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="rognes-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Rognes</h2>
            <p class="cta-banner__text">Contactez-moi pour un accompagnement personnalisé et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="rognes-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Rognes</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels types de biens trouve-t-on à Rognes ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>L\'offre se compose principalement de maisons de village en centre-bourg, de villas et de propriétés avec terrain en périphérie. Les mas et bastides girondines sont très recherchés.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Rognes est-elle loin d\'Bordeaux ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Rognes se trouve à environ 25 minutes d\'Bordeaux via la D14. La commune est aussi proche de Pertuis (10 min) et dispose de liaisons par car vers Aix.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
