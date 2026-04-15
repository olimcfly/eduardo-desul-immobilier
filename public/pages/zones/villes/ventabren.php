<?php
$pageTitle    = 'Immobilier Ventabren - Village avec vue panoramique | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez, vendez ou estimez votre bien à Ventabren avec Pascal Hamm. Village provençal offrant des vues exceptionnelles sur l\'Étang de Berre et la Sainte-Victoire.';
$metaKeywords = 'immobilier Ventabren, expert immobilier Ventabren, estimation immobilière Ventabren, maison vue panoramique Ventabren, achat vente Ventabren';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="ventabren-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/ventabren-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Ventabren</span>
            <h1 id="ventabren-hero-title">Vendre, acheter et estimer sereinement à Ventabren</h1>
            <p class="hero__subtitle">Village perché offrant des panoramas à 360° sur la Sainte-Victoire et l\'Étang de Berre, Ventabren est une adresse très prisée pour ses vues exceptionnelles et son ambiance provençale authentique.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="ventabren-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Ventabren</span>
            <h2 class="section-title">Le village aux panoramas exceptionnels</h2>
            <p class="section-subtitle">Ventabren est un village provençal d\'environ 5 500 habitants, perché à 250 mètres d\'altitude à 20 km à l\'ouest d\'Aix-en-Provence. Ses ruines médiévales, ses ruelles pittoresques et ses vues panoramiques en font l\'un des villages les plus pittoresques du Pays d\'Aix.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 6l10 4 10-4-10-4-10 4z"></path><path d="M1 12l10 4 10-4"></path><path d="M1 18l10 4 10-4"></path></svg></div>
                <h3 class="card__title">Vues panoramiques</h3>
                <p class="card__text">Les biens avec vue dégagée sur l\'Étang de Berre ou la Sainte-Victoire bénéficient d\'une forte prime sur le marché.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Village authentique</h3>
                <p class="card__text">Architecture provençale préservée, rues pavées, marché local — une qualité de vie rare et recherchée.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="ventabren-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Ventabren</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">3 600 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+3.2%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">52 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="ventabren-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Ventabren</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise tenant compte des vues et de l\'emplacement, facteurs clés à Ventabren.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente immobilière</h3>
                <p class="card__text">Mise en valeur des atouts de votre bien, notamment ses panoramas et son cachet provençal.</p>
                <a href="/contact" class="btn btn--outline">Vendre un bien</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat immobilier</h3>
                <p class="card__text">Sélection de biens correspondant à votre recherche, y compris les opportunités off-market.</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="ventabren-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à Ventabren</h2>
            <p class="cta-banner__text">Contactez-moi pour un accompagnement personnalisé et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="ventabren-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier à Ventabren</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quelle est la valeur d\'une vue panoramique à Ventabren ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Une vue dégagée peut représenter une plus-value de 15 à 25% par rapport à un bien identique sans vue. Les biens situés sur les hauteurs du village avec panorama sur l\'Étang de Berre ou la Sainte-Victoire sont les plus valorisés.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Ventabren est-elle accessible depuis Aix ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Ventabren se trouve à environ 25 minutes d\'Aix-en-Provence et 20 minutes de l\'aéroport Marseille-Provence. L\'accès à l\'A8 et à l\'A7 est facile, offrant une bonne connexion avec Marseille, Lyon et la Côte d\'Azur.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
