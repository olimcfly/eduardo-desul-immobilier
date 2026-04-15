<?php
$pageTitle       = 'Commencer ici';
$pageDescription = 'Par où démarrer sur votre tableau de bord';

function renderContent()
{
    ?>
    <style>
    .start-hero {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 36px 40px;
        color: #fff;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .start-hero-badge {
        display: inline-block;
        background: rgba(201,168,76,.2);
        color: #c9a84c;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 14px;
        border: 1px solid rgba(201,168,76,.35);
    }
    .start-hero h1 {
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 12px;
        line-height: 1.25;
    }
    .start-hero p {
        font-size: 15px;
        color: rgba(255,255,255,.7);
        line-height: 1.65;
        max-width: 640px;
        margin: 0;
    }

    .start-steps-title {
        font-size: 12px;
        font-weight: 700;
        color: #8a95a3;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 16px;
    }

    .start-steps {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 32px;
    }
    .start-step {
        display: flex;
        align-items: flex-start;
        gap: 18px;
        background: #fff;
        border-radius: 12px;
        padding: 20px 22px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        text-decoration: none;
        color: inherit;
        border-left: 4px solid #e8ecf0;
        transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    .start-step:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 16px rgba(0,0,0,.1);
        border-color: #c9a84c;
    }
    .start-step-num {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
    }
    .start-step-body { flex: 1; }
    .start-step-label {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
    }
    .start-step-desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }
    .start-step-arrow {
        flex-shrink: 0;
        color: #c9a84c;
        font-size: 16px;
        margin-top: 8px;
    }

    .start-cta {
        background: #fff;
        border-radius: 12px;
        padding: 24px 26px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .start-cta-text strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .start-cta-text span {
        font-size: 13px;
        color: #64748b;
    }
    .start-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        background: #c9a84c;
        color: #0f2237;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .start-cta-btn:hover { background: #b8943d; }

    @media (max-width: 600px) {
        .start-hero { padding: 24px 20px; }
        .start-step { flex-wrap: wrap; }
    }
    </style>

    <!-- Bandeau d'accueil -->
    <div class="start-hero">
        <div class="start-hero-badge">Guide de démarrage</div>
        <h1>Bienvenue sur votre espace marketing</h1>
        <p>
            Ce guide vous explique comment tirer le meilleur parti de votre tableau de bord.
            Suivez les étapes dans l'ordre pour poser les bases de votre système d'acquisition.
        </p>
    </div>

    <!-- Étapes -->
    <div class="start-steps-title">Les 5 étapes pour bien démarrer</div>
    <div class="start-steps">

        <a href="/admin/?module=construire" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-layer-group" style="color:#3b82f6;margin-right:6px;"></i>Construire votre positionnement</div>
                <div class="start-step-desc">Commencez par ANCRE+, NeuroPersona, Offre, Zone et Synthèse pour poser des bases claires.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=attirer" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-bullseye" style="color:#10b981;margin-right:6px;"></i>Attirer des vendeurs qualifiés</div>
                <div class="start-step-desc">Créez un plan de contenu régulier pour générer des contacts de manière organique et continue.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=capturer" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-inbox" style="color:#f59e0b;margin-right:6px;"></i>Capturer vos prospects</div>
                <div class="start-step-desc">Mettez en place des formulaires et lead magnets pour transformer vos visiteurs en contacts qualifiés.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=convertir" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-arrow-trend-up" style="color:#ef4444;margin-right:6px;"></i>Convertir en clients</div>
                <div class="start-step-desc">Utilisez des scripts d'appel et des séquences de suivi pour transformer vos leads en mandats signés.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=optimiser" class="start-step">
            <div class="start-step-num">5</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-chart-line" style="color:#8b5cf6;margin-right:6px;"></i>Optimiser vos résultats</div>
                <div class="start-step-desc">Analysez vos KPIs, identifiez les points de friction et améliorez continuellement votre système.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>

    <!-- CTA final -->
    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Prêt à commencer ?</strong>
            <span>Rendez-vous sur votre tableau de bord pour voir l'état de votre activité en temps réel.</span>
        </div>
        <a href="/admin/?module=dashboard" class="start-cta-btn">
            <i class="fas fa-gauge-high"></i> Voir le tableau de bord
        </a>
    </div>
    <?php
}
