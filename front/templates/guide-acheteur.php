<?php
/**
 * Guide Acheteur Immobilier (France) - Page autonome.
 */
if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$canonical = $baseUrl . '/guide-acheteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide Acheteur Immobilier 2026 | Eduardo Desul Immobilier</title>
    <meta name="description" content="Le guide acheteur complet pour réussir votre achat immobilier : budget, recherche, offre, financement, signature et checklist interactive.">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/front/assets/css/guide-acheteur.css">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "HowTo",
      "name": "Guide acheteur immobilier : de la préparation à la signature",
      "description": "6 étapes clés pour acheter un bien immobilier en France.",
      "totalTime": "P90D",
      "step": [
        {"@type": "HowToStep", "name": "Définir votre budget"},
        {"@type": "HowToStep", "name": "Clarifier le projet et les critères"},
        {"@type": "HowToStep", "name": "Rechercher et visiter efficacement"},
        {"@type": "HowToStep", "name": "Faire l'offre et négocier"},
        {"@type": "HowToStep", "name": "Monter le financement"},
        {"@type": "HowToStep", "name": "Signer et finaliser l'achat"}
      ]
    }
    </script>
</head>
<body>
<div class="reading-progress" id="readingProgress"></div>

<main class="acheteur-layout" id="top">
    <article class="acheteur-main">
        <section class="hero reveal">
            <span class="hero-badge">Guide 2026 • Achat immobilier</span>
            <h1>GUIDE ACHETEUR IMMOBILIER</h1>
            <p class="hero-subtitle">Un parcours clair, humain et actionnable pour acheter sans stress, du budget à la remise des clés.</p>
            <div class="hero-stats">
                <div><strong>6</strong><span>étapes clés</span></div>
                <div><strong>32</strong><span>points checklist</span></div>
                <div><strong>J+90</strong><span>timeline cible</span></div>
            </div>
            <a href="#checklist" class="btn btn-primary">Commencer ma checklist</a>
        </section>

        <section class="sommaire reveal">
            <h2>Sommaire</h2>
            <ol>
                <li><a href="#etape-1">Étape 1 — Budget</a></li>
                <li><a href="#etape-2">Étape 2 — Projet & critères</a></li>
                <li><a href="#etape-3">Étape 3 — Recherche & visites</a></li>
                <li><a href="#etape-4">Étape 4 — Offre & négociation</a></li>
                <li><a href="#etape-5">Étape 5 — Financement</a></li>
                <li><a href="#etape-6">Étape 6 — Signature</a></li>
                <li><a href="#bonus">Bonus — Checklist 32 points</a></li>
            </ol>
        </section>

        <section class="intro reveal">
            <p>Acheter son premier (ou prochain) bien peut être émotionnellement intense : peur de se tromper, pression du marché, jargon bancaire… Ce guide est pensé pour vous aider à décider sereinement, étape par étape.</p>
        </section>

        <section id="etape-1" class="step reveal" data-step="1">
            <h2>Étape 1 — Budget</h2>
            <div class="budget-box">
                <h3>Décomposition du budget global</h3>
                <ul>
                    <li>Prix d'achat du bien</li>
                    <li>Frais de notaire (ancien/neuf)</li>
                    <li>Frais de garantie bancaire</li>
                    <li>Frais de courtage éventuels</li>
                    <li>Travaux, ameublement, déménagement</li>
                </ul>
            </div>
            <div class="formula-box">Taux d'endettement maximal recommandé : <strong>(Charges + future mensualité) / Revenus nets ≤ 35%</strong></div>
            <div class="apport-stats">
                <div><h4>0%</h4><p>Financement plus exigeant</p></div>
                <div><h4>10%</h4><p>Profil rassurant pour la banque</p></div>
                <div><h4>20%</h4><p>Meilleures conditions en général</p></div>
            </div>
            <aside class="callout">💡 <strong>PTZ :</strong> si vous êtes primo-accédant, vérifiez votre éligibilité au Prêt à Taux Zéro selon votre zone et vos revenus.</aside>
            <div class="table-wrap">
                <table class="aids-table">
                    <thead><tr><th>Aide</th><th>Public</th><th>Point clé</th></tr></thead>
                    <tbody>
                    <tr><td>PTZ</td><td>Primo-accédants</td><td>Prêt sans intérêts sous conditions</td></tr>
                    <tr><td>Prêt Action Logement</td><td>Salariés éligibles</td><td>Taux avantageux en complément</td></tr>
                    <tr><td>Prêt conventionné/PAS</td><td>Ménages modestes</td><td>Peut ouvrir droit à APL accession</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="etape-2" class="step reveal" data-step="2">
            <h2>Étape 2 — Projet & Critères</h2>
            <p>Hiérarchisez vos critères : non négociables, importants, secondaires. Exemple : localisation, transport, surface, DPE, extérieur, stationnement, copropriété.</p>
        </section>

        <section id="etape-3" class="step reveal" data-step="3">
            <h2>Étape 3 — Recherche & Visites</h2>
            <div class="visit-grid">
                <div>État structurel (murs, toiture, humidité)</div>
                <div>Isolation, DPE, système de chauffage</div>
                <div>Charges copropriété et PV d'AG</div>
                <div>Nuisances sonores, exposition, vis-à-vis</div>
                <div>Travaux votés / à venir</div>
                <div>Réseau, transports, commerces</div>
            </div>
        </section>

        <section id="etape-4" class="step reveal" data-step="4">
            <h2>Étape 4 — Offre & Négociation</h2>
            <div class="offer-scenarios">
                <article class="scenario s1"><h3>Prix affiché accepté</h3><p>Rapide, idéal si marché tendu.</p></article>
                <article class="scenario s2"><h3>Offre ajustée</h3><p>Argumentez avec travaux, DPE, ventes comparables.</p></article>
                <article class="scenario s3"><h3>Contre-offre vendeur</h3><p>Négociez prix + délais + conditions.</p></article>
                <article class="scenario s4"><h3>Refus</h3><p>Restez stratégique, gardez un plan B.</p></article>
            </div>
        </section>

        <section id="etape-5" class="step reveal" data-step="5">
            <h2>Étape 5 — Financement</h2>
            <ol>
                <li>Rassembler les pièces justificatives</li>
                <li>Comparer banques/courtiers</li>
                <li>Recevoir les offres de prêt</li>
                <li>Signer après délai légal de réflexion</li>
            </ol>
            <div class="apport-stats assurance-stats">
                <div><h4>100%</h4><p>Délégation possible</p></div>
                <div><h4>≈40%</h4><p>Économie potentielle*</p></div>
                <div><h4>Annuel</h4><p>Droit à la résiliation</p></div>
            </div>
        </section>

        <section id="etape-6" class="step reveal" data-step="6">
            <h2>Étape 6 — Signature</h2>
            <div class="timeline">
                <div><span class="dot"></span><strong>J0</strong> Offre acceptée</div>
                <div><span class="dot"></span><strong>J+30</strong> Compromis signé</div>
                <div><span class="dot"></span><strong>J+45</strong> Accord de prêt</div>
                <div><span class="dot"></span><strong>J+90</strong> Acte authentique</div>
            </div>
        </section>

        <section id="bonus" class="reveal">
            <h2>Checklist 32 points</h2>
            <p id="checklist" class="checklist-meta">Cochez les actions terminées. Votre progression est sauvegardée automatiquement.</p>
            <div class="progress-wrapper">
                <div class="progress-track"><div id="checkProgress" class="progress-fill"></div></div>
                <strong id="checkProgressText">0 / 32</strong>
            </div>
            <div id="checkGroups" class="check-groups"></div>
            <p id="completionMessage" class="completion-message" hidden>🎉 Bravo, votre parcours acheteur est prêt. <a href="#top">Revenir en haut</a></p>
            <button id="resetChecklist" class="btn btn-ghost" type="button">Réinitialiser la checklist</button>
        </section>

        <section class="final-cta reveal">
            <h2>Prêt à passer à l'action ?</h2>
            <p>Recevez une sélection ciblée de biens + un accompagnement pas à pas.</p>
            <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/contact">Parler à un conseiller</a>
        </section>
    </article>

    <aside class="sticky-sidebar" id="sidebar">
        <h3>Votre progression</h3>
        <div class="side-progress"><div id="sideReadProgress"></div></div>
        <p id="activeStepLabel">Étape active : Introduction</p>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/biens">Créer une alerte biens</a>
        <a class="btn btn-ghost" href="<?= htmlspecialchars($baseUrl) ?>/contact">Contacter un conseiller</a>
        <blockquote>“On a acheté en confiance grâce à une méthode claire.” — Camille & Rayan</blockquote>
        <button id="shareBtn" class="btn btn-ghost" type="button">Partager ce guide</button>
    </aside>
</main>

<script src="<?= htmlspecialchars($baseUrl) ?>/front/assets/js/guide-acheteur.js"></script>
</body>
</html>
