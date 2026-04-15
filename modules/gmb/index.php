<?php
require_once __DIR__ . '/../../core/bootstrap.php';
?>

<section class="hub-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fab fa-google"></i> Google My Business</div>
        <h1>Recevez plus d'appels depuis Google</h1>
        <p>Activez votre présence locale pour convertir les recherches en rendez-vous.</p>
    </header>

    <section class="hub-narrative" aria-label="Méthode GMB">
        <article class="hub-narrative-card hub-narrative-card--motivation">
            <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Problème</h3>
            <p>Votre fiche locale ne transforme pas assez de vues en contacts.</p>
        </article>
        <article class="hub-narrative-card hub-narrative-card--explanation">
            <h3><i class="fas fa-diagram-project" style="color:#3b82f6;"></i> Logique</h3>
            <p>Profil complet, avis réguliers, publications utiles.</p>
        </article>
        <article class="hub-narrative-card hub-narrative-card--resultat">
            <h3><i class="fas fa-chart-line" style="color:#10b981;"></i> Bénéfice</h3>
            <p>Vous gagnez en confiance locale et en demandes entrantes.</p>
        </article>
        <article class="hub-narrative-card hub-narrative-card--action">
            <h3><i class="fas fa-play-circle" style="color:#f59e0b;"></i> Action</h3>
            <p>Commencez par compléter votre fiche dès aujourd'hui.</p>
        </article>
    </section>

    <div class="hub-modules-grid">
        <a class="hub-module-card" href="/admin?module=gmb&view=fiche">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-id-card"></i></div>
                <h3>Compléter la fiche</h3>
            </div>
            <p>Renseignez les infos essentielles de votre établissement.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-star"></i></div>
                <h3>Répondre aux avis</h3>
            </div>
            <p>Montrez votre réactivité et renforcez votre image.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=demande-avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-envelope-open-text"></i></div>
                <h3>Demander des avis</h3>
            </div>
            <p>Augmentez les retours clients après chaque transaction.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=redaction&action=pool_gmb">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-pen-nib"></i></div>
                <h3>Publier chaque semaine</h3>
            </div>
            <p>Restez visible localement avec des posts réguliers.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=statistiques">
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
        <a class="hub-btn hub-btn--gold" href="/admin?module=gmb&view=fiche"><i class="fas fa-arrow-trend-up"></i> Lancer la première étape</a>
    </section>

</section>
