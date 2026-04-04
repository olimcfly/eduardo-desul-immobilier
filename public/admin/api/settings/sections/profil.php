<?php
$s = settings_group('profil');
$v = fn(string $k) => htmlspecialchars((string)($s[$k] ?? ''));
?>
<form class="settings-form" method="post">
<input type="hidden" name="section" value="profil">

<div class="sf-group">
    <label>Nom complet</label>
    <input type="text" name="profil[nom]" value="<?= $v('nom') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Email</label>
    <input type="email" name="profil[email]" value="<?= $v('email') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Téléphone</label>
    <input type="text" name="profil[telephone]" value="<?= $v('telephone') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Ville</label>
    <input type="text" name="profil[ville]" value="<?= $v('ville') ?>" class="sf-input">
</div>
<div class="sf-group">
    <label>Bio / Présentation</label>
    <textarea name="profil[bio]" class="sf-input" rows="4"><?= $v('bio') ?></textarea>
</div>

<div class="sf-actions">
    <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
</div>
</form>
