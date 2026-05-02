<?php
$pageTitle       = 'Être visible';
$pageDescription = 'Attirer des vendeurs qualifiés en continu';

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
        <div class="start-hero-badge">Guide d'attraction</div>
        <h1>Attirer des vendeurs qualifiés en continu</h1>
        <p>
            Utilisez les bons leviers au bon moment pour générer des contacts réguliers.
            Suivez ces 5 étapes pour construire une visibilité durable sans dépendre du hasard.
        </p>
    </div>

    <!-- Étapes -->
    <div class="start-steps-title">Les 5 leviers clés</div>
    <div class="start-steps">

        <a href="/admin?module=gmb" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-magnifying-glass" style="color:#3b82f6;margin-right:6px;"></i>Être visible localement</div>
                <div class="start-step-desc">Optimisez votre présence sur Google Maps, Google My Business et les annuaires locaux pour apparaître quand on vous cherche.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=blog" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-pen" style="color:#10b981;margin-right:6px;"></i>Créer du contenu régulier</div>
                <div class="start-step-desc">Publiez des articles, vidéos et conseils que vos vendeurs cherchent activement sur Google et les réseaux sociaux.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=social" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-rocket" style="color:#f59e0b;margin-right:6px;"></i>Utiliser la publicité payante</div>
                <div class="start-step-desc">Lancez des campagnes Google Ads ou Facebook pour accélérer vos résultats avec des contacts qualifiés rapidement.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=crm-hub" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-share-nodes" style="color:#ef4444;margin-right:6px;"></i>Activer le bouche-à-oreille</div>
                <div class="start-step-desc">Demandez des recommandations à vos anciens clients et créez des partenariats avec les professionnels locaux (notaires, banquiers, syndics).</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin?module=optimiser" class="start-step">
            <div class="start-step-num">5</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-chart-line" style="color:#8b5cf6;margin-right:6px;"></i>Mesurer et optimiser</div>
                <div class="start-step-desc">Suivez d'où viennent vos contacts, testez différents messages, et ajustez votre stratégie pour améliorer vos résultats mois après mois.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>

    <!-- CTA final -->
    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Prêt à devenir visible ?</strong>
            <span>Commencez par l'étape 1 : optimisez votre présence locale pour qu'on vous trouve facilement.</span>
        </div>
        <a href="/admin?module=gmb" class="start-cta-btn">
            <i class="fas fa-arrow-right"></i> Démarrer maintenant
        </a>
    </div>
    <?php
}
