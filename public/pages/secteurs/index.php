<?php
$pageTitle    = 'Nos secteurs immobiliers — ' . ADVISOR_NAME;
$metaDesc     = 'Découvrez nos secteurs d\'intervention autour d\'Aix-en-Provence. Expertise locale pour Aix-en-Provence, Venelles, Bouc-Bel-Air, Eguilles et leurs alentours.';
$metaKeywords = 'secteurs immobiliers Provence, expert immobilier Aix-en-Provence, immobilier Bouc-Bel-Air, immobilier Eguilles, immobilier Simiane-Collongue';
$extraCss     = ['/assets/css/villes.css'];
?>

<section class="hero hero--light" aria-labelledby="secteurs-hero-title">
    <div class="container">
        <div class="hero__content" style="max-width:700px">
            <span class="section-label">Secteurs</span>
            <h1 id="secteurs-hero-title" style="color:var(--clr-primary)">Nos secteurs immobiliers en Provence</h1>
            <p class="hero__subtitle" style="color:var(--clr-text-muted)">Expertise locale pour une transaction immobilière sereine dans toute la région d'Aix-en-Provence et le Pays d'Aix.</p>
        </div>
    </div>
</section>

<!-- Villes -->
<section class="section" aria-labelledby="villes-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Villes</span>
            <h2 id="villes-title" class="section-title">Villes couvertes</h2>
            <p class="section-subtitle">ADVISOR_NAME . ' intervient sur l'ensemble des communes du Pays d'Aix avec une connaissance approfondie de chaque secteur.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/secteurs/villes/aix-en-provence') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Aix-en-Provence</h3>
                    <p class="city-card__desc">Zone principale d'intervention — quartiers historiques, résidentiels et périphérie.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/venelles') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Venelles</h3>
                    <p class="city-card__desc">Village résidentiel calme au nord d'Aix, très prisé des familles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/bouc-bel-air') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Bouc-Bel-Air</h3>
                    <p class="city-card__desc">Commune résidentielle au sud d'Aix, espaces verts et cadre de vie paisible.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/le-tholonet') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Le Tholonet</h3>
                    <p class="city-card__desc">Village de charme au pied de la Sainte-Victoire, propriétés de caractère.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/eguilles') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Eguilles</h3>
                    <p class="city-card__desc">Village perché à l'ouest d'Aix, vues panoramiques et mas provençaux.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/simiane-collongue') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Simiane-Collongue</h3>
                    <p class="city-card__desc">Commune verdoyante entre Aix et Marseille, cadre naturel préservé.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/gardanne') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Gardanne</h3>
                    <p class="city-card__desc">Ville dynamique entre Aix et Marseille, prix attractifs et bonne desserte.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/lambesc') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Lambesc</h3>
                    <p class="city-card__desc">Bourg provençal typique à l'ouest d'Aix, bastides et maisons de village.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/meyreuil') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Meyreuil</h3>
                    <p class="city-card__desc">Village résidentiel à l'est d'Aix, calme et proche du bassin minier.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/rognes') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Rognes</h3>
                    <p class="city-card__desc">Village provençal au nord-ouest d'Aix, propriétés rurales et vignobles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/saint-cannat') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Saint-Cannat</h3>
                    <p class="city-card__desc">Commune entre Aix et Salon-de-Provence, maisons provençales abordables.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/villes/ventabren') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Ventabren</h3>
                    <p class="city-card__desc">Village médiéval surplombant l'Arc, vues d'exception et biens de caractère.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Quartiers d'Aix -->
<section class="section section--alt" aria-labelledby="quartiers-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Quartiers d'Aix-en-Provence</span>
            <h2 id="quartiers-title" class="section-title">Quartiers couverts</h2>
            <p class="section-subtitle">Une connaissance fine de chaque quartier aixois pour vous orienter vers le bien qui correspond à votre projet.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/secteurs/quartiers/mazarin') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Mazarin</h3>
                    <p class="city-card__desc">Quartier historique haussmannien, hôtels particuliers et immeubles de prestige.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/quartiers/centre-ville') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Centre-ville</h3>
                    <p class="city-card__desc">Le cœur d'Aix — Cours Mirabeau, vieux-Aix, commerces et vie culturelle.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/quartiers/puyricard') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Puyricard</h3>
                    <p class="city-card__desc">Quartier résidentiel premium au nord — villas, piscines et grand calme.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/quartiers/jas-de-bouffan') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Jas de Bouffan</h3>
                    <p class="city-card__desc">Quartier résidentiel à l'ouest, maisons individuelles et résidences récentes.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/quartiers/luynes') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Luynes</h3>
                    <p class="city-card__desc">Village intégré au sud-ouest d'Aix, pavillons et copropriétés récentes.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/secteurs/quartiers/les-milles') ?>" class="city-card">
                <div class="city-card__body">
                    <h3 class="city-card__name">Les Milles</h3>
                    <p class="city-card__desc">Secteur économique et résidentiel au sud, technopôle et résidences modernes.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Pourquoi un expert local -->
<section class="section" aria-labelledby="expertise-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Notre expertise</span>
            <h2 id="expertise-title" class="section-title">Pourquoi choisir un expert local ?</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Connaissance précise</h3>
                <p class="card__text">Chaque secteur a ses particularités de prix, de demande et de types de biens. Notre expertise hyperlocale vous garantit une évaluation juste.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Réseau local solide</h3>
                <p class="card__text">Notaires, courtiers, artisans — notre réseau de partenaires locaux facilite chaque étape de votre transaction immobilière.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Accès off-market</h3>
                <p class="card__text">Des biens non publiés sur les portails, disponibles en exclusivité grâce à notre ancrage local et notre portefeuille de vendeurs.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Vous avez un projet dans l'un de ces secteurs ?</h2>
            <p class="cta-banner__text">Contactez Pascal pour une expertise locale et des conseils personnalisés.</p>
            <div class="cta-banner__actions">
                <a href="<?= url('/estimation-gratuite') ?>" class="btn btn--accent">Estimation gratuite</a>
                <a href="<?= url('/contact') ?>" class="btn btn--outline-white">Nous contacter</a>
            </div>
        </div>
    </div>
</section>
