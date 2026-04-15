<?php
$pageTitle = "Tableau de bord";
$pageDescription = "Vue d'ensemble de votre activité immobilière";

function renderContent() {
    $pdo = db();

    // ── KPIs ─────────────────────────────────────────────────────
    $biens_actifs = (int) $pdo->query("SELECT COUNT(*) FROM biens WHERE statut = 'Disponible'")->fetchColumn();
    $biens_total  = (int) $pdo->query("SELECT COUNT(*) FROM biens")->fetchColumn();

    $leads_mois   = (int) $pdo->query(
        "SELECT COUNT(*) FROM crm_leads WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();
    $leads_total  = (int) $pdo->query("SELECT COUNT(*) FROM crm_leads")->fetchColumn();

    $msgs_nonlus  = (int) $pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE direction = 'in' AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    $estims_mois  = (int) $pdo->query(
        "SELECT COUNT(*) FROM estimation_requests WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    $rdv_mois     = (int) $pdo->query(
        "SELECT COUNT(*) FROM estimation_rdv WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    // ── Données utilisateur ───────────────────────────────────────
    $user     = Auth::user();
    $prenom   = $user['firstname'] ?? explode(' ', APP_NAME)[0] ?? 'Pascal';

    $jours_fr = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois_fr  = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $date_str = $jours_fr[date('w')] . ' ' . date('j') . ' ' . $mois_fr[(int)date('n') - 1] . ' ' . date('Y');

    // ── Message contextuel ────────────────────────────────────────
    $heure = (int) date('G');
    $salut = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');

    $urgence = '';
    if ($msgs_nonlus > 0) {
        $urgence = $msgs_nonlus === 1
            ? '<strong>1 message reçu</strong> ce mois — à traiter.'
            : '<strong>' . $msgs_nonlus . ' messages reçus</strong> ce mois — à traiter.';
    } elseif ($leads_mois > 0) {
        $urgence = $leads_mois === 1
            ? '<strong>1 nouveau lead</strong> ce mois — à qualifier.'
            : '<strong>' . $leads_mois . ' nouveaux leads</strong> ce mois — à qualifier.';
    } else {
        $urgence = 'Aucun lead ni message en attente. Bon moment pour prospecter.';
    }

    ?>
    <style>
    .db-welcome {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .db-welcome-left { flex: 1; }
    .db-welcome-salut { font-size: 13px; color: rgba(255,255,255,.55); font-weight: 500; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 6px; }
    .db-welcome-name  { font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.2; }
    .db-welcome-msg   { font-size: 14px; color: rgba(255,255,255,.75); line-height: 1.6; }
    .db-welcome-msg strong { color: #c9a84c; }
    .db-welcome-date  {
        text-align: right; color: rgba(255,255,255,.4); font-size: 12px;
        white-space: nowrap; flex-shrink: 0;
    }
    .db-welcome-date-day { font-size: 36px; font-weight: 700; color: rgba(255,255,255,.15); line-height: 1; display: block; }

    .db-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .db-kpi {
        background: #fff;
        border-radius: 12px;
        padding: 20px 22px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-left: 4px solid #e8ecf0;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none;
        display: block;
    }
    .db-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .db-kpi.accent-blue   { border-color: #3498db; }
    .db-kpi.accent-gold   { border-color: #c9a84c; }
    .db-kpi.accent-green  { border-color: #27ae60; }
    .db-kpi.accent-orange { border-color: #e67e22; }
    .db-kpi.accent-red    { border-color: #e74c3c; }
    .db-kpi-icon  { font-size: 22px; margin-bottom: 10px; }
    .db-kpi-val   { font-size: 32px; font-weight: 700; color: #2c3e50; line-height: 1; margin-bottom: 4px; }
    .db-kpi-label { font-size: 12px; color: #8a95a3; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
    .db-kpi-sub   { font-size: 11px; color: #b0bac5; margin-top: 4px; }

    .db-actions {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        margin-bottom: 24px;
    }
    .db-actions-title { font-size: 13px; font-weight: 600; color: #8a95a3; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; }
    .db-actions-list  { display: flex; flex-wrap: wrap; gap: 10px; }
    .db-action-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 500;
        text-decoration: none; transition: background .15s, color .15s;
        border: 1.5px solid #e8ecf0; color: #4a5568; background: #f8fafc;
    }
    .db-action-btn:hover { background: #0f2237; color: #fff; border-color: #0f2237; }
    .db-action-btn.primary { background: #c9a84c; color: #0f2237; border-color: #c9a84c; font-weight: 700; }
    .db-action-btn.primary:hover { background: #b8943d; border-color: #b8943d; }

    @media (max-width: 600px) {
        .db-welcome { flex-direction: column; }
        .db-welcome-date { text-align: left; }
        .db-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    </style>

    <!-- Bandeau de bienvenue -->
    <div class="db-welcome">
        <div class="db-welcome-left">
            <div class="db-welcome-salut"><?= $salut ?></div>
            <div class="db-welcome-name"><?= htmlspecialchars($prenom) ?> — votre journée en un coup d'œil</div>
            <div class="db-welcome-msg"><?= $urgence ?></div>
        </div>
        <div class="db-welcome-date">
            <span class="db-welcome-date-day"><?= date('j') ?></span>
            <?= htmlspecialchars($date_str) ?>
        </div>
    </div>

    <!-- KPIs -->
    <div class="db-kpi-grid">

        <a href="/admin/?module=biens" class="db-kpi accent-blue">
            <div class="db-kpi-icon">🏠</div>
            <div class="db-kpi-val"><?= $biens_actifs ?></div>
            <div class="db-kpi-label">Biens actifs</div>
            <div class="db-kpi-sub"><?= $biens_total ?> au total</div>
        </a>

        <a href="/admin/?module=capturer" class="db-kpi <?= $leads_mois > 0 ? 'accent-gold' : 'accent-orange' ?>">
            <div class="db-kpi-icon">🎯</div>
            <div class="db-kpi-val"><?= $leads_mois ?></div>
            <div class="db-kpi-label">Leads ce mois</div>
            <div class="db-kpi-sub"><?= $leads_total ?> au total</div>
        </a>

        <a href="/admin/?module=biens" class="db-kpi <?= $msgs_nonlus > 0 ? 'accent-gold' : 'accent-green' ?>">
            <div class="db-kpi-icon">💬</div>
            <div class="db-kpi-val"><?= $msgs_nonlus ?></div>
            <div class="db-kpi-label">Messages reçus</div>
            <div class="db-kpi-sub">Ce mois</div>
        </a>

        <a href="/admin/?module=estimation" class="db-kpi accent-gold">
            <div class="db-kpi-icon">📊</div>
            <div class="db-kpi-val"><?= $estims_mois ?></div>
            <div class="db-kpi-label">Estimations ce mois</div>
            <div class="db-kpi-sub"><?= $rdv_mois ?> RDV générés</div>
        </a>

    </div>

    <!-- Actions rapides -->
    <div class="db-actions">
        <div class="db-actions-title">Actions rapides</div>
        <div class="db-actions-list">
            <a href="/admin/?module=biens&action=nouveau" class="db-action-btn primary">
                <i class="fas fa-plus"></i> Ajouter un bien
            </a>
            <a href="/admin/?module=capturer" class="db-action-btn">
                <i class="fas fa-user-plus"></i> Nouveau lead
            </a>
            <a href="/admin/?module=biens" class="db-action-btn">
                <i class="fas fa-list"></i> Voir les biens
            </a>
            <a href="/admin/?module=seo" class="db-action-btn">
                <i class="fas fa-chart-line"></i> Visibilité SEO
            </a>
            <a href="/admin/?module=social" class="db-action-btn">
                <i class="fas fa-share-nodes"></i> Réseaux sociaux
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         COACHING — Règles d'or du conseiller
         ══════════════════════════════════════════════ -->
    <style>
    .coaching-header {
        display: flex; align-items: center; justify-content: space-between;
        margin: 32px 0 16px; gap: 12px;
    }
    .coaching-header-left { display: flex; align-items: center; gap: 10px; }
    .coaching-header h2 {
        font-size: 14px; font-weight: 700; color: #2c3e50;
        text-transform: uppercase; letter-spacing: .07em; margin: 0;
    }
    .coaching-badge {
        background: #fef3cd; color: #856404; font-size: 11px;
        font-weight: 700; padding: 3px 9px; border-radius: 999px;
        letter-spacing: .04em;
    }
    .coaching-toggle {
        background: none; border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 5px 12px; font-size: 12px; color: #8a95a3; cursor: pointer;
        display: flex; align-items: center; gap: 5px; transition: all .15s;
        font-family: inherit;
    }
    .coaching-toggle:hover { border-color: #1a3c5e; color: #1a3c5e; }

    .coaching-body { display: block; }
    .coaching-body.hidden { display: none; }

    /* Grille principale coaching */
    .coaching-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 14px;
        margin-bottom: 14px;
    }

    .coaching-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-top: 3px solid #e2e8f0;
    }
    .coaching-card.c-contact { border-top-color: #27ae60; }
    .coaching-card.c-email   { border-top-color: #3498db; }
    .coaching-card.c-social  { border-top-color: #9b59b6; }

    .coaching-card-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #2c3e50;
        margin-bottom: 14px;
    }
    .coaching-card-title .icon {
        font-size: 18px; flex-shrink: 0;
    }

    /* Règles de contact */
    .contact-rules { display: flex; flex-direction: column; gap: 8px; }
    .rule-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 8px;
        font-size: 13px;
    }
    .rule-row.danger  { background: #fef2f2; }
    .rule-row.warning { background: #fffbeb; }
    .rule-row.good    { background: #f0fdf4; }
    .rule-row.great   { background: #eff6ff; }
    .rule-num {
        font-size: 20px; font-weight: 800; min-width: 36px;
        text-align: center; line-height: 1;
    }
    .rule-num.danger  { color: #dc2626; }
    .rule-num.warning { color: #d97706; }
    .rule-num.good    { color: #16a34a; }
    .rule-num.great   { color: #2563eb; }
    .rule-text { flex: 1; color: #374151; line-height: 1.4; }
    .rule-text strong { display: block; font-weight: 700; }
    .rule-text small { color: #9ca3af; font-size: 11px; }

    /* Règles email */
    .email-rules { display: flex; flex-direction: column; gap: 8px; }
    .email-rule {
        border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;
    }
    .email-rule-top {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 4px;
    }
    .email-rule-label { font-size: 12px; font-weight: 700; color: #475569; }
    .email-rule-limit {
        font-size: 13px; font-weight: 800; color: #1a3c5e;
        background: #eef2ff; padding: 2px 8px; border-radius: 999px;
    }
    .email-rule-desc { font-size: 11px; color: #94a3b8; line-height: 1.5; }

    /* Social */
    .social-rules { display: flex; flex-direction: column; gap: 8px; }
    .social-rule {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; background: #f8fafc; border-radius: 8px;
    }
    .social-rule-icon { font-size: 20px; flex-shrink: 0; }
    .social-rule-info { flex: 1; }
    .social-rule-name { font-size: 12px; font-weight: 700; color: #374151; }
    .social-rule-freq {
        font-size: 11px; color: #6b7280;
        background: #e0f2fe; color: #0369a1;
        display: inline-block; padding: 1px 7px; border-radius: 999px;
        font-weight: 600; margin-top: 3px;
    }

    /* Stat Pareto */
    .pareto-box {
        background: linear-gradient(135deg, #0f2237 0%, #1a3c5e 100%);
        color: #fff; border-radius: 14px; padding: 18px 20px;
        margin-bottom: 14px;
        display: flex; align-items: center; gap: 16px;
        box-shadow: 0 4px 16px rgba(15,34,55,.2);
    }
    .pareto-icon { font-size: 32px; flex-shrink: 0; }
    .pareto-text h3 { font-size: 14px; font-weight: 700; color: #c9a84c; margin-bottom: 4px; }
    .pareto-text p  { font-size: 13px; color: rgba(255,255,255,.8); margin: 0; line-height: 1.5; }
    .pareto-text strong { color: #c9a84c; }

    /* Grille basse: funnel + calculateur */
    .coaching-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 900px) {
        .coaching-grid-2 { grid-template-columns: 1fr; }
    }

    /* Funnel email */
    .funnel-card {
        background: #fff; border-radius: 14px; padding: 20px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-top: 3px solid #3498db;
    }
    .funnel-title {
        font-size: 13px; font-weight: 700; color: #2c3e50;
        margin-bottom: 16px; display: flex; align-items: center; gap: 7px;
    }
    .funnel-steps { display: flex; flex-direction: column; gap: 0; }
    .funnel-step {
        display: flex; align-items: center; gap: 12px; position: relative;
    }
    .funnel-step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 18px; top: 42px;
        width: 2px; height: 22px;
        background: #e2e8f0;
        z-index: 0;
    }
    .funnel-dot {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 12px; flex-shrink: 0; z-index: 1;
        position: relative;
    }
    .funnel-info { flex: 1; padding-block: 12px; }
    .funnel-label { font-size: 12px; font-weight: 700; color: #374151; line-height: 1.3; }
    .funnel-rate  { font-size: 11px; color: #9ca3af; }
    .funnel-bar-wrap {
        width: 80px; flex-shrink: 0;
    }
    .funnel-bar {
        height: 6px; border-radius: 999px; background: #e2e8f0;
        overflow: hidden;
    }
    .funnel-bar-fill { height: 100%; border-radius: 999px; }
    .funnel-pct {
        font-size: 14px; font-weight: 800; text-align: right;
        line-height: 1; margin-bottom: 4px;
    }

    /* Calculateur potentiel */
    .calc-card {
        background: #fff; border-radius: 14px; padding: 20px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-top: 3px solid #c9a84c;
    }
    .calc-title {
        font-size: 13px; font-weight: 700; color: #2c3e50;
        margin-bottom: 16px; display: flex; align-items: center; gap: 7px;
    }
    .calc-input-group { margin-bottom: 12px; }
    .calc-input-group label {
        font-size: 11px; font-weight: 700; color: #6b7280;
        text-transform: uppercase; letter-spacing: .05em;
        display: block; margin-bottom: 6px;
    }
    .calc-row { display: flex; align-items: center; gap: 10px; }
    .calc-row input[type=range] {
        flex: 1; accent-color: #1a3c5e; cursor: pointer;
    }
    .calc-val {
        font-size: 18px; font-weight: 800; color: #1a3c5e;
        min-width: 36px; text-align: right;
    }

    .calc-results {
        background: #f8fafc; border-radius: 10px;
        padding: 14px; margin-top: 14px;
    }
    .calc-results-title {
        font-size: 11px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: 10px;
    }
    .calc-result-row {
        display: flex; justify-content: space-between;
        align-items: center; padding-block: 6px;
        border-bottom: 1px solid #e2e8f0; font-size: 12px;
    }
    .calc-result-row:last-child { border-bottom: none; }
    .calc-result-label { color: #6b7280; }
    .calc-result-num { font-weight: 800; color: #1a3c5e; font-size: 15px; }
    .calc-result-num.highlight { color: #c9a84c; font-size: 18px; }

    .calc-alert {
        margin-top: 10px; padding: 8px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 600; text-align: center;
        display: none;
    }
    .calc-alert.show { display: block; }
    .calc-alert.danger  { background: #fef2f2; color: #dc2626; }
    .calc-alert.warning { background: #fffbeb; color: #92400e; }
    .calc-alert.good    { background: #f0fdf4; color: #166534; }

    @media (max-width: 600px) {
        .coaching-grid { grid-template-columns: 1fr; }
        .pareto-box { flex-direction: column; text-align: center; }
    }
    </style>

    <!-- En-tête section coaching -->
    <div class="coaching-header">
        <div class="coaching-header-left">
            <h2>🧠 Règles d'or du conseiller</h2>
            <span class="coaching-badge">Coaching quotidien</span>
        </div>
        <button class="coaching-toggle" id="coaching-toggle" onclick="toggleCoaching()">
            <i class="fas fa-eye-slash" id="coaching-toggle-icon"></i>
            <span id="coaching-toggle-label">Masquer</span>
        </button>
    </div>

    <div class="coaching-body" id="coaching-body">

        <!-- Règle Pareto -->
        <div class="pareto-box">
            <div class="pareto-icon">📊</div>
            <div class="pareto-text">
                <h3>La loi de Pareto appliquée à l'immobilier</h3>
                <p>
                    Statistiquement, il faut <strong>20 contacts nouveaux</strong> pour générer
                    <strong>1 vente</strong>. Si vous parlez à 2 nouvelles personnes par jour pendant 22 jours ouvrés,
                    vous avez vos contacts du mois — et potentiellement <strong>2 à 3 transactions</strong> en vue.
                    La régularité bat le talent.
                </p>
            </div>
        </div>

        <!-- 3 cartes règles -->
        <div class="coaching-grid">

            <!-- Contacts parlés -->
            <div class="coaching-card c-contact">
                <div class="coaching-card-title">
                    <span class="icon">🗣️</span>
                    Contacts parlés (tél / présentiel)
                </div>
                <div class="contact-rules">
                    <div class="rule-row danger">
                        <div class="rule-num danger">0-1</div>
                        <div class="rule-text">
                            <strong>🚨 Business en danger</strong>
                            <small>Votre pipeline se vide. Décrochez le téléphone maintenant.</small>
                        </div>
                    </div>
                    <div class="rule-row warning">
                        <div class="rule-num warning">2</div>
                        <div class="rule-text">
                            <strong>⚠️ Minimum vital</strong>
                            <small>Vous survivez. Mais ce n'est pas ce pour quoi vous êtes là.</small>
                        </div>
                    </div>
                    <div class="rule-row good">
                        <div class="rule-num good">3-4</div>
                        <div class="rule-text">
                            <strong>✅ Bonne cadence</strong>
                            <small>Vous construisez. Le pipeline se remplit correctement.</small>
                        </div>
                    </div>
                    <div class="rule-row great">
                        <div class="rule-num great">5+</div>
                        <div class="rule-text">
                            <strong>🚀 En mode croissance</strong>
                            <small>À ce rythme, votre activité est en avance sur le marché.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emails -->
            <div class="coaching-card c-email">
                <div class="coaching-card-title">
                    <span class="icon">📧</span>
                    Emails — limites à respecter
                </div>
                <div class="email-rules">
                    <div class="email-rule">
                        <div class="email-rule-top">
                            <span class="email-rule-label">🥶 Prospect froid (jamais parlé)</span>
                            <span class="email-rule-limit">max 100/jour</span>
                        </div>
                        <div class="email-rule-desc">
                            Au-delà, vous risquez le blocage de votre domaine.
                            La qualité prime sur le volume. Personnalisez l'objet.
                        </div>
                    </div>
                    <div class="email-rule">
                        <div class="email-rule-top">
                            <span class="email-rule-label">🌡️ Prospection pure (inconnu)</span>
                            <span class="email-rule-limit">20-50/jour</span>
                        </div>
                        <div class="email-rule-desc">
                            Idéalement, ne dépassez pas 50 emails de prospection
                            par jour. La délivrabilité reste protégée.
                        </div>
                    </div>
                    <div class="email-rule">
                        <div class="email-rule-top">
                            <span class="email-rule-label">🔥 Base connue (déjà en contact)</span>
                            <span class="email-rule-limit">sans limite stricte</span>
                        </div>
                        <div class="email-rule-desc">
                            Ces personnes vous connaissent. Le volume peut être plus élevé
                            mais restez pertinent — pas de spam déguisé.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="coaching-card c-social">
                <div class="coaching-card-title">
                    <span class="icon">📱</span>
                    Cadence réseaux &amp; contenu
                </div>
                <div class="social-rules">
                    <div class="social-rule">
                        <div class="social-rule-icon">🗺️</div>
                        <div class="social-rule-info">
                            <div class="social-rule-name">Google My Business</div>
                            <span class="social-rule-freq">1 publication minimum / semaine</span>
                            <div style="font-size:11px;color:#6b7280;margin-top:4px">
                                Signal SEO local fort. Ne pas négliger.
                            </div>
                        </div>
                    </div>
                    <div class="social-rule">
                        <div class="social-rule-icon">📲</div>
                        <div class="social-rule-info">
                            <div class="social-rule-name">Instagram / Facebook / LinkedIn</div>
                            <span class="social-rule-freq">1 à 3 posts max / jour</span>
                            <div style="font-size:11px;color:#6b7280;margin-top:4px">
                                Au-delà, l'algorithme pénalise la portée. La constance bat la fréquence.
                            </div>
                        </div>
                    </div>
                    <div class="social-rule">
                        <div class="social-rule-icon">✍️</div>
                        <div class="social-rule-info">
                            <div class="social-rule-name">Contenu de valeur</div>
                            <span class="social-rule-freq">1 contenu expert / semaine minimum</span>
                            <div style="font-size:11px;color:#6b7280;margin-top:4px">
                                Article, guide, vidéo marché : vous positionnez l'expertise.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.coaching-grid -->

        <!-- Ligne basse : funnel email + calculateur -->
        <div class="coaching-grid-2">

            <!-- Funnel email -->
            <div class="funnel-card">
                <div class="funnel-title">
                    <span>📩</span> Funnel email de prospection (pour 100 envoyés)
                </div>
                <div class="funnel-steps">
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#dbeafe;color:#1d4ed8">100</div>
                        <div class="funnel-info">
                            <div class="funnel-label">Emails envoyés</div>
                            <div class="funnel-rate">Base de départ</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#1d4ed8">100%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:100%;background:#3b82f6"></div></div>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#dcfce7;color:#15803d">80</div>
                        <div class="funnel-info">
                            <div class="funnel-label">Ouvertures (bon objet)</div>
                            <div class="funnel-rate">Si l'objet accroche → taux réel</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#16a34a">80%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:80%;background:#22c55e"></div></div>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#fef9c3;color:#a16207">40</div>
                        <div class="funnel-info">
                            <div class="funnel-label">Ouvrent l'email 2</div>
                            <div class="funnel-rate">50% des ouvreurs restent engagés</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#ca8a04">50%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:50%;background:#eab308"></div></div>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#fce7f3;color:#be185d">16</div>
                        <div class="funnel-info">
                            <div class="funnel-label">Répondent</div>
                            <div class="funnel-rate">20% des ouvreurs passent à l'action</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#db2777">20%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:20%;background:#ec4899"></div></div>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#ede9fe;color:#6d28d9">3</div>
                        <div class="funnel-info">
                            <div class="funnel-label">Prennent RDV</div>
                            <div class="funnel-rate">20% des répondants</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#7c3aed">3%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:3%;background:#8b5cf6"></div></div>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-dot" style="background:#0f2237;color:#c9a84c">1-2</div>
                        <div class="funnel-info">
                            <div class="funnel-label" style="color:#c9a84c;font-weight:800">🏆 Vente(s)</div>
                            <div class="funnel-rate">1 RDV sur 2 = 1 transaction</div>
                        </div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-pct" style="color:#c9a84c">~1,5%</div>
                            <div class="funnel-bar"><div class="funnel-bar-fill" style="width:2%;background:#c9a84c"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculateur potentiel -->
            <div class="calc-card">
                <div class="calc-title">
                    <span>🔮</span> Calculateur de potentiel mensuel
                </div>

                <div class="calc-input-group">
                    <label>Contacts parlés par jour (tél/terrain)</label>
                    <div class="calc-row">
                        <input type="range" id="calc-contacts" min="0" max="20" value="2" oninput="updateCalc()">
                        <div class="calc-val" id="val-contacts">2</div>
                    </div>
                </div>

                <div class="calc-input-group">
                    <label>Emails de prospection par jour</label>
                    <div class="calc-row">
                        <input type="range" id="calc-emails" min="0" max="100" value="20" step="5" oninput="updateCalc()">
                        <div class="calc-val" id="val-emails">20</div>
                    </div>
                </div>

                <div class="calc-results">
                    <div class="calc-results-title">📅 Projection sur 22 jours ouvrés</div>
                    <div class="calc-result-row">
                        <span class="calc-result-label">Contacts parlés</span>
                        <span class="calc-result-num" id="res-contacts">44</span>
                    </div>
                    <div class="calc-result-row">
                        <span class="calc-result-label">Emails envoyés</span>
                        <span class="calc-result-num" id="res-emails">440</span>
                    </div>
                    <div class="calc-result-row">
                        <span class="calc-result-label">RDV estimés (email)</span>
                        <span class="calc-result-num" id="res-rdv">13</span>
                    </div>
                    <div class="calc-result-row">
                        <span class="calc-result-label">🏆 Ventes potentielles</span>
                        <span class="calc-result-num highlight" id="res-ventes">8</span>
                    </div>
                </div>

                <div class="calc-alert" id="calc-alert"></div>

                <p style="font-size:11px;color:#9ca3af;margin-top:12px;margin-bottom:0;line-height:1.5;">
                    * Projection basée sur : 20 contacts parlés = 1 vente,
                    funnel email 80/50/20/20%, 1 RDV sur 2 = 1 vente.
                </p>
            </div>

        </div><!-- /.coaching-grid-2 -->

    </div><!-- /.coaching-body -->

    <script>
    function toggleCoaching() {
        var body  = document.getElementById('coaching-body');
        var icon  = document.getElementById('coaching-toggle-icon');
        var label = document.getElementById('coaching-toggle-label');
        var hidden = body.classList.toggle('hidden');
        icon.className  = hidden ? 'fas fa-eye' : 'fas fa-eye-slash';
        label.textContent = hidden ? 'Afficher' : 'Masquer';
        localStorage.setItem('coaching_hidden', hidden ? '1' : '0');
    }

    // Restaurer l'état depuis localStorage
    (function() {
        if (localStorage.getItem('coaching_hidden') === '1') {
            var body  = document.getElementById('coaching-body');
            var icon  = document.getElementById('coaching-toggle-icon');
            var label = document.getElementById('coaching-toggle-label');
            if (body) { body.classList.add('hidden'); }
            if (icon)  icon.className = 'fas fa-eye';
            if (label) label.textContent = 'Afficher';
        }
    })();

    function updateCalc() {
        var contacts = parseInt(document.getElementById('calc-contacts').value);
        var emails   = parseInt(document.getElementById('calc-emails').value);
        var jours    = 22;

        document.getElementById('val-contacts').textContent = contacts;
        document.getElementById('val-emails').textContent   = emails;

        var totalContacts = contacts * jours;
        var totalEmails   = emails * jours;

        // Ventes via contacts parlés (1 vente / 20 contacts)
        var ventesContacts = totalContacts / 20;

        // Funnel email : 80% open, 20% répondent, 20% RDV, 50% vente
        var rdvEmail    = Math.round(totalEmails * 0.80 * 0.20 * 0.20);
        var ventesEmail = rdvEmail / 2;

        var totalVentes = ventesContacts + ventesEmail;

        document.getElementById('res-contacts').textContent = totalContacts;
        document.getElementById('res-emails').textContent   = totalEmails;
        document.getElementById('res-rdv').textContent      = rdvEmail;
        document.getElementById('res-ventes').textContent   = totalVentes % 1 === 0
            ? totalVentes
            : totalVentes.toFixed(1);

        var alert = document.getElementById('calc-alert');
        alert.className = 'calc-alert';
        if (contacts === 0 && emails === 0) {
            alert.textContent = '🚨 Zéro contact, zéro email. Business à l\'arrêt.';
            alert.classList.add('show', 'danger');
        } else if (contacts < 2 && emails < 20) {
            alert.textContent = '⚠️ Cadence insuffisante. Votre pipeline va se vider dans quelques semaines.';
            alert.classList.add('show', 'warning');
        } else if (totalVentes >= 3) {
            alert.textContent = '🚀 Excellente cadence ! Vous êtes sur la trajectoire d\'un top performer.';
            alert.classList.add('show', 'good');
        } else {
            void alert.offsetWidth;
        }
    }

    // Init au chargement
    updateCalc();
    </script>
    <?php
}
