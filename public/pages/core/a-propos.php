<?php
declare(strict_types=1);

$advisorName = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Votre conseiller immobilier'));
$advisorTitle = $advisorTitle ?? ($siteSettings['advisor_title'] ?? ($_ENV['ADVISOR_TITLE'] ?? 'Conseiller immobilier local'));
$advisorPhone = $advisorPhone ?? ($siteSettings['phone'] ?? ($_ENV['APP_PHONE'] ?? ''));
$advisorPhoneDisplay = $advisorPhoneDisplay ?? $advisorPhone;
$advisorEmail = $advisorEmail ?? ($siteSettings['email'] ?? ($_ENV['APP_EMAIL'] ?? ''));
$advisorCity = $advisorCity ?? ($siteSettings['city'] ?? ($_ENV['APP_CITY'] ?? 'Votre ville'));
$advisorImage = $advisorImage ?? ($siteSettings['advisor_image'] ?? '/assets/images/placeholder.php');
$advisorSecondaryImage = $advisorSecondaryImage ?? ($siteSettings['advisor_secondary_image'] ?? $advisorImage);
$advisorTerritoryImage = $advisorTerritoryImage ?? ($siteSettings['advisor_territory_image'] ?? $advisorImage);

$siteName = $siteSettings['site_name'] ?? ($_ENV['APP_NAME'] ?? $advisorName);
$speciality1 = $siteSettings['speciality_1'] ?? 'vente immobilière';
$speciality2 = $siteSettings['speciality_2'] ?? 'accompagnement local';
$speciality3 = $siteSettings['speciality_3'] ?? 'estimation immobilière';
$yearsExperience = $siteSettings['years_experience'] ?? '10+ ans';
$approachLabel = $siteSettings['approach_label'] ?? 'Accompagnement humain';
$territoryTitle = $siteSettings['territory_title'] ?? $advisorCity;
$territoryDescription = $siteSettings['territory_description'] ?? "J’accompagne mes clients sur {$advisorCity} et ses environs avec une approche locale, claire et personnalisée.";
$aboutHeroTitle = $siteSettings['about_hero_title'] ?? "{$advisorName} - Votre partenaire immobilier à {$advisorCity}";
$aboutHeroSubtitle = $siteSettings['about_hero_subtitle'] ?? "{$advisorTitle} à {$advisorCity}";
$aboutIntroTitle = $siteSettings['about_intro_title'] ?? "Votre allié immobilier pour concrétiser votre projet à {$advisorCity}";
$aboutIntroText = $siteSettings['about_intro_text'] ?? "J’accompagne vendeurs, acheteurs et investisseurs avec une approche humaine, locale et structurée pour faire avancer chaque projet dans les meilleures conditions.";
$aboutStoryTitle = $siteSettings['about_story_title'] ?? "Une approche fondée sur l’écoute, la clarté et l’action";
$aboutStoryText1 = $siteSettings['about_story_text_1'] ?? "Mon rôle ne se limite pas à ouvrir des portes ou diffuser une annonce. Mon objectif est de vous aider à prendre les bonnes décisions, au bon moment, avec une vraie stratégie.";
$aboutStoryText2 = $siteSettings['about_story_text_2'] ?? "Je privilégie une relation simple, directe et transparente, avec une attention particulière portée à la qualité du suivi et à la compréhension du marché local.";
$ctaTitle = $siteSettings['about_cta_title'] ?? "Parlons de votre projet immobilier";
$ctaText = $siteSettings['about_cta_text'] ?? "Vous avez un projet de vente, d’achat ou besoin d’un avis de valeur ? Échangeons ensemble sur la meilleure stratégie à mettre en place.";
$ctaPrimaryLabel = $siteSettings['about_cta_primary_label'] ?? 'Contactez-moi';
$ctaPrimaryUrl = $siteSettings['about_cta_primary_url'] ?? '/contact';
$ctaSecondaryLabel = $siteSettings['about_cta_secondary_label'] ?? 'Demander une estimation';
$ctaSecondaryUrl = $siteSettings['about_cta_secondary_url'] ?? '/estimation-gratuite';

$pageTitle = "À propos — {$advisorName} | Immobilier à {$advisorCity}";
$metaDesc = "Découvrez {$advisorName}, {$advisorTitle} à {$advisorCity}. Un accompagnement immobilier local, humain et structuré pour vendre, acheter ou estimer votre bien.";

$territories = $siteSettings['territories'] ?? [
    $advisorCity,
    'Centre-ville',
    'Quartiers résidentiels',
    'Communes voisines',
];

if (is_string($territories)) {
    $decoded = json_decode($territories, true);
    $territories = is_array($decoded) ? $decoded : array_map('trim', explode(',', $territories));
}

$values = $siteSettings['about_values'] ?? [
    [
        'icon' => '🏡',
        'title' => 'Accompagnement sur mesure',
        'text' => 'Chaque projet est différent. Mon rôle est d’adapter la stratégie à votre situation réelle.',
    ],
    [
        'icon' => '📍',
        'title' => 'Expertise locale',
        'text' => "Je m’appuie sur une vraie lecture du marché de {$advisorCity} et de son environnement.",
    ],
    [
        'icon' => '🤝',
        'title' => 'Relation de confiance',
        'text' => 'Je privilégie une communication claire, un suivi régulier et des engagements tenus.',
    ],
    [
        'icon' => '📈',
        'title' => 'Vision stratégique',
        'text' => 'Mon objectif est de faire avancer votre projet avec méthode, pas juste de multiplier les actions.',
    ],
];

if (is_string($values)) {
    $decoded = json_decode($values, true);
    $values = is_array($decoded) ? $decoded : [];
}

$certifications = $siteSettings['about_certifications'] ?? [
    [
        'icon' => '📜',
        'title' => 'Accompagnement professionnel',
        'text' => 'Un cadre clair, structuré et sérieux pour sécuriser votre projet.',
    ],
    [
        'icon' => '🛡️',
        'title' => 'Suivi rigoureux',
        'text' => 'Une attention portée aux détails, aux délais et à la bonne coordination des étapes.',
    ],
    [
        'icon' => '🎯',
        'title' => 'Conseil orienté résultat',
        'text' => 'Des recommandations concrètes pour faire avancer votre vente ou votre achat.',
    ],
];

if (is_string($certifications)) {
    $decoded = json_decode($certifications, true);
    $certifications = is_array($decoded) ? $decoded : [];
}
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <span>À propos</span>
        </nav>
        <h1><?= e($aboutHeroTitle) ?></h1>
        <p><?= e($aboutHeroSubtitle) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:4rem;align-items:center">
            <div data-animate>
                <div class="about-portrait-container">
                    <img
                        src="<?= e($advisorImage) ?>"
                        alt="<?= e($advisorName . ' - ' . $advisorTitle) ?>"
                        class="about-portrait"
                        loading="eager"
                    >
                </div>
            </div>

            <div data-animate>
                <span class="section-label">Mon approche</span>
                <h2 class="section-title"><?= e($aboutIntroTitle) ?></h2>
                <p><?= e($aboutIntroText) ?></p>

                <ul class="feature-list">
                    <li>✓ <?= e(ucfirst($speciality1)) ?></li>
                    <li>✓ <?= e(ucfirst($speciality2)) ?></li>
                    <li>✓ <?= e(ucfirst($speciality3)) ?></li>
                    <li>✓ Accompagnement local et personnalisé</li>
                </ul>

                <p>J’interviens principalement sur <strong><?= e($advisorCity) ?></strong> et les secteurs environnants, avec une volonté simple : vous aider à avancer avec plus de clarté et moins de friction.</p>

                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?= e($yearsExperience) ?></div>
                        <div class="stat-label">d’expérience terrain</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">Local</div>
                        <div class="stat-label">ancrage marché</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= e($approachLabel) ?></div>
                        <div class="stat-label">approche client</div>
                    </div>
                </div>

                <div class="contact-info">
                    <?php if (!empty($advisorPhone)): ?>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', (string) $advisorPhone)) ?>" class="contact-link">
                            <span class="contact-icon">📞</span> <?= e($advisorPhoneDisplay) ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($advisorEmail)): ?>
                        <a href="mailto:<?= e($advisorEmail) ?>" class="contact-link">
                            <span class="contact-icon">✉️</span> <?= e($advisorEmail) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <a href="/contact" class="btn btn--primary">Discutons de votre projet</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Mes engagements</span>
            <h2 class="section-title">Ce qui fait ma différence</h2>
            <p class="section-subtitle">Une façon de travailler plus claire, plus humaine et plus structurée.</p>
        </div>

        <div class="grid-3" data-animate>
            <?php foreach ($values as $value): ?>
                <div class="value-card">
                    <div class="value-icon"><?= e($value['icon'] ?? '•') ?></div>
                    <h3><?= e($value['title'] ?? '') ?></h3>
                    <p><?= e($value['text'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:4rem;align-items:center">
            <div data-animate>
                <img
                    src="<?= e($advisorSecondaryImage) ?>"
                    alt="<?= e($advisorName . ' en accompagnement client') ?>"
                    class="about-image"
                >
            </div>

            <div data-animate>
                <span class="section-label">Mon parcours</span>
                <h2 class="section-title"><?= e($aboutStoryTitle) ?></h2>
                <p><?= e($aboutStoryText1) ?></p>
                <p><?= e($aboutStoryText2) ?></p>

                <ul class="feature-list">
                    <li>✓ Conseils adaptés à votre situation</li>
                    <li>✓ Lecture locale du marché</li>
                    <li>✓ Suivi clair des étapes</li>
                    <li>✓ Accompagnement orienté résultat</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Professionnalisme</span>
            <h2 class="section-title">Mes repères de travail</h2>
        </div>

        <div class="certifications-grid" data-animate>
            <?php foreach ($certifications as $item): ?>
                <div class="certification-card">
                    <div class="certification-icon"><?= e($item['icon'] ?? '•') ?></div>
                    <h3><?= e($item['title'] ?? '') ?></h3>
                    <p><?= e($item['text'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:4rem;align-items:center">
            <div data-animate>
                <img
                    src="<?= e($advisorTerritoryImage) ?>"
                    alt="<?= e($advisorName . ' sur le secteur de ' . $advisorCity) ?>"
                    class="about-image"
                >
            </div>

            <div data-animate>
                <span class="section-label">Mon territoire</span>
                <h2 class="section-title"><?= e($territoryTitle) ?></h2>
                <p><?= e($territoryDescription) ?></p>

                <div class="location-grid">
                    <?php foreach ($territories as $territory): ?>
                        <div class="location-item"><?= e((string) $territory) ?></div>
                    <?php endforeach; ?>
                </div>

                <p>Que votre projet concerne une résidence principale, un investissement ou une vente, je m’appuie sur une lecture concrète du terrain pour vous orienter dans la bonne direction.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2><?= e($ctaTitle) ?></h2>
        <p><?= e($ctaText) ?></p>

        <div class="cta-actions">
            <a href="<?= e($ctaPrimaryUrl) ?>" class="btn btn--accent btn--lg"><?= e($ctaPrimaryLabel) ?></a>
            <a href="<?= e($ctaSecondaryUrl) ?>" class="btn btn--outline-white btn--lg"><?= e($ctaSecondaryLabel) ?></a>
        </div>
    </div>
</section>

<style>
.about-portrait-container {
    background: linear-gradient(135deg, #f5f1eb 0%, #e8ddd4 100%);
    border-radius: 2rem;
    padding: 2rem;
    aspect-ratio: 3/4;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.about-portrait {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 1rem;
    transform: scale(1.02);
}

.about-image {
    width: 100%;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-item {
    text-align: center;
    padding: 1.5rem;
    background: var(--clr-bg);
    border-radius: 1rem;
    border: 1px solid var(--clr-border);
}

.stat-value {
    font-family: var(--font-display);
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--clr-primary);
    margin-bottom: 0.3rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--clr-text-muted);
}

.value-card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    text-align: center;
    transition: transform 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
}

.value-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.certifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.certification-card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    text-align: center;
}

.certification-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.location-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.8rem;
    margin: 1.5rem 0;
}

.location-item {
    background: var(--clr-bg);
    padding: 0.8rem 1.2rem;
    border-radius: 0.8rem;
    font-size: 0.9rem;
    text-align: center;
    border: 1px solid var(--clr-border);
}

.cta-banner {
    background: linear-gradient(135deg, var(--clr-primary) 0%, var(--clr-accent) 100%);
    color: white;
    padding: 4rem 0;
    text-align: center;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .grid-2,
    .grid-3,
    .stats-grid,
    .certifications-grid,
    .location-grid {
        grid-template-columns: 1fr;
    }
}
</style>