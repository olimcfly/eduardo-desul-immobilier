<?php
$pageTitle = "Capture";
$pageDescription = "Transformez vos visites en prises de contact";

function renderContent() {
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-magnet"></i> Conversion</div>
            <h1>Recevez plus de demandes qualifiées</h1>
            <p>Créez un parcours clair pour convertir chaque visite en contact.</p>
        </header>

        <section class="hub-narrative" aria-label="Méthode de conversion">
            <article class="hub-narrative-card hub-narrative-card--motivation">
                <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Problème</h3>
                <p>Des visiteurs partent sans laisser leurs coordonnées.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--explanation">
                <h3><i class="fas fa-diagram-project" style="color:#3b82f6;"></i> Logique</h3>
                <p>Une promesse claire, un formulaire court, puis une confirmation immédiate.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--resultat">
                <h3><i class="fas fa-chart-line" style="color:#10b981;"></i> Bénéfice</h3>
                <p>Vous obtenez plus de contacts sans effort supplémentaire.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--action">
                <h3><i class="fas fa-play-circle" style="color:#f59e0b;"></i> Action</h3>
                <p>Lancez votre premier parcours de capture maintenant.</p>
            </article>
        </section>

        <div class="hub-modules-grid" aria-label="Actions clés de conversion">
            <a href="/capture/" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-flag-checkered"></i></div>
                    <h3>Page d'entrée</h3>
                </div>
                <p>Présentez votre offre en quelques secondes.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/capture/form.php" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-list-check"></i></div>
                    <h3>Formulaire</h3>
                </div>
                <p>Gardez seulement les champs essentiels.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/capture/merci.php" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-circle-check"></i></div>
                    <h3>Confirmation</h3>
                </div>
                <p>Confirmez la demande et proposez la suite.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=optimiser&view=analytics" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-chart-line"></i></div>
                    <h3>Mesurer</h3>
                </div>
                <p>Repérez rapidement ce qui fait gagner des contacts.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Voir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression conversion">
            <div>
                <h2>Progression : Entrée → Formulaire → Confirmation → Mesure</h2>
                <p>Commencez par un levier, puis développez votre parcours.</p>
            </div>
            <a href="?module=funnels" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Démarrer le parcours</a>
        </section>

    </section>
    <?php
}
