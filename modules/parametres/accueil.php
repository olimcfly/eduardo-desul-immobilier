<?php
// ============================================================
// MODULE PARAMÈTRES — Accueil / Hub
// ============================================================
require_once __DIR__ . '/../../includes/settings.php';

$pageTitle       = 'Paramètres';
$pageDescription = 'Compte et préférences';

function renderContent(): void
{
    $profil_prenom = setting('profil_prenom', '');
    $profil_nom    = setting('profil_nom', defined('ADVISOR_NAME') ? ADVISOR_NAME : '');
    $profil_email  = setting('profil_email', defined('APP_EMAIL') ? APP_EMAIL : '');
    $profil_ville  = setting('profil_ville', defined('APP_CITY') ? APP_CITY : '');
    $profil_photo  = setting('profil_photo', '');

    // Vérification clés API configurées
    $apis = [
        'openai'      => (bool) setting('api_openai'),
        'openrouter'  => (bool) (trim((string) setting('api_openrouter', '')) !== '' || trim((string) ($_ENV['OPENROUTER_API_KEY'] ?? '')) !== ''),
        'google_maps' => (bool) setting('api_google_maps'),
        'gmb'         => (bool) setting('api_gmb_client_id'),
        'facebook'    => (bool) setting('api_fb_access_token'),
        'cloudinary'  => (bool) setting('api_cloudinary_key'),
        'gsc'         => (bool) setting('api_gsc_refresh_token'),
    ];
    $apis_ok    = array_sum($apis);
    $apis_total = count($apis);
    ?>

    <!-- ── EN-TÊTE ─────────────────────────────────────────── -->
    <div class="start-hero">
        <div class="start-hero-badge">Configuration</div>
        <h1>Paramètres</h1>
        <p><?= htmlspecialchars($pageDescription ?? '') ?>, profil, site public, intégrations et sécurité.</p>
    </div>

    <!-- ── PROFIL RÉSUMÉ ───────────────────────────────────── -->
    <div class="settings-profile-banner">
        <div class="spb-avatar">
            <?php if ($profil_photo): ?>
                <img src="<?= htmlspecialchars($profil_photo) ?>" alt="Photo profil">
            <?php else: ?>
                <span><?= strtoupper(substr($profil_nom, 0, 2)) ?></span>
            <?php endif; ?>
        </div>
        <div class="spb-info">
            <div class="spb-name"><?= htmlspecialchars(trim($profil_prenom . ' ' . $profil_nom)) ?></div>
            <div class="spb-email"><?= htmlspecialchars($profil_email) ?></div>
            <div class="spb-location"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($profil_ville) ?></div>
        </div>
        <div class="spb-status">
            <div class="spb-api-badge <?= $apis_ok === $apis_total ? 'badge-ok' : 'badge-warn' ?>">
                <i class="fas fa-plug"></i>
                <?= $apis_ok ?>/<?= $apis_total ?> APIs configurées
            </div>
        </div>
    </div>

    <!-- ── GRILLE MODULES ──────────────────────────────────── -->
    <div class="settings-grid">

        <!-- Profil -->
        <div class="settings-card" onclick="loadSettingsSection('profil')">
            <div class="sc-icon" style="background:#e3f2fd; color:#1976d2">
                <i class="fas fa-user-gear"></i>
            </div>
            <div class="sc-body">
                <h3>Mon profil</h3>
                <p>Nom, prénom, photo, bio, carte professionnelle, réseau.</p>
                <div class="sc-tags">
                    <span class="sc-tag">Identité</span>
                    <span class="sc-tag">Photo</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Site public -->
        <div class="settings-card" onclick="loadSettingsSection('site')">
            <div class="sc-icon" style="background:#e8f5e9; color:#388e3c">
                <i class="fas fa-globe"></i>
            </div>
            <div class="sc-body">
                <h3>Site public</h3>
                <p>Nom du site, slogan, logo, couleur principale, favicon.</p>
                <div class="sc-tags">
                    <span class="sc-tag">Branding</span>
                    <span class="sc-tag">Logo</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Zone géographique -->
        <div class="settings-card" onclick="loadSettingsSection('zone')">
            <div class="sc-icon" style="background:#fff3e0; color:#f57c00">
                <i class="fas fa-map-location-dot"></i>
            </div>
            <div class="sc-body">
                <h3>Zone géographique</h3>
                <p>Ville principale, communes couvertes, rayon d'intervention.</p>
                <div class="sc-tags">
                    <span class="sc-tag">Localisation</span>
                    <span class="sc-tag">SEO local</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Intégrations API -->
        <div class="settings-card" onclick="loadSettingsSection('api')">
            <div class="sc-icon" style="background:#fce4ec; color:#c62828">
                <i class="fas fa-plug-circle-bolt"></i>
            </div>
            <div class="sc-body">
                <h3>Intégrations & API</h3>
                <p>OpenAI, OpenRouter, Google Maps, GMB, Facebook, Cloudinary, GSC.</p>
                <div class="sc-tags">
                    <span class="sc-tag">API</span>
                    <span class="sc-tag <?= $apis_ok < $apis_total ? 'sc-tag-warn' : 'sc-tag-ok' ?>">
                        <?= $apis_ok ?>/<?= $apis_total ?> actives
                    </span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Notifications -->
        <div class="settings-card" onclick="loadSettingsSection('notif')">
            <div class="sc-icon" style="background:#ede7f6; color:#6a1b9a">
                <i class="fas fa-bell"></i>
            </div>
            <div class="sc-body">
                <h3>Notifications</h3>
                <p>Emails reçus pour les contacts, estimations, avis et alertes.</p>
                <div class="sc-tags">
                    <span class="sc-tag">Email</span>
                    <span class="sc-tag">Alertes</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- SMTP -->
        <div class="settings-card" onclick="loadSettingsSection('smtp')">
            <div class="sc-icon" style="background:#e0f7fa; color:#00695c">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="sc-body">
                <h3>Email & SMTP</h3>
                <p>Serveur d'envoi, expéditeur, configuration TLS/SSL.</p>
                <div class="sc-tags">
                    <span class="sc-tag">SMTP</span>
                    <span class="sc-tag">Mailing</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Bot Telegram -->
        <div class="settings-card" onclick="loadSettingsSection('telegram')">
            <div class="sc-icon" style="background:#e3f2fd; color:#0088cc">
                <i class="fab fa-telegram"></i>
            </div>
            <div class="sc-body">
                <h3>Bot Telegram</h3>
                <p>Configuration du bot, token, users approuvés, historique.</p>
                <div class="sc-tags">
                    <span class="sc-tag">Bot</span>
                    <span class="sc-tag">Mobile</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Sécurité -->
        <div class="settings-card" onclick="loadSettingsSection('securite')">
            <div class="sc-icon" style="background:#ffebee; color:#b71c1c">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div class="sc-body">
                <h3>Sécurité</h3>
                <p>Mot de passe, double authentification OTP, sessions.</p>
                <div class="sc-tags">
                    <span class="sc-tag">2FA</span>
                    <span class="sc-tag">Accès</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

        <!-- Danger zone -->
        <div class="settings-card settings-card-danger" onclick="loadSettingsSection('danger')">
            <div class="sc-icon" style="background:#fff8e1; color:#e65100">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="sc-body">
                <h3>Zone de danger</h3>
                <p>Export des données, réinitialisation, suppression du compte.</p>
                <div class="sc-tags">
                    <span class="sc-tag sc-tag-danger">Irréversible</span>
                </div>
            </div>
            <div class="sc-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>

    </div>

    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Priorité de configuration</strong>
            <span>Commencez par le profil et les intégrations API pour garantir le bon fonctionnement des modules.</span>
        </div>
        <button type="button" class="start-cta-btn" onclick="loadSettingsSection('profil')">
            <i class="fas fa-user-gear"></i> Ouvrir le profil
        </button>
    </div>

    <!-- ── PANNEAU LATÉRAL (drawer) ─────────────────────────── -->
    <div class="settings-drawer" id="settings-drawer">
        <div class="drawer-backdrop" onclick="closeSettingsDrawer()"></div>
        <div class="drawer-panel" id="drawer-panel">
            <div class="drawer-header">
                <button class="drawer-close" onclick="closeSettingsDrawer()">
                    <i class="fas fa-times"></i>
                </button>
                <h2 class="drawer-title" id="drawer-title">Paramètres</h2>
            </div>
            <div class="drawer-body" id="drawer-body">
                <div class="drawer-loading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement…
                </div>
            </div>
        </div>
    </div>

    <style>
    /* ── HERO ─────────────────────────────────────────────── */
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
        max-width: 680px;
        margin: 0;
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
        margin: 28px 0;
    }
    .start-cta-text strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .start-cta-text span { font-size: 13px; color: #64748b; }
    .start-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        background: #c9a84c;
        color: #0f2237;
        border: 0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    /* ── PROFIL BANNER ────────────────────────────────────── */
    .settings-profile-banner {
        background: linear-gradient(135deg, #1a2332, #2c3e50);
        border-radius: 14px;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 28px;
        box-shadow: 0 4px 20px rgba(26,35,50,.15);
    }
    .spb-avatar {
        width: 64px; height: 64px; border-radius: 50%;
        background: #3498db;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700; color: white;
        flex-shrink: 0; overflow: hidden;
        border: 3px solid rgba(255,255,255,.15);
    }
    .spb-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .spb-info { flex: 1; }
    .spb-name  { font-size: 18px; font-weight: 700; color: white; }
    .spb-email { font-size: 13px; color: rgba(255,255,255,.55); margin-top: 3px; }
    .spb-location { font-size: 12px; color: rgba(255,255,255,.4); margin-top: 4px; }
    .spb-location i { margin-right: 4px; color: #3498db; }
    .spb-api-badge {
        padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
        display: flex; align-items: center; gap: 6px;
    }
    .badge-ok   { background: rgba(46,204,113,.2);  color: #2ecc71; }
    .badge-warn { background: rgba(230,126,34,.2);  color: #f39c12; }

    /* ── SETTINGS GRID ────────────────────────────────────── */
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }
    .settings-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        cursor: pointer;
        border: 1px solid #e8ecf0;
        transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    .settings-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.08);
        border-color: #3498db;
    }
    .settings-card-danger:hover { border-color: #e74c3c; }
    .sc-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .sc-body { flex: 1; min-width: 0; }
    .sc-body h3 { font-size: 14px; font-weight: 700; color: #1a2332; margin-bottom: 4px; }
    .sc-body p  { font-size: 12px; color: #7f8c8d; line-height: 1.4; }
    .sc-tags { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
    .sc-tag {
        font-size: 10px; font-weight: 600; padding: 2px 8px;
        border-radius: 10px; background: #f0f4f8; color: #5a6a7a;
        text-transform: uppercase; letter-spacing: .03em;
    }
    .sc-tag-ok   { background: #e8f5e9; color: #2e7d32; }
    .sc-tag-warn { background: #fff3e0; color: #e65100; }
    .sc-tag-danger { background: #ffebee; color: #c62828; }
    .sc-arrow { color: #bdc3c7; font-size: 12px; flex-shrink: 0; }

    /* ── DRAWER ───────────────────────────────────────────── */
    .settings-drawer {
        position: fixed; inset: 0; z-index: 9999;
        pointer-events: none; opacity: 0;
        transition: opacity .25s;
    }
    .settings-drawer.open {
        pointer-events: all; opacity: 1;
    }
    .drawer-backdrop {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.45);
        backdrop-filter: blur(2px);
    }
    .drawer-panel {
        position: absolute; top: 0; right: 0; bottom: 0;
        width: 560px; max-width: 95vw;
        background: white;
        display: flex; flex-direction: column;
        transform: translateX(100%);
        transition: transform .3s cubic-bezier(.4,0,.2,1);
        box-shadow: -8px 0 40px rgba(0,0,0,.15);
    }
    .settings-drawer.open .drawer-panel { transform: translateX(0); }
    .drawer-header {
        display: flex; align-items: center; gap: 14px;
        padding: 20px 24px; border-bottom: 1px solid #e8ecf0;
        flex-shrink: 0;
    }
    .drawer-close {
        width: 32px; height: 32px; border-radius: 8px;
        border: 1px solid #e8ecf0; background: white;
        color: #7f8c8d; cursor: pointer; font-size: 14px;
        transition: background .15s, color .15s;
        display: flex; align-items: center; justify-content: center;
    }
    .drawer-close:hover { background: #ffebee; color: #e74c3c; }
    .drawer-title { font-size: 16px; font-weight: 700; color: #1a2332; }
    .drawer-body {
        flex: 1; overflow-y: auto; padding: 24px;
    }
    .drawer-loading {
        display: flex; align-items: center; justify-content: center;
        height: 200px; color: #7f8c8d; gap: 10px; font-size: 14px;
    }

    /* ── FORMS (dans le drawer) ───────────────────────────── */
    .settings-form .form-group { margin-bottom: 20px; }
    .settings-form label {
        display: block; font-size: 13px; font-weight: 600;
        color: #2c3e50; margin-bottom: 6px;
    }
    .settings-form label .label-hint {
        font-weight: 400; color: #95a5a6; font-size: 12px; margin-left: 6px;
    }
    .settings-form input[type=text],
    .settings-form input[type=email],
    .settings-form input[type=url],
    .settings-form input[type=number],
    .settings-form input[type=password],
    .settings-form textarea,
    .settings-form select {
        width: 100%; padding: 10px 14px;
        border: 1px solid #dde1e7; border-radius: 8px;
        font-size: 13px; font-family: inherit; color: #2c3e50;
        background: #f8fafc; outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .settings-form input:focus,
    .settings-form textarea:focus,
    .settings-form select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52,152,219,.12);
        background: white;
    }
    .settings-form input.input-error { border-color: #e74c3c; }
    .settings-form textarea { resize: vertical; min-height: 80px; }
    .settings-form .form-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
    }
    .settings-form .form-section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #7f8c8d;
        border-bottom: 1px solid #e8ecf0;
        padding-bottom: 8px; margin: 24px 0 16px;
    }

    /* Toggle switch */
    .toggle-group {
        display: flex; align-items: center;
        justify-content: space-between;
        padding: 12px 0; border-bottom: 1px solid #f0f4f8;
    }
    .toggle-group:last-child { border-bottom: none; }
    .toggle-label { font-size: 13px; color: #2c3e50; }
    .toggle-hint  { font-size: 11px; color: #95a5a6; margin-top: 2px; }
    .toggle-switch { position: relative; width: 40px; height: 22px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background: #dde1e7; border-radius: 22px;
        transition: background .2s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 16px; height: 16px; border-radius: 50%;
        left: 3px; top: 3px; background: white;
        transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .toggle-switch input:checked + .toggle-slider { background: #3498db; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

    /* API key input row */
    .api-key-row { position: relative; }
    .api-key-row input { padding-right: 42px; font-family: monospace; font-size: 12px; }
    .api-key-toggle {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: #95a5a6; cursor: pointer;
        font-size: 14px; padding: 0;
    }
    .api-key-toggle:hover { color: #3498db; }
    .api-status-dot {
        display: inline-block; width: 8px; height: 8px;
        border-radius: 50%; margin-right: 6px;
    }
    .dot-ok   { background: #2ecc71; }
    .dot-warn { background: #e67e22; }
    .dot-off  { background: #bdc3c7; }

    /* Boutons du drawer */
    .drawer-footer {
        padding: 16px 24px; border-top: 1px solid #e8ecf0;
        display: flex; gap: 10px; justify-content: flex-end;
        flex-shrink: 0;
    }
    .btn-save {
        padding: 10px 24px; background: #3498db; color: white;
        border: none; border-radius: 8px; font-size: 13px;
        font-weight: 600; cursor: pointer; transition: background .15s;
        display: flex; align-items: center; gap: 8px;
    }
    .btn-save:hover { background: #2980b9; }
    .btn-save:disabled { background: #bdc3c7; cursor: not-allowed; }
    .btn-cancel {
        padding: 10px 20px; background: white; color: #7f8c8d;
        border: 1px solid #dde1e7; border-radius: 8px; font-size: 13px;
        cursor: pointer; transition: background .15s;
    }
    .btn-cancel:hover { background: #f5f7fa; }

    /* Toast */
    .settings-toast {
        position: fixed; bottom: 28px; right: 28px; z-index: 99999;
        padding: 12px 20px; border-radius: 10px;
        font-size: 13px; font-weight: 600; color: white;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,.15);
        transform: translateY(20px); opacity: 0;
        transition: transform .25s, opacity .25s;
    }
    .settings-toast.show { transform: translateY(0); opacity: 1; }
    .toast-success { background: #27ae60; }
    .toast-error   { background: #e74c3c; }

    @media (max-width: 640px) {
        .start-hero { padding: 24px 20px; }
        .settings-grid { grid-template-columns: 1fr; }
        .settings-profile-banner { flex-direction: column; text-align: center; }
    }
    </style>

    <script>
    // ── DRAWER ─────────────────────────────────────────────────
    const drawer = document.getElementById('settings-drawer');
    const drawerBody = document.getElementById('drawer-body');
    const drawerTitle = document.getElementById('drawer-title');

    const SECTION_TITLES = {
        profil: 'Mon profil',
        site: 'Site public',
        zone: 'Zone géographique',
        api: 'Intégrations & API',
        notif: 'Notifications',
        smtp: 'Email & SMTP',
        telegram: 'Bot Telegram',
        securite: 'Sécurité',
        danger: 'Zone de danger',
    };

    function loadSettingsSection(section) {
        drawerTitle.textContent = SECTION_TITLES[section] || section;
        drawerBody.innerHTML = '<div class="drawer-loading"><i class="fas fa-spinner fa-spin"></i> Chargement…</div>';
        drawer.classList.add('open');
        document.body.style.overflow = 'hidden';

        fetch('/admin/api/settings/load.php?section=' + encodeURIComponent(section))
            .then(r => r.text())
            .then(html => {
                drawerBody.innerHTML = html;
                initDrawerJs(section);
            })
            .catch(() => {
                drawerBody.innerHTML = '<p style="color:#e74c3c;padding:20px">Erreur de chargement.</p>';
            });
    }

    function closeSettingsDrawer() {
        drawer.classList.remove('open');
        document.body.style.overflow = '';
    }

    function initDrawerJs(section) {
        // Toggle visibilité clés API
        document.querySelectorAll('.api-key-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const inp = btn.previousElementSibling;
                if (inp.type === 'password') {
                    inp.type = 'text';
                    btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    inp.type = 'password';
                    btn.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
    }

    // Soumission AJAX générique
    document.addEventListener('submit', function(e) {
        if (!e.target.classList.contains('settings-form')) return;
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('.btn-save');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement…';
        }

        fetch('/admin/api/settings/save.php', {
            method: 'POST',
            body: new FormData(form),
        })
            .then(r => r.json())
            .then(data => {
                showToast(data.success ? data.message || 'Enregistré !' : data.error || 'Erreur', data.success ? 'success' : 'error');
            })
            .catch(() => showToast('Erreur réseau', 'error'))
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Enregistrer';
                }
            });
    });

    function showToast(msg, type = 'success') {
        let t = document.querySelector('.settings-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'settings-toast';
            document.body.appendChild(t);
        }
        t.className = 'settings-toast toast-' + type;
        t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.classList.remove('show'), 3500);
    }

    // ESC ferme le drawer
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeSettingsDrawer();
    });
    </script>
    <?php
}
