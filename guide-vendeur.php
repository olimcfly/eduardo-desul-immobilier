<?php
$siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'example.com');
$pageUrl = $siteUrl . ($_SERVER['REQUEST_URI'] ?? '/guide-vendeur.php');
$orgName = 'Conseiller Immobilier';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide vendeur immobilier : vendre au bon prix, sans stress</title>
    <meta name="description" content="Le guide vendeur complet en 5 étapes : estimation, documents, valorisation, négociation et signature. Checklist 29 points + conseils d'expert.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="guide.css">
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => 'Guide vendeur immobilier : 5 étapes pour vendre au meilleur prix',
        'description' => 'Méthode pratique de vente immobilière de l\'estimation à la signature définitive.',
        'totalTime' => 'P90D',
        'publisher' => ['@type' => 'Organization', 'name' => $orgName],
        'url' => $pageUrl,
        'step' => [
            ['@type' => 'HowToStep', 'name' => 'Estimation du bien'],
            ['@type' => 'HowToStep', 'name' => 'Préparer les documents obligatoires'],
            ['@type' => 'HowToStep', 'name' => 'Valoriser le bien avant diffusion'],
            ['@type' => 'HowToStep', 'name' => 'Gérer visites et négociation'],
            ['@type' => 'HowToStep', 'name' => 'Signer et finaliser chez le notaire'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body>
<div class="reading-progress" id="readingProgress"></div>

<header class="hero" id="top">
    <div class="container hero-grid">
        <div>
            <span class="badge">Guide vendeur 2026</span>
            <h1>Vendre votre bien <em>au bon prix</em>, sans y laisser votre énergie.</h1>
            <p class="lead">Vous ne vendez pas “juste des mètres carrés”. Vous tournez une page de vie. Ce guide vous aide à prendre les bonnes décisions au bon moment.</p>
            <div class="hero-cta">
                <a href="#step-1" class="btn btn-primary">Commencer le guide</a>
                <a href="#final-cta" class="btn btn-light">Demander une estimation</a>
            </div>
        </div>
        <div class="hero-stats">
            <article><strong>5</strong><span>Étapes clés</span></article>
            <article><strong data-counter="29">0</strong><span>Points de checklist</span></article>
            <article><strong>J+90</strong><span>Timeline notariale type</span></article>
        </div>
    </div>
</header>

<div class="container page-grid">
    <main>
        <nav class="toc reveal">
            <h2>Sommaire interactif</h2>
            <div class="toc-links">
                <a href="#intro">Intro</a>
                <a href="#step-1">1. Estimation</a>
                <a href="#step-2">2. Documents</a>
                <a href="#step-3">3. Valorisation</a>
                <a href="#step-4">4. Visites & négociation</a>
                <a href="#step-5">5. Signature</a>
            </div>
        </nav>

        <section id="intro" class="card reveal">
            <h2>Vous avez déjà commencé à réfléchir. C'est le moment de structurer.</h2>
            <p>La plupart des vendeurs oscillent entre deux peurs : <strong>vendre trop bas</strong> ou <strong>attendre trop longtemps</strong>. Notre méthode vous donne une trajectoire claire, mesurable, et rassurante.</p>
        </section>

        <section id="step-1" class="card reveal step" data-step-title="Estimation">
            <h2>Étape 1 — Estimation</h2>
            <div class="stat-grid">
                <article><strong data-counter="3">0</strong><span>Méthodes de comparaison</span></article>
                <article><strong data-counter="72">0</strong><span>Heures pour ajuster la stratégie</span></article>
                <article><strong data-counter="90">0</strong><span>Jours de projection de vente</span></article>
            </div>
            <h3>Grille des méthodes</h3>
            <div class="method-grid">
                <article class="bad"><h4>❌ Mauvais</h4><p>Se baser uniquement sur le voisinage ou l'affect.</p></article>
                <article class="ok"><h4>⚠️ Correct</h4><p>Comparer 2-3 annonces actives sans historique de vente.</p></article>
                <article class="good"><h4>✅ Optimal</h4><p>Analyse ventes signées + tension locale + qualité du bien.</p></article>
            </div>
            <div class="plus-minus">
                <div><h4>Facteurs +</h4><ul><li>Localisation précise</li><li>Luminosité et plan</li><li>DPE favorable</li></ul></div>
                <div><h4>Facteurs -</h4><ul><li>Travaux lourds</li><li>Nuisances</li><li>Charges élevées</li></ul></div>
            </div>
            <aside class="callout">Piège émotionnel : “J'ai acheté cher, donc je dois revendre cher.” Le marché n'achète pas votre histoire, il achète une valeur actuelle.</aside>
            <a href="#final-cta" class="inline-cta">Obtenir une estimation fiable maintenant →</a>
        </section>

        <section id="step-2" class="card reveal step" data-step-title="Documents">
            <h2>Étape 2 — Documents</h2>
            <div class="tabs" data-tabs>
                <div class="tab-buttons">
                    <button class="tab-btn is-active" data-tab="appartement">Appartement</button>
                    <button class="tab-btn" data-tab="maison">Maison</button>
                    <button class="tab-btn" data-tab="investissement">Investissement</button>
                </div>
                <div class="tab-panels">
                    <article class="tab-panel is-active" data-panel="appartement"><ul><li>Titre de propriété</li><li>PV AG + carnet d'entretien</li><li>DPE, amiante, électricité</li></ul></article>
                    <article class="tab-panel" data-panel="maison"><ul><li>Titre de propriété</li><li>Taxe foncière</li><li>Diagnostics (DPE, termites, assainissement)</li></ul></article>
                    <article class="tab-panel" data-panel="investissement"><ul><li>Baux en cours</li><li>Historique des loyers</li><li>Charges et rentabilité nette</li></ul></article>
                </div>
            </div>
        </section>

        <section id="step-3" class="card reveal step" data-step-title="Valorisation">
            <h2>Étape 3 — Valorisation</h2>
            <p>Un bien bien préparé se vend plus vite. Désencombrement, neutralisation visuelle, petites réparations ciblées et photos professionnelles font la différence dès la première impression.</p>
        </section>

        <section id="step-4" class="card reveal step" data-step-title="Visites & Négociation">
            <h2>Étape 4 — Visites & Négociation</h2>
            <h3>6 conseils visite</h3>
            <ul class="tips-grid">
                <li>Préparer un parcours de visite logique.</li><li>Aérer et illuminer 30 min avant.</li><li>Limiter les objets personnels.</li>
                <li>Répondre avec des faits, pas des promesses.</li><li>Collecter le feedback à chaud.</li><li>Relancer sous 24 h.</li>
            </ul>
            <h3>Scénarios d'offres (4 cas)</h3>
            <div class="offer-grid">
                <article>Offre au prix : sécuriser délai et financement.</article>
                <article>Offre basse : contre-offre structurée.</article>
                <article>Offres multiples : arbitrer net vendeur + risque.</article>
                <article>Offre conditionnelle : vérifier clause suspensive.</article>
            </div>
        </section>

        <section id="step-5" class="card reveal step" data-step-title="Signature">
            <h2>Étape 5 — Signature</h2>
            <div class="timeline">
                <article><strong>J0</strong><span>Offre acceptée</span></article>
                <article><strong>J+7</strong><span>Compromis signé</span></article>
                <article><strong>J+45</strong><span>Levée des conditions</span></article>
                <article><strong>J+90</strong><span>Acte authentique</span></article>
            </div>
            <div class="fiscality">
                <article><h4>Résidence principale</h4><p>Exonération de principe de la plus-value.</p></article>
                <article><h4>Résidence secondaire</h4><p>Fiscalité sur plus-value selon durée de détention.</p></article>
                <article><h4>Astuce</h4><p>Anticiper frais, diagnostics et délais bancaires.</p></article>
            </div>
        </section>

        <section class="card reveal" id="checklist">
            <div class="checklist-head">
                <h2>Checklist vendeur — 29 points</h2>
                <button id="resetChecklist" class="btn btn-light" type="button">Réinitialiser</button>
            </div>
            <div class="check-progress"><div id="checkProgressBar"></div></div>
            <p><span id="checkCount">0</span>/29 complétés</p>
            <div class="check-groups" id="checklistGroups"></div>
        </section>

        <section id="final-cta" class="final-cta reveal">
            <h2>Prêt à passer à l'action ?</h2>
            <p>Demandez une estimation argumentée et un plan de vente personnalisé sous 24h.</p>
            <a href="/estimation" class="btn btn-primary">Je veux mon estimation</a>
        </section>
    </main>

    <aside class="sidebar">
        <div class="sidebar-box">
            <h3>Progression lecture</h3>
            <div class="check-progress"><div id="articleProgressBar"></div></div>
            <p id="activeStepLabel">Section en cours : Intro</p>
        </div>
        <div class="sidebar-box"><h3>Estimation gratuite</h3><a href="#final-cta" class="btn btn-primary">Lancer l'estimation</a></div>
        <div class="sidebar-box"><h3>Contact conseiller</h3><p>06 00 00 00 00<br>contact@agence.fr</p></div>
        <div class="sidebar-box"><h3>Témoignage</h3><p>“Vente signée en 6 semaines, avec une stratégie claire du début à la fin.”</p></div>
    </aside>
</div>

<script src="guide.js"></script>
</body>
</html>
