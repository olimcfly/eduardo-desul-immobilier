<?php

require_once __DIR__ . '/services/MonthlyReportService.php';

$allowedActions = ['index', 'rapport-mensuel'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

$pageTitle = $action === 'rapport-mensuel' ? 'Optimiser — Rapport mensuel' : 'Optimiser';
$pageDescription = 'Améliorez vos résultats avec des décisions simples';

function renderContent(): void
{
    global $action;

    if ($action === 'rapport-mensuel') {
        require __DIR__ . '/views/rapport-mensuel.php';
        return;
    }
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-chart-line"></i> Amélioration continue</div>
            <h1>Gagnez en résultats chaque semaine</h1>
            <p>Voyez ce qui marche, corrigez vite, avancez en continu.</p>
        </header>

        <section class="hub-narrative" aria-label="Méthode d'amélioration">
            <article class="hub-narrative-card hub-narrative-card--motivation">
                <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Problème</h3>
                <p>Vous agissez sans repère clair.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--explanation">
                <h3><i class="fas fa-diagram-project" style="color:#3b82f6;"></i> Logique</h3>
                <p>Mesurer, ajuster, vérifier.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--resultat">
                <h3><i class="fas fa-chart-line" style="color:#10b981;"></i> Bénéfice</h3>
                <p>Chaque action devient plus rentable.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--action">
                <h3><i class="fas fa-play-circle" style="color:#f59e0b;"></i> Action</h3>
                <p>Commencez par un seul levier.</p>
            </article>
        </section>

        <div class="hub-modules-grid">
            <a href="?module=optimiser&view=analytics" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-chart-bar"></i></div>
                    <h3>Lire les résultats</h3>
                </div>
                <p>Comprenez en un coup d'œil ce qui monte ou baisse.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <div class="hub-module-card hub-module-card--soon">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-lightbulb"></i></div>
                    <h3>Trouver le frein</h3>
                </div>
                <p>Identifiez le point qui bloque votre progression.</p>
                <span class="hub-state hub-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
            </div>

            <div class="hub-module-card hub-module-card--soon">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-vials"></i></div>
                    <h3>Tester une amélioration</h3>
                </div>
                <p>Comparez deux versions et gardez la meilleure.</p>
                <span class="hub-state hub-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
            </div>

            <a href="/admin?module=optimiser&action=rapport-mensuel" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-file-chart-line"></i></div>
                    <h3>Bilan mensuel</h3>
                </div>
                <p>Gardez une vision claire et prenez vos décisions plus vite.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression optimiser">
            <div>
                <h2>Progression : Mesurer → Corriger → Vérifier → Répéter</h2>
                <p>Commencez par un levier, puis développez votre système.</p>
            </div>
            <a href="?module=optimiser&view=analytics" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Lancer ma première amélioration</a>
        </section>

    </section>
    <?php
}
