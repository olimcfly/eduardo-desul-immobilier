<?php
$pageTitle    = 'Immobilier Le Tholonet - Village de prestige près d\'Aix | ' . ADVISOR_NAME;
$metaDesc     = 'Achetez ou vendez un bien d\'exception au Tholonet avec ' . ADVISOR_NAME . ', expert immobilier indépendant. Village prisé au pied de la Montagne Sainte-Victoire.';
$metaKeywords = 'immobilier Le Tholonet, expert immobilier Tholonet, maison de prestige Tholonet, estimation immobilière Tholonet, Sainte-Victoire immobilier';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="tholonet-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/le-tholonet-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Le Tholonet</span>
            <h1 id="tholonet-hero-title">L\'immobilier de prestige au Tholonet</h1>
            <p class="hero__subtitle">Au pied de la Montagne Sainte-Victoire, Le Tholonet est l\'un des villages les plus recherchés du Pays d\'Aix. Expert en biens d\'exception, je vous accompagne dans chaque étape de votre projet.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="tholonet-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Le Tholonet</span>
            <h2 class="section-title">Un village d\'exception</h2>
            <p class="section-subtitle">Le Tholonet est un village d\'environ 2 000 habitants situé à 8 km à l\'est d\'Aix-en-Provence. Immortalisé par Cézanne, il offre des paysages époustouflants dominés par la Sainte-Victoire et un marché immobilier parmi les plus exclusifs du Pays d\'Aix.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg></div>
                <h3 class="card__title">Biens d\'exception</h3>
                <p class="card__text">Bastides, villas architecturales, domaines viticoles — Le Tholonet concentre les plus belles propriétés du Pays d\'Aix.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Discrétion et expertise</h3>
                <p class="card__text">Transactions off-market, réseau d\'acquéreurs qualifiés — une approche adaptée au marché du prestige.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="tholonet-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier au Tholonet</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 800 €</div>
                <div class="stat-card__label">Prix moyen au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+5.1%</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">35 jours</div>
                <div class="stat-card__label">Temps de vente moyen</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="tholonet-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Un service sur mesure pour le prestige</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation de prestige</h3>
                <p class="card__text">Estimation précise pour des biens d\'exception tenant compte des spécificités du marché local.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente confidentielle</h3>
                <p class="card__text">Commercialisation discrète et ciblée auprès d\'une clientèle exigeante.</p>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Recherche sur mesure</h3>
                <p class="card__text">Sélection de propriétés selon vos critères précis, y compris les biens off-market.</p>
                <a href="/contact" class="btn btn--outline">Déposer une recherche</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="tholonet-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Un projet immobilier au Tholonet ?</h2>
            <p class="cta-banner__text">Contactez-moi pour une consultation personnalisée et confidentielle.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contactez-moi</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="tholonet-faq">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes sur l\'immobilier au Tholonet</h2>
        </div>
        <div class="accordion" data-animate>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Quels types de biens trouve-t-on au Tholonet ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>Le Tholonet est principalement composé de maisons individuelles, bastides, villas architecturales et domaines. Les appartements sont rares. La majorité des biens disposent de grands terrains et de piscines.</p></div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button"><span class="accordion__title">Pourquoi les prix sont-ils si élevés au Tholonet ?</span><svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
                <div class="accordion__content"><p>La rareté des biens disponibles, la proximité immédiate d\'Aix-en-Provence, la qualité des paysages (Montagne Sainte-Victoire, forêt) et l\'environnement préservé expliquent les niveaux de prix élevés du Tholonet.</p></div>
            </div>
        </div>
    </div>
</section>
';
?>

<?php include(__DIR__ . '/../../../templates/page.php'); ?>
