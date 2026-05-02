<?php
$pageTitle = 'Capter des contacts';
$pageDescription = 'Convertis les visiteurs en prospects';

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

    <div class="start-hero">
        <div class="start-hero-badge">Guide de capture</div>
        <h1>Convertir les visiteurs en prospects</h1>
        <p>
            Creez un parcours clair et convaincant pour transformer chaque visite en contact qualifie.
            Suivez ces 5 etapes pour maximiser vos conversions.
        </p>
    </div>

    <div class="start-steps-title">Les 5 etapes clés</div>
    <div class="start-steps">

        <a href="/admin?module=landing-pages" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-flag" style="color:#3b82f6;margin-right:6px;"></i>Creer une landing page</div>
                <div class="start-step-desc">Designez une page d'entree simple et claire qui presente votre offre en quelques secondes.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=cms" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-list-check" style="color:#10b981;margin-right:6px;"></i>Concevoir un formulaire efficace</div>
                <div class="start-step-desc">Gardez seulement les champs essentiels pour ne pas decourager vos prospects.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=cms" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-circle-check" style="color:#f59e0b;margin-right:6px;"></i>Proposer une page de confirmation</div>
                <div class="start-step-desc">Confirmez la demande et proposez l'etape suivante (appel, email, rendez-vous).</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=optimiser" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-chart-line" style="color:#ef4444;margin-right:6px;"></i>Mesurer vos conversions</div>
                <div class="start-step-desc">Installez le suivi (UTM, pixels) pour savoir d'ou viennent vos meilleurs contacts.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=optimiser" class="start-step">
            <div class="start-step-num">5</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-rocket" style="color:#8b5cf6;margin-right:6px;"></i>Optimiser et tester</div>
                <div class="start-step-desc">Testez differents titres, messages et appels a l'action pour ameliorer votre taux de conversion.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>

    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Pret a capturer plus de contacts ?</strong>
            <span>Commencez par l'etape 1 : creez une landing page qui convertit.</span>
        </div>
        <a href="/admin?module=landing-pages" class="start-cta-btn">
            <i class="fas fa-arrow-right"></i> Demarrer maintenant
        </a>
    </div>
    <?php
}
