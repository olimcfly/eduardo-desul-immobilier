<?php
require_once __DIR__ . '/../../core/bootstrap.php';
?>

<section class="hub-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fab fa-google"></i> Google My Business</div>
        <h1>Recevez plus d'appels depuis Google</h1>
        <p>Activez votre présence locale pour convertir les recherches en rendez-vous.</p>
    </header>

    <div class="gmb-manual-notice">
        Votre module fonctionne actuellement en mode manuel. Vous pouvez préparer votre fiche, vos avis,
        vos demandes d’avis et vos publications. La connexion Google Business Profile pourra être ajoutée plus tard.
    </div>

    <div class="hub-modules-grid">
        <a class="hub-module-card" href="/admin/?module=gmb&view=fiche">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-id-card"></i></div>
                <h3>Compléter la fiche</h3>
            </div>
            <p>Renseignez les infos essentielles de votre établissement.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin/?module=gmb&view=avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-star"></i></div>
                <h3>Répondre aux avis</h3>
            </div>
            <p>Montrez votre réactivité et renforcez votre image.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin/?module=gmb&view=demande-avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-envelope-open-text"></i></div>
                <h3>Demander des avis</h3>
            </div>
            <p>Augmentez les retours clients après chaque transaction.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin/?module=redaction&action=pool_gmb">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-pen-nib"></i></div>
                <h3>Publier chaque semaine</h3>
            </div>
            <p>Restez visible localement avec des posts réguliers.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin/?module=gmb&view=statistiques">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-chart-bar"></i></div>
                <h3>Suivre les résultats</h3>
            </div>
            <p>Mesurez les appels, clics et vues générés par votre fiche.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>
    </div>

    <section class="hub-final-cta" aria-label="Progression GMB">
        <div>
            <h2>Progression : Fiche → Avis → Demandes → Publications → Résultats</h2>
            <p>Commencez par un levier, puis activez les suivants.</p>
        </div>
        <a class="hub-btn hub-btn--gold" href="/admin/?module=gmb&view=fiche"><i class="fas fa-arrow-trend-up"></i> Lancer la première étape</a>
    </section>

</section>
