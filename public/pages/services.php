<?php
$pageTitle = 'Services immobiliers à Bordeaux — Eduardo Desul';
$metaDesc  = 'Vente, achat, estimation, investissement locatif : Eduardo Desul vous accompagne pour tous vos projets immobiliers à Bordeaux.';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Services</span></nav>
        <h1>Mes services</h1>
        <p>Un accompagnement complet pour tous vos projets immobiliers à Bordeaux et alentours.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php
        $services = [
            ['🏠', 'Vente de votre bien', 'Je vous accompagne de l\'estimation à la signature chez le notaire.', [
                'Estimation précise au prix du marché',
                'Photos professionnelles & home staging',
                'Diffusion sur +30 portails immobiliers',
                'Sélection et accompagnement des acquéreurs',
                'Négociation et suivi jusqu\'à la signature',
            ], '/estimation-gratuite', 'Estimer mon bien'],
            ['🔑', 'Recherche d\'un bien à acheter', 'Je trouve le bien qui correspond exactement à vos critères.', [
                'Définition précise de votre projet',
                'Accès à des biens hors-marché',
                'Visites accompagnées et conseillées',
                'Analyse de la valeur avant offre',
                'Accompagnement jusqu\'à la remise des clés',
            ], '/contact', 'Démarrer ma recherche'],
            ['📊', 'Estimation gratuite', 'Connaissez la vraie valeur de votre bien en moins de 48h.', [
                'Analyse comparative du marché',
                'Visite du bien et évaluation terrain',
                'Rapport d\'estimation détaillé',
                'Conseils pour valoriser votre bien',
                'Sans engagement, 100% gratuit',
            ], '/estimation-gratuite', 'Demander une estimation'],
            ['💼', 'Investissement locatif', 'Maximisez votre rendement locatif avec une stratégie adaptée.', [
                'Analyse des zones à fort potentiel',
                'Calcul de rentabilité détaillé',
                'Sélection de biens adaptés à votre profil',
                'Conseils fiscaux (dispositifs Pinel, LMNP…)',
                'Gestion locative optionnelle',
            ], '/contact', 'Étudier mon investissement'],
        ];
        foreach ($services as $i => [$icon, $titre, $desc, $items, $href, $cta]): ?>
        <div class="grid-2" style="gap:4rem;align-items:center;margin-bottom:5rem<?= $i % 2 === 0 ? '' : ';direction:rtl' ?>" data-animate>
            <div style="direction:ltr">
                <span class="section-label">Service <?= $i + 1 ?></span>
                <h2 class="section-title"><?= $icon ?> <?= e($titre) ?></h2>
                <p style="color:var(--clr-text-muted);margin-bottom:1.5rem"><?= e($desc) ?></p>
                <ul style="list-style:none;margin-bottom:2rem">
                    <?php foreach ($items as $item): ?>
                    <li style="display:flex;gap:.75rem;margin-bottom:.75rem;font-size:.95rem">
                        <span style="color:var(--clr-success);font-weight:700;flex-shrink:0">✓</span>
                        <?= e($item) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= e($href) ?>" class="btn btn--primary"><?= e($cta) ?></a>
            </div>
            <div style="direction:ltr">
                <div style="background:linear-gradient(135deg,var(--clr-primary),#0f2644);border-radius:var(--radius-xl);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:5rem">
                    <?= $icon ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Tarifs -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Transparence</span>
            <h2 class="section-title">Des honoraires clairs</h2>
            <p class="section-subtitle">Pas de surprise : mes honoraires sont fixés à l'avance et intégrés dans le prix de vente.</p>
        </div>
        <div class="grid-3" data-animate>
            <?php foreach ([
                ['Vente', '4 à 6%', 'du prix de vente TTC, honoraires vendeur. Négociable selon la valeur du bien.'],
                ['Achat', '2 à 3%', 'du prix d\'achat TTC, honoraires acquéreur. Service de recherche clé en main.'],
                ['Estimation', 'Gratuit', 'Estimation et rapport écrit. Sans engagement et sans condition.'],
            ] as [$service, $tarif, $desc]): ?>
            <div style="background:var(--clr-white);border-radius:var(--radius-lg);border:1px solid var(--clr-border);padding:2rem;text-align:center">
                <h3 style="margin-bottom:.5rem"><?= $service ?></h3>
                <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--clr-accent);margin:.75rem 0"><?= $tarif ?></div>
                <p style="font-size:.875rem;color:var(--clr-text-muted);margin:0"><?= e($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Commençons votre projet</h2>
        <p>Discutons de vos besoins lors d'un premier échange gratuit et sans engagement.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Prendre rendez-vous</a>
        </div>
    </div>
</section>
