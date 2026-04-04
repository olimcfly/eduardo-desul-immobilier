<?php
declare(strict_types=1);

$s = settings_group('api');
$v = fn(string $k) => htmlspecialchars($s[$k] ?? '');

$status = fn(string $k) => !empty($s[$k])
    ? '<span class="api-status-dot dot-ok"></span>Configurée'
    : '<span class="api-status-dot dot-off"></span>Non configurée';

$gscConnected = !empty($s['api_gsc_refresh_token']);
$redirectUri  = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
              . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
              . '/admin/api/settings/gsc-callback.php';
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="api">

    <!-- ── OpenAI ─────────────────────────────────────────── -->
    <div class="form-section-title">
        <i class="fas fa-robot" style="color:#10a37f"></i> OpenAI
        <small style="float:right;font-weight:400"><?= $status('api_openai') ?></small>
    </div>
    <div class="form-group">
        <label>Clé API OpenAI</label>
        <div class="api-key-row">
            <input type="password" name="api_openai"
                   value="<?= $v('api_openai') ?>" placeholder="sk-…">
            <button type="button" class="api-key-toggle">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <!-- ── Google Maps + PSI ──────────────────────────────── -->
    <div class="form-section-title">
        <i class="fab fa-google" style="color:#4285f4"></i> Google
        <small style="float:right;font-weight:400"><?= $status('api_google_maps') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Clé Google Maps</label>
            <div class="api-key-row">
                <input type="password" name="api_google_maps"
                       value="<?= $v('api_google_maps') ?>" placeholder="AIza…">
                <button type="button" class="api-key-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="form-group">
            <label>Clé PageSpeed (PSI)</label>
            <div class="api-key-row">
                <input type="password" name="api_google_psi"
                       value="<?= $v('api_google_psi') ?>" placeholder="AIza…">
                <button type="button" class="api-key-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Google My Business ─────────────────────────────── -->
    <div class="form-section-title">
        <i class="fas fa-store" style="color:#fbbc04"></i> Google My Business
        <small style="float:right;font-weight:400"><?= $status('api_gmb_client_id') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Client ID OAuth</label>
            <input type="text" name="api_gmb_client_id"
                   value="<?= $v('api_gmb_client_id') ?>">
        </div>
        <div class="form-group">
            <label>Client Secret OAuth</label>
            <div class="api-key-row">
                <input type="password" name="api_gmb_client_secret"
                       value="<?= $v('api_gmb_client_secret') ?>">
                <button type="button" class="api-key-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Account ID <span class="label-hint">accounts/XXXXXXXXX</span></label>
        <input type="text" name="api_gmb_account_id"
               value="<?= $v('api_gmb_account_id') ?>"
               placeholder="accounts/123456789">
    </div>

    <!-- ── Facebook / Instagram ───────────────────────────── -->
    <div class="form-section-title">
        <i class="fab fa-facebook" style="color:#1877f2"></i> Facebook & Instagram
        <small style="float:right;font-weight:400"><?= $status('api_fb_access_token') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Page ID Facebook</label>
            <input type="text" name="api_fb_page_id"
                   value="<?= $v('api_fb_page_id') ?>" placeholder="123456789">
        </div>
        <div class="form-group">
            <label>Instagram Account ID</label>
            <input type="text" name="api_instagram_id"
                   value="<?= $v('api_instagram_id') ?>" placeholder="17841…">
        </div>
    </div>
    <div class="form-group">
        <label>Access Token permanent</label>
        <div class="api-key-row">
            <input type="password" name="api_fb_access_token"
                   value="<?= $v('api_fb_access_token') ?>">
            <button type="button" class="api-key-toggle">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <!-- ── Cloudinary ─────────────────────────────────────── -->
    <div class="form-section-title">
        <i class="fas fa-cloud-arrow-up" style="color:#3448c5"></i> Cloudinary
        <small style="float:right;font-weight:400"><?= $status('api_cloudinary_key') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Cloud Name</label>
            <input type="text" name="api_cloudinary_name"
                   value="<?= $v('api_cloudinary_name') ?>" placeholder="my-cloud">
        </div>
        <div class="form-group">
            <label>API Key</label>
            <input type="text" name="api_cloudinary_key"
                   value="<?= $v('api_cloudinary_key') ?>">
        </div>
    </div>
    <div class="form-group">
        <label>API Secret</label>
        <div class="api-key-row">
            <input type="password" name="api_cloudinary_secret"
                   value="<?= $v('api_cloudinary_secret') ?>">
            <button type="button" class="api-key-toggle">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <!-- ── DataForSEO ─────────────────────────────────────── -->
    <div class="form-section-title" style="margin-top:2rem">
        <i class="fas fa-magnifying-glass-chart" style="color:#6366f1"></i> DataForSEO
        <small style="float:right;font-weight:400"><?= $status('api_dataforseo_login') ?></small>
    </div>
    <div class="api-help-banner">
        <i class="fas fa-circle-info"></i>
        <div>
            <strong>API Key DataForSEO</strong><br>
            <span>
                Créez un compte sur
                <a href="https://app.dataforseo.com/register" target="_blank" rel="noopener">app.dataforseo.com</a>
                — authentification par <strong>login + mot de passe API</strong> (Basic Auth).<br>
                Coût indicatif : ~5$ pour 8 000 requêtes SERP.
            </span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Login (email)</label>
            <input type="email" name="api_dataforseo_login"
                   value="<?= $v('api_dataforseo_login') ?>"
                   placeholder="vous@email.com">
        </div>
        <div class="form-group">
            <label>Mot de passe API</label>
            <div class="api-key-row">
                <input type="password" name="api_dataforseo_password"
                       value="<?= $v('api_dataforseo_password') ?>"
                       placeholder="Mot de passe API…">
                <button type="button" class="api-key-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <button type="button" class="btn-oauth btn-test"
                id="btn-dfs-test" onclick="testDataForSEO()">
            <i class="fas fa-plug"></i> Tester la connexion
        </button>
        <span id="dfs-test-result" style="margin-left:10px;font-size:.85rem"></span>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel"
                onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save">
            <i class="fas fa-check"></i> Enregistrer
        </button>
    </div>
</form>

<!-- ══════════════════════════════════════════════════════════
     Google Search Console — OAuth2 (hors <form> principal)
════════════════════════════════════════════════════════════ -->
<div class="gsc-section">
    <div class="form-section-title" style="margin-top:2rem">
        <i class="fas fa-chart-line" style="color:#34a853"></i> Google Search Console
        <small style="float:right;font-weight:400">
            <?= $gscConnected
                ? '<span class="api-status-dot dot-ok"></span>Connecté'
                : '<span class="api-status-dot dot-off"></span>Non connecté' ?>
        </small>
    </div>

    <!-- Aide configuration -->
    <div class="api-help-banner">
        <i class="fas fa-circle-info"></i>
        <div>
            <strong>Configuration OAuth2 requise</strong><br>
            <span>
                1. <a href="https://console.cloud.google.com/apis/credentials"
                      target="_blank" rel="noopener">console.cloud.google.com</a>
                → Créer un <em>ID client OAuth</em> (application Web)<br>
                2. Ajouter l'URI de redirection autorisée :
                <code><?= htmlspecialchars($redirectUri) ?></code><br>
                3. Activer l'API <strong>Search Console API</strong> dans la bibliothèque.
            </span>
        </div>
    </div>

    <?php if ($gscConnected): ?>

        <!-- ── Compte connecté ──────────────────────────── -->
        <div class="gsc-oauth-status">
            <span class="oauth-badge badge-ok">
                <i class="fas fa-check-circle"></i> Compte connecté
            </span>
            <a href="/admin/api/settings/gsc-callback.php?action=revoke"
               class="btn-oauth btn-revoke"
               onclick="return confirm('Révoquer la connexion GSC ?')">
                <i class="fas fa-unlink"></i> Déconnecter
            </a>
        </div>

        <!-- Site URL modifiable même si connecté -->
        <form class="settings-form" method="post" style="margin-top:1rem">
            <input type="hidden" name="section" value="api">
            <div class="form-group">
                <label>Site URL dans GSC
                    <span class="label-hint">ex: sc-domain:monsite.fr</span>
                </label>
                <input type="text" name="api_gsc_site_url"
                       value="<?= $v('api_gsc_site_url') ?>"
                       placeholder="sc-domain:monsite.fr">
            </div>
            <button type="submit" class="btn-oauth btn-connect" style="margin-top:.5rem">
                <i class="fas fa-save"></i> Mettre à jour l'URL
            </button>
        </form>

    <?php else: ?>

        <!-- ── Non connecté : saisie credentials + bouton OAuth ── -->
        <div class="gsc-oauth-status" style="margin-bottom:1rem">
            <span class="oauth-badge badge-off">
                <i class="fas fa-circle-xmark"></i> Non connecté
            </span>
        </div>

        <form class="settings-form" method="post">
            <input type="hidden" name="section" value="api">
            <div class="form-row">
                <div class="form-group">
                    <label>Client ID OAuth</label>
                    <input type="text" name="api_gsc_client_id"
                           value="<?= $v('api_gsc_client_id') ?>"
                           placeholder="XXXX.apps.googleusercontent.com">
                </div>
                <div class="form-group">
                    <label>Client Secret</label>
                    <div class="api-key-row">
                        <input type="password" name="api_gsc_client_secret"
                               value="<?= $v('api_gsc_client_secret') ?>"
                               placeholder="GOCSPX-…">
                        <button type="button" class="api-key-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Site URL dans GSC
                    <span class="label-hint">ex: sc-domain:monsite.fr</span>
                </label>
                <input type="text" name="api_gsc_site_url"
                       value="<?= $v('api_gsc_site_url') ?>"
                       placeholder="sc-domain:monsite.fr">
            </div>

            <div style="display:flex;gap:1rem;align-items:center;margin-top:.75rem">
                <button type="submit" class="btn-oauth btn-test">
                    <i class="fas fa-save"></i> Enregistrer les credentials
                </button>
                <a href="/admin/api/settings/gsc-auth.php"
                   class="btn-oauth btn-connect"
                   id="btn-gsc-connect">
                    <i class="fab fa-google"></i> Connecter Google
                </a>
            </div>
        </form>

    <?php endif; ?>
</div>

<!-- ── Styles ─────────────────────────────────────────────── -->
<style>
.api-help-banner {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    background: var(--bg-secondary, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-left: 3px solid #6366f1;
    border-radius: 8px;
    padding: .85rem 1rem;
    margin-bottom: 1rem;
    font-size: .82rem;
    line-height: 1.6;
    color: var(--text-secondary, #64748b);
}
.api-help-banner > i { margin-top:2px; color:#6366f1; flex-shrink:0; }
.api-help-banner a   { color:#6366f1; text-decoration:underline; }
.api-help-banner code {
    background: var(--bg-tertiary, #f1f5f9);
    padding: 1px 5px;
    border-radius: 4px;
    font-size: .78rem;
    word-break: break-all;
}
.gsc-section { margin-top: .5rem; }
.gsc-oauth-status {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: .75rem;
}
.oauth-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .82rem;
    font-weight: 600;
    padding: .3rem .75rem;
    border-radius: 20px;
}
.badge-ok  { background:#dcfce7; color:#16a34a; }
.badge-off { background:#fee2e2; color:#dc2626; }
.btn-oauth {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .9rem;
    border-radius: 6px;
    font-size: .83rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity .2s;
}
.btn-oauth:hover { opacity:.85; }
.btn-connect { background:#4285f4; color:#fff; }
.btn-revoke  { background:#fee2e2; color:#dc2626; }
.btn-test    { background:#6366f1; color:#fff; }
</style>

<!-- ── Script test DataForSEO ─────────────────────────────── -->
<script>
function testDataForSEO() {
    const btn    = document.getElementById('btn-dfs-test');
    const result = document.getElementById('dfs-test-result');
    const login  = document.querySelector('[name="api_dataforseo_login"]').value.trim();
    const pass   = document.querySelector('[name="api_dataforseo_password"]').value.trim();

    if (!login || !pass) {
        result.innerHTML = '<span style="color:#dc2626">⚠️ Remplis login + mot de passe d\'abord.</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test…';
    result.innerHTML = '';

    fetch('/admin/api/settings/dataforseo-test.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ login, password: pass }),
    })
    .then(r => r.json())
    .then(d => {
        result.innerHTML = d.success
            ? `<span style="color:#16a34a"><i class="fas fa-check-circle"></i> Connecté — Solde : ${d.balance ?? '?'}$</span>`
            : `<span style="color:#dc2626"><i class="fas fa-circle-xmark"></i> ${d.error ?? 'Erreur'}</span>`;
    })
    .catch(() => {
        result.innerHTML = '<span style="color:#dc2626">Erreur réseau</span>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Tester la connexion';
    });
}
</script>
