<?php
$s = settings_group('api');
$v = fn(string $k) => htmlspecialchars($s[$k] ?? '');

$status = fn(string $k) => !empty($s[$k])
    ? '<span class="api-status-dot dot-ok"></span>Configurée'
    : '<span class="api-status-dot dot-off"></span>Non configurée';
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="api">

    <!-- OpenAI -->
    <div class="form-section-title">
        <i class="fas fa-robot" style="color:#10a37f"></i> OpenAI
        <small style="float:right;font-weight:400"><?= $status('api_openai') ?></small>
    </div>
    <div class="form-group">
        <label>Clé API OpenAI</label>
        <div class="api-key-row">
            <input type="password" name="api_openai" value="<?= $v('api_openai') ?>" placeholder="sk-…">
            <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <!-- Google -->
    <div class="form-section-title">
        <i class="fab fa-google" style="color:#4285f4"></i> Google
        <small style="float:right;font-weight:400"><?= $status('api_google_maps') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Clé Google Maps</label>
            <div class="api-key-row">
                <input type="password" name="api_google_maps" value="<?= $v('api_google_maps') ?>" placeholder="AIza…">
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="form-group">
            <label>Clé PageSpeed (PSI)</label>
            <div class="api-key-row">
                <input type="password" name="api_google_psi" value="<?= $v('api_google_psi') ?>" placeholder="AIza…">
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Clé Search Console (GSC)</label>
        <div class="api-key-row">
            <input type="password" name="api_gsc" value="<?= $v('api_gsc') ?>" placeholder="Clé de service…">
            <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <!-- Google My Business -->
    <div class="form-section-title">
        <i class="fas fa-store" style="color:#fbbc04"></i> Google My Business
        <small style="float:right;font-weight:400"><?= $status('api_gmb_client_id') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Client ID OAuth</label>
            <input type="text" name="api_gmb_client_id" value="<?= $v('api_gmb_client_id') ?>">
        </div>
        <div class="form-group">
            <label>Client Secret OAuth</label>
            <div class="api-key-row">
                <input type="password" name="api_gmb_client_secret" value="<?= $v('api_gmb_client_secret') ?>">
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Account ID <span class="label-hint">accounts/XXXXXXXXX</span></label>
        <input type="text" name="api_gmb_account_id" value="<?= $v('api_gmb_account_id') ?>" placeholder="accounts/123456789">
    </div>

    <!-- Facebook / Instagram -->
    <div class="form-section-title">
        <i class="fab fa-facebook" style="color:#1877f2"></i> Facebook & Instagram
        <small style="float:right;font-weight:400"><?= $status('api_fb_access_token') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Page ID Facebook</label>
            <input type="text" name="api_fb_page_id" value="<?= $v('api_fb_page_id') ?>" placeholder="123456789">
        </div>
        <div class="form-group">
            <label>Instagram Account ID</label>
            <input type="text" name="api_instagram_id" value="<?= $v('api_instagram_id') ?>" placeholder="17841…">
        </div>
    </div>
    <div class="form-group">
        <label>Access Token permanent</label>
        <div class="api-key-row">
            <input type="password" name="api_fb_access_token" value="<?= $v('api_fb_access_token') ?>">
            <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <!-- Cloudinary -->
    <div class="form-section-title">
        <i class="fas fa-cloud-arrow-up" style="color:#3448c5"></i> Cloudinary
        <small style="float:right;font-weight:400"><?= $status('api_cloudinary_key') ?></small>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Cloud Name</label>
            <input type="text" name="api_cloudinary_name" value="<?= $v('api_cloudinary_name') ?>" placeholder="my-cloud">
        </div>
        <div class="form-group">
            <label>API Key</label>
            <input type="text" name="api_cloudinary_key" value="<?= $v('api_cloudinary_key') ?>">
        </div>
    </div>
    <div class="form-group">
        <label>API Secret</label>
        <div class="api-key-row">
            <input type="password" name="api_cloudinary_secret" value="<?= $v('api_cloudinary_secret') ?>">
            <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
