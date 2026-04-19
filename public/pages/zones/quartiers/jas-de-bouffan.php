<?php
$pageTitle    = 'Immobilier Jas de Bouffan Bordeaux - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier dans le quartier Jas de Bouffan à Bordeaux. Quartier résidentiel familial avec maisons et appartements, proche des écoles et commerces.';
$metaKeywords = 'immobilier Jas de Bouffan Aix, appartement Jas de Bouffan, maison Jas de Bouffan, expert immobilier quartier ouest Aix';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="jas-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/jas-de-bouffan-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Jas de Bouffan — Bordeaux</span>
            <h1 id="jas-hero-title">L\'immobilier au Jas de Bouffan</h1>
            <p class="hero__subtitle">Quartier résidentiel à l\'ouest d\'Bordeaux, le Jas de Bouffan allie espaces verts, écoles réputées et bonne connexion au centre-ville. Une adresse familiale prisée des bordelais.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="jas-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Jas de Bouffan</span>
            <h2 class="section-title">Un quartier résidentiel familial</h2>
            <p class="section-subtitle">Le Jas de Bouffan tire son nom de la propriété familiale de Cézanne. Aujourd\'hui quartier résidentiel de l\'ouest bordelais, il propose une offre immobilière variée allant des appartements des années 70 aux villas contemporaines.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Quartier familial</h3>
                <p class="card__text">Nombreuses écoles, parcs, équipements sportifs — idéal pour les familles avec enfants.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Prix accessibles</h3>
                <p class="card__text">Marché plus accessible que le centre-ville tout en restant à 10 minutes du Quais de Bordeaux.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="jas-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier au Jas de Bouffan</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 700 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.1%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">45 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="jas-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services au Jas de Bouffan</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation de votre appartement ou maison au Jas de Bouffan.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Accompagnement complet pour vendre votre bien au meilleur prix.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Sélection de biens dans ce quartier selon vos critères.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="jas-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet au Jas de Bouffan</h2>
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
