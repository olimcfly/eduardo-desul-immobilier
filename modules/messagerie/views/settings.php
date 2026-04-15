<?php
/** @var ImapService $imap */
/** @var int $userId */

$isConfigured = $imap->isConfigured();
$advisorEmail = $imap->getAdvisorEmail();
$imapHost     = (string) setting('imap_host',   $_ENV['IMAP_HOST']   ?? setting('smtp_host',   $_ENV['SMTP_HOST']   ?? ''), $userId);
$imapPort     = (string) setting('imap_port',   $_ENV['IMAP_PORT']   ?? '993', $userId);
$imapUser     = (string) setting('imap_user',   $_ENV['IMAP_USER']   ?? $advisorEmail, $userId);
$imapSecure   = (string) setting('imap_secure', $_ENV['IMAP_SECURE'] ?? 'ssl', $userId);
?>
<style>
.conn-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px;max-width:520px;}
.conn-status{display:flex;align-items:center;gap:10px;padding:14px;border-radius:10px;margin-bottom:20px;}
.conn-status.ok{background:#dcfce7;border:1px solid #bbf7d0;}
.conn-status.nok{background:#fef3c7;border:1px solid #fde68a;}
.conn-status i{font-size:1.1rem;}
.conn-status.ok i{color:#16a34a;}
.conn-status.nok i{color:#d97706;}
.conn-status-text h4{margin:0;font-size:.9rem;font-weight:700;}
.conn-status-text p{margin:2px 0 0;font-size:.78rem;color:#475569;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.form-group{display:flex;flex-direction:column;gap:4px;}
.form-group.full{grid-column:1/-1;}
.form-group label{font-size:.76rem;font-weight:600;color:#374151;}
.form-group input,.form-group select{border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font:inherit;font-size:.84rem;}
.form-actions{display:flex;gap:8px;margin-top:16px;align-items:center;}
.btn-test{background:#f1f5f9;color:#475569;border:0;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-test:hover{background:#e2e8f0;}
.btn-save{background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-save:hover{background:#1d4ed8;}
.btn-disconnect{background:#fee2e2;color:#991b1b;border:0;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
#formFeedback{font-size:.78rem;color:#64748b;}
</style>

<div class="page-header" style="margin-bottom:16px;">
    <h1><i class="fas fa-plug page-icon"></i> Connexion email</h1>
    <p>Configurez votre compte IMAP pour recevoir et envoyer des emails depuis l'application.</p>
</div>

<div class="conn-card">

    <!-- Statut -->
    <div class="conn-status <?= $isConfigured ? 'ok' : 'nok' ?>">
        <i class="fas <?= $isConfigured ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
        <div class="conn-status-text">
            <h4><?= $isConfigured ? 'Compte connecté' : 'Aucun compte configuré' ?></h4>
            <p><?= $isConfigured ? htmlspecialchars($advisorEmail) : 'Renseignez vos paramètres IMAP ci-dessous.' ?></p>
        </div>
        <?php if ($isConfigured): ?>
            <button class="btn-disconnect" style="margin-left:auto;" onclick="disconnectAccount()">
                <i class="fas fa-xmark"></i> Déconnecter
            </button>
        <?php endif; ?>
    </div>

    <!-- Formulaire -->
    <form onsubmit="saveConfig(event)">
        <div class="form-grid">
            <div class="form-group full">
                <label>Adresse email (nom d'utilisateur IMAP)</label>
                <input type="email" id="cfgUser" value="<?= htmlspecialchars($imapUser) ?>" placeholder="contact@votredomaine.fr" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" id="cfgPass" placeholder="<?= $isConfigured ? '••••••••• (inchangé si vide)' : 'Mot de passe' ?>">
            </div>
            <div class="form-group">
                <label>Sécurité</label>
                <select id="cfgSecure">
                    <option value="ssl"  <?= $imapSecure === 'ssl'  ? 'selected' : '' ?>>SSL (port 993)</option>
                    <option value="tls"  <?= $imapSecure === 'tls'  ? 'selected' : '' ?>>TLS (port 143)</option>
                    <option value="none" <?= $imapSecure === 'none' ? 'selected' : '' ?>>Aucune (port 143)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hôte IMAP <small style="font-weight:400;color:#94a3b8;">(auto si même que SMTP)</small></label>
                <input type="text" id="cfgHost" value="<?= htmlspecialchars($imapHost) ?>" placeholder="mail.votredomaine.fr">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" id="cfgPort" value="<?= htmlspecialchars($imapPort ?: '993') ?>" min="1" max="65535">
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-test" id="testBtn" onclick="testConnection()">
                <i class="fas fa-plug"></i> Tester la connexion
            </button>
            <span id="formFeedback"></span>
            <button type="submit" class="btn-save" style="margin-left:auto;">
                <i class="fas fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </form>

    <!-- Aide -->
    <div style="margin-top:20px;padding:12px;background:#f8fafc;border-radius:9px;border:1px solid #e5e7eb;">
        <p style="font-size:.76rem;color:#64748b;margin:0;line-height:1.6;">
            <strong>OVH / cPanel :</strong> hôte = <code>mail.votredomaine.fr</code>, port SSL = 993<br>
            <strong>Infomaniak :</strong> hôte = <code>mail.infomaniak.com</code>, port SSL = 993<br>
            <strong>Google Workspace :</strong> hôte = <code>imap.gmail.com</code>, port SSL = 993 (IMAP activé requis)<br>
            Le mot de passe IMAP est généralement le même que votre mot de passe email.
        </p>
    </div>
</div>

<script>
async function saveConfig(e) {
    e.preventDefault();
    const fb = document.getElementById('formFeedback');
    fb.textContent = 'Enregistrement...';
    const fd = new FormData();
    fd.append('host',   document.getElementById('cfgHost').value.trim());
    fd.append('port',   document.getElementById('cfgPort').value);
    fd.append('user',   document.getElementById('cfgUser').value.trim());
    fd.append('pass',   document.getElementById('cfgPass').value);
    fd.append('secure', document.getElementById('cfgSecure').value);
    const d = await (await fetch('/admin?module=messagerie&action=save_imap', {method:'POST',body:fd})).json();
    if (d.ok) { fb.style.color='#16a34a'; fb.textContent = '✓ Enregistré.'; setTimeout(() => location.reload(), 800); }
    else { fb.style.color='#ef4444'; fb.textContent = d.error||'Erreur.'; }
}
async function testConnection() {
    const btn = document.getElementById('testBtn');
    const fb  = document.getElementById('formFeedback');
    // Save first then test
    await saveConfig({preventDefault:()=>{}});
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test...';
    fb.style.color = '#64748b'; fb.textContent = '';
    try {
        const d = await (await fetch('/admin?module=messagerie&action=test_imap')).json();
        if (d.ok) { fb.style.color='#16a34a'; fb.textContent = '✓ ' + d.message; }
        else { fb.style.color='#ef4444'; fb.textContent = '✗ ' + (d.error||'Échec.'); }
    } catch(e) { fb.style.color='#ef4444'; fb.textContent = 'Erreur réseau.'; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plug"></i> Tester la connexion'; }
}
async function disconnectAccount() {
    if (!confirm('Déconnecter ce compte ? Les messages synchronisés sont conservés.')) return;
    const d = await (await fetch('/admin?module=messagerie&action=disconnect_imap')).json();
    if (d.ok) { location.reload(); }
}
</script>
