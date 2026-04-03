<?php
$s = settings_group('notif');
$checked = fn(string $k) => ($s[$k] ?? '0') === '1' ? 'checked' : '';
$v = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="notif">

    <div class="form-group">
        <label>Email de destination <span class="label-hint">Si différent du profil</span></label>
        <input type="email" name="notif_email_dest" value="<?= $v('notif_email_dest') ?>" placeholder="vous@exemple.fr">
    </div>

    <div class="form-section-title">Événements déclencheurs</div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Nouveau contact</div>
            <div class="toggle-hint">Mail envoyé à chaque demande de contact</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="notif_email_contact" value="1" <?= $checked('notif_email_contact') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Nouvelle estimation</div>
            <div class="toggle-hint">Mail à chaque demande d'estimation en ligne</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="notif_email_estimation" value="1" <?= $checked('notif_email_estimation') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Nouvel avis Google</div>
            <div class="toggle-hint">Alerte lors d'un nouvel avis (via GMB)</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="notif_email_avis" value="1" <?= $checked('notif_email_avis') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Alertes biens (acheteurs)</div>
            <div class="toggle-hint">Notification quand un bien correspond à une alerte</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="notif_email_alerte" value="1" <?= $checked('notif_email_alerte') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Résumé hebdomadaire</div>
            <div class="toggle-hint">Synthèse envoyée chaque lundi matin</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="notif_resume_hebdo" value="1" <?= $checked('notif_resume_hebdo') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
