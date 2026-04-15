<?php
declare(strict_types=1);


$siteSettings = $siteSettings ?? [];

$advisorName = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Votre conseiller immobilier'));
$advisorTitle = $advisorTitle ?? ($siteSettings['advisor_title'] ?? ($_ENV['ADVISOR_TITLE'] ?? 'Conseiller immobilier local'));
$advisorPhone = $advisorPhone ?? ($siteSettings['phone'] ?? ($_ENV['APP_PHONE'] ?? ''));
$advisorEmail = $advisorEmail ?? ($siteSettings['email'] ?? ($_ENV['APP_EMAIL'] ?? ''));
$advisorCity = $advisorCity ?? ($siteSettings['city'] ?? ($_ENV['APP_CITY'] ?? 'Votre ville'));
$siteName = $siteSettings['site_name'] ?? ($_ENV['APP_NAME'] ?? $advisorName);
$territoryName = $siteSettings['territory_name'] ?? ($siteSettings['territory'] ?? $advisorCity);
$brandNetwork = $siteSettings['brand_network'] ?? 'réseau immobilier';
$heroBg = $siteSettings['home_hero_bg'] ?? '/assets/images/hero-bg.jpg';
$advisorPhoto = $siteSettings['advisor_photo'] ?? '/assets/images/placeholder.php';

$pageTitle = $siteSettings['home_meta_title'] ?? "Immobilier {$advisorCity} — {$advisorName} | Vente, Achat, Estimation";
$metaDesc = $siteSettings['home_meta_description'] ?? "{$advisorTitle} à {$advisorCity} : vente, achat, estimation immobilière et accompagnement local.";
$metaKeywords = $siteSettings['home_meta_keywords'] ?? "immobilier {$advisorCity}, estimation immobilière {$advisorCity}, vente immobilière {$advisorCity}, achat immobilier {$advisorCity}";
$extraCss = ['/assets/css/home.css', '/assets/css/mere.css'];

$heroLabel = $siteSettings['home_hero_label'] ?? "Immobilier {$advisorCity} · {$territoryName}";
$heroTitle = $siteSettings['home_hero_title'] ?? "Vendre, acheter et estimer sereinement à {$advisorCity}, avec un conseiller local unique.";
$heroSubtitle = $siteSettings['home_hero_subtitle'] ?? "{$advisorName} vous accompagne de la stratégie jusqu'à la signature : estimation immobilière, vente et recherche ciblée d'opportunités à {$territoryName}.";
$heroPrimaryLabel = $siteSettings['home_hero_primary_label'] ?? 'Demander une estimation gratuite';
$heroPrimaryUrl = $siteSettings['home_hero_primary_url'] ?? '/estimation-gratuite';
$heroSecondaryLabel = $siteSettings['home_hero_secondary_label'] ?? 'Voir les biens à vendre';
$heroSecondaryUrl = $siteSettings['home_hero_secondary_url'] ?? '/biens';

$heroPillars = $siteSettings['home_hero_pillars'] ?? ['Vente', 'Achat', 'Estimation', 'Accompagnement 360°'];
if (is_string($heroPillars)) {
    $decoded = json_decode($heroPillars, true);
    $heroPillars = is_array($decoded) ? $decoded : array_map('trim', explode(',', $heroPillars));
}

$stats = $siteSettings['home_stats'] ?? [
    ['value' => '4,9/5', 'label' => 'Avis clients'],
    ['value' => $territoryName, 'label' => 'Expertise terrain locale'],
    ['value' => '24h', 'label' => 'Délai de réponse'],
    ['value' => '360°', 'label' => 'Accompagnement complet'],
];
if (is_string($stats)) {
    $decoded = json_decode($stats, true);
    $stats = is_array($decoded) ? $decoded : [];
}

$prospectReality = $siteSettings['home_reality_cards'] ?? [
    [
        'title' => 'Vendre au bon prix',
        'text' => "Vous voulez une estimation fiable, pas un prix vitrine. L'objectif : vendre dans les bonnes conditions et dans un délai cohérent avec votre projet.",
    ],
    [
        'title' => 'Trouver les bonnes opportunités',
        'text' => "Le marché bouge vite. Vous avez besoin d'un tri pertinent et d'un accès aux opportunités locales qui correspondent vraiment à votre projet.",
    ],
    [
        'title' => 'Réduire la charge mentale',
        'text' => "Visites, négociation, compromis, suivi notaire : vous ne voulez pas gérer seul chaque détail ni prendre de risques inutiles.",
    ],
];
if (is_string($prospectReality)) {
    $decoded = json_decode($prospectReality, true);
    $prospectReality = is_array($decoded) ? $decoded : [];
}

$comparison = $siteSettings['home_comparison'] ?? [
    'with' => [
        'tag' => 'Avec accompagnement',
        'title' => 'Un projet structuré, sécurisé, sans mauvaise surprise.',
        'items' => [
            'Estimation fondée sur les données réelles du marché local',
            'Stratégie de commercialisation ou de recherche sur mesure',
            'Négociation cadrée et argumentée',
            'Un interlocuteur unique du premier échange jusqu’à la signature',
        ],
    ],
    'without' => [
        'tag' => 'Sans accompagnement',
        'title' => 'Des risques réels sur une décision à fort impact.',
        'items' => [
            'Surestimation ou sous-estimation fréquente',
            'Visibilité limitée et tri peu efficace',
            'Négociation souvent subie',
            'Charge administrative portée seul',
        ],
    ],
];
if (is_string($comparison)) {
    $decoded = json_decode($comparison, true);
    $comparison = is_array($decoded) ? $decoded : [];
}

$aboutTitle = $siteSettings['home_about_title'] ?? "{$advisorName} — conseiller immobilier indépendant, {$territoryName}.";
$aboutText = $siteSettings['home_about_text'] ?? "Interlocuteur unique, {$advisorName} accompagne les projets d'achat, de vente et d'estimation immobilière à {$advisorCity} avec une approche humaine, structurée et rigoureuse.";
$aboutBenefits = $siteSettings['home_about_benefits'] ?? [
    "Expert local : {$territoryName}.",
    'Accompagnement 360° : stratégie, commercialisation, négociation, sécurisation.',
    'Suivi personnalisé du premier échange jusqu’à la signature.',
];
if (is_string($aboutBenefits)) {
    $decoded = json_decode($aboutBenefits, true);
    $aboutBenefits = is_array($decoded) ? $decoded : array_map('trim', explode(',', $aboutBenefits));
}

$steps = $siteSettings['home_steps'] ?? [
    ['num' => '01', 'title' => 'Comprendre votre projet', 'text' => 'Objectifs, contraintes, timing et contexte du projet.'],
    ['num' => '02', 'title' => 'Définir la stratégie', 'text' => 'Positionnement, plan de commercialisation ou cahier de recherche.'],
    ['num' => '03', 'title' => 'Valoriser ou cibler', 'text' => 'Mise en valeur du bien ou sélection affinée des opportunités.'],
    ['num' => '04', 'title' => 'Négocier et sécuriser', 'text' => 'Négociation argumentée, vérifications et cadre sécurisé.'],
    ['num' => '05', 'title' => 'Accompagner jusqu’à la signature', 'text' => 'Suivi notarial et coordination jusqu’à l’acte authentique.'],
];
if (is_string($steps)) {
    $decoded = json_decode($steps, true);
    $steps = is_array($decoded) ? $decoded : [];
}

$testimonials = $siteSettings['home_testimonials'] ?? [
    ['stars' => '★★★★★', 'text' => 'Accompagnement clair du début à la fin. Notre projet a été structuré, fluide et sécurisé.', 'author' => 'Client vendeur'],
    ['stars' => '★★★★★', 'text' => 'Une vraie stratégie et un tri efficace. On a gagné beaucoup de temps.', 'author' => 'Client acquéreur'],
    ['stars' => '★★★★★', 'text' => 'Communication excellente et disponibilité réelle à chaque étape.', 'author' => 'Client estimation'],
];
if (is_string($testimonials)) {
    $decoded = json_decode($testimonials, true);
    $testimonials = is_array($decoded) ? $decoded : [];
}

$featuredProperties = $siteSettings['featured_properties'] ?? [
    [
        'title' => 'Bien mis en avant',
        'city' => $advisorCity,
        'price' => 'Sur demande',
        'surface' => '',
        'rooms' => '',
        'badge' => 'Sélection',
        'image' => '/assets/images/placeholder.php',
    ],
];
if (is_string($featuredProperties)) {
    $decoded = json_decode($featuredProperties, true);
    $featuredProperties = is_array($decoded) ? $decoded : [];
}

$marketCards = $siteSettings['home_market_cards'] ?? [
    [
        'title' => 'Un marché porteur et exigeant',
        'text' => "Le marché de {$territoryName} demande un positionnement juste et une bonne lecture de la demande locale.",
    ],
    [
        'title' => 'Des acheteurs bien informés',
        'text' => "Les acquéreurs comparent, négocient et arbitrent vite. Une estimation réaliste est indispensable.",
    ],
    [
        'title' => "{$advisorCity} et ses environs",
        'text' => "Chaque secteur a ses propres réalités de marché. Une connaissance fine du territoire fait la différence.",
    ],
];
if (is_string($marketCards)) {
    $decoded = json_decode($marketCards, true);
    $marketCards = is_array($decoded) ? $decoded : [];
}

$sellGuide = $siteSettings['home_sell_guide'] ?? [
    [
        'title' => '1. Estimer juste, pas haut',
        'text' => 'Un prix surestimé génère peu de visites et affaiblit la crédibilité de la mise en vente.',
    ],
    [
        'title' => '2. Préparer les documents en amont',
        'text' => 'Anticiper les démarches évite des délais inutiles et rassure les acquéreurs.',
    ],
    [
        'title' => '3. Valoriser et diffuser intelligemment',
        'text' => 'Photos, argumentaire et diffusion ciblée permettent d’attirer des contacts qualifiés.',
    ],
    [
        'title' => '4. Négocier et sécuriser la transaction',
        'text' => 'Une négociation menée avec méthode protège votre prix et la solidité de la vente.',
    ],
];
if (is_string($sellGuide)) {
    $decoded = json_decode($sellGuide, true);
    $sellGuide = is_array($decoded) ? $decoded : [];
}

$faqItems = $siteSettings['home_faq'] ?? [
    [
        'question' => "Combien vaut mon bien immobilier à {$advisorCity} ?",
        'answer' => "Le prix dépend du secteur, de l’état du bien, de ses caractéristiques et des références réelles du marché local. Un avis de valeur sérieux demande une vraie analyse.",
    ],
    [
        'question' => 'Quelle est la différence entre un conseiller indépendant et une agence ?',
        'answer' => 'Le niveau d’implication, le suivi, la disponibilité et la personnalisation de la stratégie changent souvent radicalement.',
    ],
    [
        'question' => 'Quels diagnostics sont nécessaires pour vendre ?',
        'answer' => 'Cela dépend du bien et de sa date de construction. Un accompagnement permet de ne rien oublier.',
    ],
    [
        'question' => "Combien de temps faut-il pour vendre à {$advisorCity} ?",
        'answer' => 'Tout dépend du prix, du bien, du secteur et de la qualité de préparation du dossier de vente.',
    ],
];
if (is_string($faqItems)) {
    $decoded = json_decode($faqItems, true);
    $faqItems = is_array($decoded) ? $decoded : [];
}

$finalCtaTitle = $siteSettings['home_final_cta_title'] ?? "Parlons de votre projet immobilier à {$advisorCity}.";
$finalCtaText = $siteSettings['home_final_cta_text'] ?? 'Choisissez votre prochain pas selon où vous en êtes.';
?>

<section class="hero hero--premium" aria-labelledby="home-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url('<?= e($heroBg) ?>');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label"><?= e($heroLabel) ?></span>
            <h1 id="home-hero-title"><?= e($heroTitle) ?></h1>
            <p class="hero__subtitle"><?= e($heroSubtitle) ?></p>

            <div class="hero__actions">
                <a href="<?= e($heroPrimaryUrl) ?>" class="btn btn--accent btn--lg"><?= e($heroPrimaryLabel) ?></a>
                <a href="<?= e($heroSecondaryUrl) ?>" class="btn btn--outline-white btn--lg"><?= e($heroSecondaryLabel) ?></a>
            </div>

            <?php if ($heroPillars !== []): ?>
                <div class="hero__pillars" role="list" aria-label="Domaines d'expertise">
                    <?php foreach ($heroPillars as $pillar): ?>
                        <span role="listitem"><?= e((string) $pillar) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="stat-strip" role="region" aria-label="Chiffres clés">
    <div class="container">
        <div class="stat-strip__inner">
            <?php foreach ($stats as $item): ?>
                <div class="stat-item">
                    <span class="stat-item__value"><?= e($item['value'] ?? '') ?></span>
                    <span class="stat-item__label"><?= e($item['label'] ?? '') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<section class="section section--alt" id="realite-prospect">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Votre réalité immobilière</span>
            <h2 class="section-title">Vous avez un projet sérieux.<br>Vous méritez un accompagnement à la hauteur.</h2>
            <p class="section-subtitle">Vendre au bon prix, acheter au bon moment et éviter les erreurs inutiles : ce sont les vraies préoccupations d’un projet immobilier.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($prospectReality as $card): ?>
                <article class="card" data-animate>
                    <div class="card__body">
                        <h3 class="card__title"><?= e($card['title'] ?? '') ?></h3>
                        <p class="card__text"><?= e($card['text'] ?? '') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="distinction">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Ce qui change vraiment</span>
            <h2 class="section-title">Avec ou sans un conseiller indépendant : la différence concrète.</h2>
            <p class="section-subtitle">Pas une question de discours. Une question de résultat, de sécurité et de tranquillité d’esprit.</p>
        </div>

        <div class="grid-2 insight-grid">
            <article class="insight-card insight-card--gain" data-animate>
                <span class="insight-card__tag"><?= e($comparison['with']['tag'] ?? 'Avec accompagnement') ?></span>
                <h3><?= e($comparison['with']['title'] ?? '') ?></h3>
                <ul>
                    <?php foreach (($comparison['with']['items'] ?? []) as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <article class="insight-card insight-card--risk" data-animate>
                <span class="insight-card__tag"><?= e($comparison['without']['tag'] ?? 'Sans accompagnement') ?></span>
                <h3><?= e($comparison['without']['title'] ?? '') ?></h3>
                <ul>
                    <?php foreach (($comparison['without']['items'] ?? []) as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container grid-2 about-split">
        <div data-animate>
            <span class="section-label">Votre conseiller</span>
            <h2 class="section-title"><?= e($aboutTitle) ?></h2>
            <p><?= e($aboutText) ?></p>

            <ul class="benefits-list">
                <?php foreach ($aboutBenefits as $item): ?>
                    <li><?= e((string) $item) ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="/a-propos" class="btn btn--outline">Découvrir son parcours</a>
        </div>

        <figure class="about-photo">
            <img src="<?= e($advisorPhoto) ?>" alt="<?= e($advisorName . ', conseiller immobilier à ' . $advisorCity) ?>" loading="lazy">
        </figure>
    </div>
</section>

<section class="section" id="methode">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">La méthode <?= e($advisorName) ?></span>
            <h2 class="section-title">Une méthode claire en 5 étapes pour sécuriser votre projet.</h2>
            <p class="section-subtitle">Chaque étape a une fonction précise. Rien n’est improvisé.</p>
        </div>

        <div class="grid-5-steps">
            <?php foreach ($steps as $step): ?>
                <article class="step-card" data-animate>
                    <span class="step-card__num"><?= e($step['num'] ?? '') ?></span>
                    <h3><?= e($step['title'] ?? '') ?></h3>
                    <p><?= e($step['text'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/contact" class="btn btn--primary">Réserver un rendez-vous</a>
            <a href="/secteurs" class="btn btn--outline">Consulter les secteurs</a>
        </div>
    </div>
</section>

<section class="section section--alt" id="preuves-sociales">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Ils l'ont fait</span>
            <h2 class="section-title">Des résultats concrets, des avis authentiques.</h2>
        </div>

        <div class="grid-3">
            <?php foreach ($testimonials as $item): ?>
                <article class="testimonial" data-animate>
                    <div class="testimonial__stars"><?= e($item['stars'] ?? '★★★★★') ?></div>
                    <p class="testimonial__text"><?= e($item['text'] ?? '') ?></p>
                    <p class="testimonial__author"><?= e($item['author'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/avis-clients" class="btn btn--outline">Voir tous les avis clients</a>
        </div>
    </div>
</section>

<section class="section" id="biens-en-vente">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Biens sélectionnés</span>
            <h2 class="section-title">Des opportunités à <?= e($territoryName) ?>.</h2>
            <p class="section-subtitle">Chaque bien est présenté avec ses informations clés pour vous permettre une décision rapide et éclairée.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($featuredProperties as $property): ?>
                <article class="card property-card-premium">
                    <img class="card__img" src="<?= e($property['image'] ?? '/assets/images/placeholder.php') ?>" alt="<?= e(($property['title'] ?? 'Bien') . ' à ' . ($property['city'] ?? $advisorCity)) ?>" loading="lazy">
                    <div class="card__body">
                        <span class="property-badge"><?= e($property['badge'] ?? 'Sélection') ?></span>
                        <h3 class="card__title"><?= e($property['title'] ?? '') ?></h3>
                        <p class="card__text property-meta"><?= e($property['city'] ?? '') ?><?= !empty($property['surface']) ? ' · ' . e($property['surface']) : '' ?><?= !empty($property['rooms']) ? ' · ' . e($property['rooms']) : '' ?></p>
                        <p class="property-price"><?= e($property['price'] ?? '') ?></p>
                        <a href="/biens" class="btn btn--primary btn--sm">Voir le bien</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/biens" class="btn btn--outline btn--lg">Voir tous les biens disponibles</a>
        </div>
    </div>
</section>

<section class="section section--alt" id="marche-immobilier-local">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Le marché local</span>
            <h2 class="section-title">Immobilier à <?= e($advisorCity) ?> : comprendre le marché</h2>
            <p class="section-subtitle">Le marché immobilier local demande de la lecture, du timing et une bonne compréhension des attentes acheteurs.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($marketCards as $card): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><?= e($card['title'] ?? '') ?></h3>
                        <p class="card__text"><?= e($card['text'] ?? '') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/estimation-gratuite" class="btn btn--primary">Obtenir une estimation de mon bien</a>
        </div>
    </div>
</section>

<section class="section" id="comment-vendre-bien-immobilier">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Vendre sereinement</span>
            <h2 class="section-title">Comment vendre un bien immobilier à <?= e($advisorCity) ?></h2>
            <p class="section-subtitle">Une vente réussie ne s’improvise pas. Chaque étape demande méthode, disponibilité et clarté.</p>
        </div>

        <div class="grid-2 sell-guide">
            <?php foreach ($sellGuide as $item): ?>
                <div class="sell-guide__item">
                    <h3 class="sell-guide__step"><?= e($item['title'] ?? '') ?></h3>
                    <p><?= e($item['text'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/avis-de-valeur" class="btn btn--outline">Demander un avis de valeur gratuit</a>
        </div>
    </div>
</section>

<section class="section section--alt" id="faq-immobilier-local">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Questions fréquentes</span>
            <h2 class="section-title">FAQ immobilier à <?= e($advisorCity) ?></h2>
            <p class="section-subtitle">Les questions que posent le plus souvent les vendeurs et acheteurs.</p>
        </div>

        <div class="faq">
            <?php foreach ($faqItems as $item): ?>
                <div class="faq__item">
                    <h3 class="faq__question"><?= e($item['question'] ?? '') ?></h3>
                    <p class="faq__answer"><?= e($item['answer'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner" id="cta-final">
    <div class="container">
        <h2><?= e($finalCtaTitle) ?></h2>
        <p><?= e($finalCtaText) ?></p>

        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Demander une estimation gratuite</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Prendre contact</a>
            <?php if (!empty($advisorPhone)): ?>
                <a href="tel:<?= e(preg_replace('/\s+/', '', (string) $advisorPhone)) ?>" class="btn btn--outline-white btn--lg">Appeler <?= e($advisorName) ?></a>
            <?php endif; ?>
        </div>

        <div class="cta-banner__actions cta-banner__actions--secondary">
            <a href="/biens" class="btn btn--outline-white">Voir les biens</a>
            <a href="/secteurs" class="btn btn--outline-white">Consulter les secteurs</a>
            <a href="/avis-clients" class="btn btn--outline-white">Avis clients</a>
        </div>
    </div>
</section>