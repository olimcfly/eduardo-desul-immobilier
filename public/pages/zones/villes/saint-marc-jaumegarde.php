<?php
$pageTitle    = 'Immobilier Saint-Marc-Jaumegarde - Village exclusif | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier à Saint-Marc-Jaumegarde — village résidentiel d\'exception au pied de la Sainte-Victoire. Pascal Hamm vous accompagne pour vos transactions de prestige.';
$metaKeywords = 'immobilier Saint-Marc-Jaumegarde, expert immobilier Saint-Marc, maison prestige Sainte-Victoire, estimation immobilière Saint-Marc-Jaumegarde';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="smj-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/saint-marc-jaumegarde-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Saint-Marc-Jaumegarde</span>
            <h1 id="smj-hero-title">L\'immobilier d\'exception à Saint-Marc-Jaumegarde</h1>
            <p class="hero__subtitle">Petit village résidentiel exclusif niché dans les garrigues au pied de la Montagne Sainte-Victoire, Saint-Marc-Jaumegarde est l\'une des adresses les plus prisées du Pays d\'Aix.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="smj-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Saint-Marc-Jaumegarde</span>
            <h2 class="section-title">Une adresse d\'exception</h2>
            <p class="section-subtitle">Avec seulement 1 200 habitants et une offre immobilière très limitée, Saint-Marc-Jaumegarde est l\'un des villages les plus exclusifs du Pays d\'Aix. Ses propriétés bénéficient d\'un cadre naturel exceptionnel et d\'une proximité immédiate avec Aix-en-Provence.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path></svg></div>
                <h3 class="card__title">Marché très confidentiel</h3>
                <p class="card__text">Peu de transactions, biens rares, acquéreurs triés sur le volet — un marché qui requiert un expert reconnu.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Vues exceptionnelles</h3>
                <p class="card__text">Panoramas sur la Sainte-Victoire, les garrigues et la plaine d\'Aix — un cadre de vie unique en Provence.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="smj-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Saint-Marc-Jaumegarde</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">5 200 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+4.8%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">38 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="smj-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Un service sur mesure pour ce marché exclusif</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation de prestige</h3>
                <p class="card__text">Évaluation précise tenant compte des spécificités de ce marché confidentiel.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente confidentielle</h3>
                <p class="card__text">Commercialisation discrète auprès d\'une clientèle exigeante et qualifiée.</p>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Recherche sur mesure</h3>
                <p class="card__text">Accès aux biens off-market grâce à notre réseau local exclusif.</p>
                <a href="/contact" class="btn btn--outline">Déposer une recherche</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="smj-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Un projet immobilier à Saint-Marc-Jaumegarde ?</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation personnalisée et confidentielle.</p>
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
