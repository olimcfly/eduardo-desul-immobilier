<?php
$s = settings_group('site');
$v = fn(string $k) => htmlspecialchars((string)($s[$k] ?? ''));
?>
<form class="settings-form" method="post">
<input type="hidden" name="section" value="site">

<div class="sf-group">
    <label>Nom du site</label>
    <input type="text" name="site[nom]" value="<?= $v('nom') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>URL du site</label>
    <input type="url" name="site[url]" value="<?= $v('url') ?>" placeholder="https://…" class="sf-input">
</div>
<div class="sf-group">
    <label>Slogan</label>
    <input type="text" name="site[slogan]" value="<?= $v('slogan') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Email de contact</label>
    <input type="email" name="site[email_contact]" value="<?= $v('email_contact') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Téléphone affiché</label>
    <input type="text" name="site[telephone]" value="<?= $v('telephone') ?>" class="sf-input">
</div>

<div class="sf-actions">
    <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
</div>
</form>
