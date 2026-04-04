<?php
$s = settings_group('api');
$v = fn(string $k) => htmlspecialchars((string)($s[$k] ?? ''));
$dot = fn(string $k) => !empty($s[$k])
    ? '<span class="api-status-dot dot-ok"></span>Configurée'
    : '<span class="api-status-dot dot-off"></span>Non configurée';
?>
<form class="settings-form" method="post">
<input type="hidden" name="section" value="api">

<div class="sf-group">
    <label>OpenAI — <?= $dot('openai') ?></label>
    <div class="sf-key-wrap">
        <input type="password" name="api[openai]" value="<?= $v('openai') ?>" placeholder="sk-…" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Google Maps — <?= $dot('google_maps') ?></label>
    <div class="sf-key-wrap">
        <input type="password" name="api[google_maps]" value="<?= $v('google_maps') ?>" placeholder="AIza…" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Google My Business — Client ID — <?= $dot('gmb_client_id') ?></label>
    <div class="sf-key-wrap">
        <input type="password" name="api[gmb_client_id]" value="<?= $v('gmb_client_id') ?>" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Google My Business — Client Secret — <?= $dot('gmb_client_secret') ?></label>
    <div class="sf-key-wrap">
        <input type="password" name="api[gmb_client_secret]" value="<?= $v('gmb_client_secret') ?>" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Facebook Access Token — <?= $dot('fb_access_token') ?></label>
    <div class="sf-key-wrap">
        <input type="password" name="api[fb_access_token]" value="<?= $v('fb_access_token') ?>" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Cloudinary — Cloud Name — <?= $dot('cloudinary_key') ?></label>
    <input type="text" name="api[cloudinary_cloud_name]" value="<?= $v('cloudinary_cloud_name') ?>" class="sf-input">
</div>

<div class="sf-group">
    <label>Cloudinary — API Key</label>
    <div class="sf-key-wrap">
        <input type="password" name="api[cloudinary_key]" value="<?= $v('cloudinary_key') ?>" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-group">
    <label>Cloudinary — API Secret</label>
    <div class="sf-key-wrap">
        <input type="password" name="api[cloudinary_secret]" value="<?= $v('cloudinary_secret') ?>" class="sf-input">
        <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
    </div>
</div>

<div class="sf-actions">
    <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
</div>
</form>
