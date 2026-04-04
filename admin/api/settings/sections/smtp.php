<?php
$s = settings_group('smtp');
$v = fn(string $k, string $d = '') => htmlspecialchars((string)($s[$k] ?? $d), ENT_QUOTES, 'UTF-8');
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="smtp">

    <div class="form-section-title">Serveur d'envoi</div>

    <div class="form-row">
        <div class="form-group">
            <label>Hôte SMTP</label>
            <input type="text" name="smtp_host" value="<?= $v('smtp_host') ?>" placeholder="smtp.gmail.com">
        </div>
        <div class="form-group">
            <label>Port</label>
            <input type="number" name="smtp_port" value="<?= $v('smtp_port', '587') ?>" placeholder="587">
        </div>
    </div>

    <div class="form-group">
        <label>Sécurité</label>
        <select name="smtp_secure">
            <option value="tls" <?= $v('smtp_secure', 'tls') === 'tls' ? 'selected' : '' ?>>TLS (recommandé)</option>
            <option value="ssl" <?= $v('smtp_secure') === 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value="none" <?= $v('smtp_secure') === 'none' ? 'selected' : '' ?>>Aucune</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Utilisateur SMTP</label>
            <input type="text" name="smtp_user" value="<?= $v('smtp_user') ?>" placeholder="contact@domaine.fr">
        </div>
        <div class="form-group">
            <label>Mot de passe SMTP</label>
            <input type="password" name="smtp_pass" value="<?= $v('smtp_pass') ?>" autocomplete="new-password">
        </div>
    </div>

    <div class="form-section-title">Expéditeur</div>

    <div class="form-row">
        <div class="form-group">
            <label>Email expéditeur</label>
            <input type="email" name="smtp_from" value="<?= $v('smtp_from') ?>" placeholder="noreply@domaine.fr">
        </div>
        <div class="form-group">
            <label>Nom expéditeur</label>
            <input type="text" name="smtp_from_name" value="<?= $v('smtp_from_name') ?>" placeholder="Eduardo Desul Immobilier">
        </div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
