<?php
$s       = settings_group('securite');
$v       = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
$checked = fn(string $k) => ($s[$k] ?? '0') === '1' ? 'checked' : '';
$user    = Auth::user();
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="securite">

    <!-- ── Mot de passe ─────────────────────────────────────── -->
    <div class="form-section-title">Changer le mot de passe</div>

    <div class="form-group">
        <label>Mot de passe actuel</label>
        <div class="api-key-row">
            <input type="password" name="pwd_actuel" placeholder="••••••••••••" autocomplete="current-password">
            <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <div class="api-key-row">
                <input type="password" name="pwd_nouveau" id="pwd_nouveau"
                       placeholder="Min. 10 caractères" autocomplete="new-password">
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="form-group">
            <label>Confirmation</label>
            <div class="api-key-row">
                <input type="password" name="pwd_confirm" id="pwd_confirm"
                       placeholder="Répétez le mot de passe" autocomplete="new-password">
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            </div>
        </div>
    </div>

    <!-- Indicateur de force -->
    <div class="pwd-strength" id="pwd-strength" style="display:none">
        <div class="pwd-strength-bar">
            <div class="pwd-strength-fill" id="pwd-strength-fill"></div>
        </div>
        <span class="pwd-strength-label" id="pwd-strength-label"></span>
    </div>

    <!-- ── Session ──────────────────────────────────────────── -->
    <div class="form-section-title">Session</div>

    <div class="form-group">
        <label>Expiration de session <span class="label-hint">en minutes</span></label>
        <select name="sec_session_ttl">
            <?php
            $current = $v('sec_session_ttl', '480');
            $options = [
                '60'   => '1 heure',
                '240'  => '4 heures',
                '480'  => '8 heures (défaut)',
                '1440' => '24 heures',
                '0'    => 'Jamais (non recommandé)',
            ];
            foreach ($options as $val => $label):
            ?>
            <option value="<?= $val ?>" <?= $current === (string)$val ? 'selected' : '' ?>>
                <?= $label ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>IP(s) autorisées <span class="label-hint">Laissez vide pour désactiver. Séparées par virgule.</span></label>
        <input type="text" name="sec_ip_whitelist"
               value="<?= $v('sec_ip_whitelist') ?>"
               placeholder="92.168.1.1, 78.123.45.67">
        <small class="field-hint">
            Votre IP actuelle : <strong id="current-ip">…</strong>
        </small>
    </div>

    <!-- ── Double authentification ──────────────────────────── -->
    <div class="form-section-title">Double authentification (2FA)</div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Activer le 2FA par OTP</div>
            <div class="toggle-hint">Code à 6 chiffres requis à chaque connexion</div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="sec_2fa_active" value="1" <?= $checked('sec_2fa_active') ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <?php if (($s['sec_2fa_active'] ?? '0') === '1'): ?>
    <div class="twofa-info">
        <i class="fas fa-mobile-screen-button"></i>
        <div>
            2FA activé. Utilisez une application comme <strong>Google Authenticator</strong>
            ou <strong>Authy</strong> pour générer vos codes.
        </div>
    </div>
    <?php else: ?>
    <div class="twofa-info twofa-info-off">
        <i class="fas fa-lock-open"></i>
        <div>2FA désactivé. Activez-le pour renforcer la sécurité de votre compte.</div>
    </div>
    <?php endif; ?>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>

<script>
// Afficher l'IP actuelle
fetch('https://api.ipify.org?format=json')
    .then(r => r.json())
    .then(d => {
        const el = document.getElementById('current-ip');
        if (el) el.textContent = d.ip;
    })
    .catch(() => {});

// Indicateur de force du mot de passe
document.getElementById('pwd_nouveau')?.addEventListener('input', function () {
    const bar   = document.getElementById('pwd-strength');
    const fill  = document.getElementById('pwd-strength-fill');
    const label = document.getElementById('pwd-strength-label');
    const val   = this.value;

    if (!val) { bar.style.display = 'none'; return; }
    bar.style.display = 'flex';

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val))   score++;
    if (/[0-9]/.test(val))   score++;
    if (/[^a-zA-Z0-9]/.test(val)) score++;

    const levels = [
        { pct: '20%',  color: '#e74c3c', text: 'Très faible' },
        { pct: '40%',  color: '#e67e22', text: 'Faible'      },
        { pct: '60%',  color: '#f1c40f', text: 'Moyen'       },
        { pct: '80%',  color: '#2ecc71', text: 'Fort'        },
        { pct: '100%', color: '#27ae60', text: 'Très fort'   },
    ];
    const lvl = levels[Math.min(score, 4)];
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
    label.style.color     = lvl.color;
});
</script>
