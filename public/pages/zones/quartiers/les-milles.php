<?php
$pageTitle    = 'Immobilier Les Milles Bordeaux - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier aux Milles, secteur résidentiel et d\'activités au sud d\'Bordeaux. Appartements, maisons et locaux professionnels à proximité du technopôle.';
$metaKeywords = 'immobilier Les Milles Bordeaux, appartement Les Milles, maison Les Milles, expert immobilier sud Bordeaux, secteur Milles';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="les-milles-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/les-milles-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Les Milles — Bordeaux</span>
            <h1 id="les-milles-hero-title">L\'immobilier aux Milles</h1>
            <p class="hero__subtitle">Fiche secteur à valider localement pour Bordeaux Métropole : périmètre, transports, prix et typologie de biens doivent être confirmés avant publication.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="les-milles-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Les Milles</span>
            <h2 class="section-title">Un secteur en plein développement</h2>
            <p class="section-subtitle">Si ce secteur cible une zone d’activités ou un quartier bordelais portant un nom proche, remplacez ce texte par des données locales : transports, prix, offre de biens et repères de quartier.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Localisation stratégique</h3>
                <p class="card__text">Vérifiez les accès réels (rocade, tram, bus) depuis votre adresse cible sur la métropole bordelaise.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Prix accessibles</h3>
                <p class="card__text">Des prix encore en dessous de la moyenne bordelaise, offrant de belles opportunités d\'investissement.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="les-milles-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier aux Milles</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 100 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+2.5%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">58 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="les-milles-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers aux Milles</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation de votre appartement, maison ou local professionnel aux Milles.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Commercialisation de votre bien résidentiel ou professionnel.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Investissement locatif</h3>
                <p class="card__text">Identification des meilleures opportunités d\'investissement dans ce secteur en développement.</p>
                <a href="/contact" class="btn btn--outline">En savoir plus</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="les-milles-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier aux Milles</h2>
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
