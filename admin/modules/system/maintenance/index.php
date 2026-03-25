<?php
/**
 * MODULE MAINTENANCE - index.php
 * Placement : /admin/modules/system/maintenance/index.php
 */

$maintenance = ['is_active' => 0, 'message' => '', 'allowed_ips' => '127.0.0.1', 'end_date' => null];
try {
    $stmt = $pdo->query("SELECT * FROM maintenance WHERE id = 1 LIMIT 1");
    $row = $stmt->fetch();
    if ($row) $maintenance = $row;
} catch (Exception $e) {}

$isActive   = (int)($maintenance['is_active'] ?? 0);
$message    = $maintenance['message'] ?? '';
$allowedIps = $maintenance['allowed_ips'] ?? '127.0.0.1';

$visitorIp = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '';
if (strpos($visitorIp, ',') !== false) {
    $visitorIp = trim(explode(',', $visitorIp)[0]);
}

$ipList    = array_filter(array_map('trim', explode(',', $allowedIps)));
$ipIsAllowed = in_array($visitorIp, $ipList);

$apiUrl = '/admin/api/system/maintenance/save.php';
?>

<!-- ═══════════════════════════════════════════════
     HERO
════════════════════════════════════════════════ -->
<div class="mod-hero mod-hero-light">
    <div class="mod-hero-content">
        <p>Activez le mode maintenance pour effectuer des opérations sans impacter les visiteurs</p>
    </div>
    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat-value" id="stat-mode">
                <?= $isActive ? '<span style="color:#f87171">ON</span>' : '<span style="color:#34d399">OFF</span>' ?>
            </div>
            <div class="mod-stat-label">Statut</div>
        </div>
        <div class="mod-stat">
            <div class="mod-stat-value" style="font-size:.9rem"><?= htmlspecialchars($visitorIp) ?></div>
            <div class="mod-stat-label">Votre IP</div>
        </div>
        <div class="mod-stat">
            <div class="mod-stat-value">
                <?= $ipIsAllowed
                    ? '<span style="color:#34d399"><i class="fa fa-check"></i></span>'
                    : '<span style="color:#f87171"><i class="fa fa-times"></i></span>' ?>
            </div>
            <div class="mod-stat-label">IP autorisée</div>
        </div>
        <div class="mod-stat">
            <div class="mod-stat-value" id="stat-visitors">
                <?= $isActive
                    ? '<span style="color:#f87171"><i class="fa fa-eye-slash"></i></span>'
                    : '<span style="color:#34d399"><i class="fa fa-eye"></i></span>' ?>
            </div>
            <div class="mod-stat-label">Visiteurs</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     BANNIÈRE STATUT
════════════════════════════════════════════════ -->
<div class="mod-flash <?= $isActive ? 'mod-flash-error' : 'mod-flash-success' ?>" id="maint-status-bar">
    <i class="fa <?= $isActive ? 'fa-wrench' : 'fa-globe' ?>"></i>
    <span id="maint-label">
        <?= $isActive ? 'MODE MAINTENANCE ACTIF — Les visiteurs voient la page de maintenance' : 'SITE EN LIGNE — Accessible normalement aux visiteurs' ?>
    </span>
</div>

<!-- ═══════════════════════════════════════════════
     TOGGLE ON / OFF
════════════════════════════════════════════════ -->
<div class="mod-form-panel">
    <h2><i class="fa fa-power-off"></i> Contrôle du mode maintenance</h2>
    <div class="mod-grid mod-grid-2">

        <button onclick="maintToggle(1)" id="btn-on"
            class="mod-btn mod-btn-success mod-toggle-btn mod-toggle-btn-on <?= $isActive ? 'is-current' : '' ?>">
            <i class="fa fa-toggle-on"></i>
            Activer la maintenance
            <?php if ($isActive): ?><span class="mod-badge mod-badge-active">Actuel</span><?php endif; ?>
        </button>

        <button onclick="maintToggle(0)" id="btn-off"
            class="mod-btn mod-btn-danger mod-toggle-btn mod-toggle-btn-off <?= !$isActive ? 'is-current' : '' ?>">
            <i class="fa fa-toggle-off"></i>
            Désactiver (site public)
            <?php if (!$isActive): ?><span class="mod-badge mod-badge-error">Actuel</span><?php endif; ?>
        </button>

    </div>
</div>

<!-- ═══════════════════════════════════════════════
     MESSAGE DE MAINTENANCE
════════════════════════════════════════════════ -->
<div class="mod-form-panel">
    <h2><i class="fa fa-edit"></i> Message de maintenance</h2>
    <div class="mod-form-group full">
        <label>Message affiché aux visiteurs</label>
        <textarea id="maint-message" rows="3"
            placeholder="Ex: Nous effectuons une mise à jour. Retour prévu demain à 9h."><?= htmlspecialchars($message) ?></textarea>
        <small>Soyez clair et professionnel. Indiquez si possible quand le site sera de retour.</small>
    </div>
    <div class="mod-form-actions">
        <button onclick="maintSaveMessage()" class="mod-btn mod-btn-primary" id="btn-save-msg">
            <i class="fa fa-save"></i> Sauvegarder le message
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     IPs AUTORISÉES
════════════════════════════════════════════════ -->
<div class="mod-form-panel">
    <h2><i class="fa fa-shield-alt"></i> IPs autorisées</h2>
    <div class="mod-form-group full">
        <label>Adresses IP <small>(séparées par des virgules)</small></label>
        <textarea id="maint-whitelist" rows="2"
            placeholder="Ex: 92.184.103.245, 1.2.3.4"><?= htmlspecialchars($allowedIps) ?></textarea>
        <small>Ces IPs accèdent au site normalement même pendant la maintenance.</small>
    </div>

    <div class="mod-toolbar mod-toolbar-bordered">
        <div class="mod-toolbar-left">
            <i class="fa fa-globe mod-text-muted"></i>
            <span class="mod-text-sm">Votre IP : <code class="mod-text-mono"><?= htmlspecialchars($visitorIp) ?></code></span>
            <?php if ($ipIsAllowed): ?>
                <span class="mod-badge mod-badge-active maint-ip-badge"><i class="fa fa-check"></i> Autorisée</span>
            <?php else: ?>
                <span class="mod-badge mod-badge-error maint-ip-badge"><i class="fa fa-times"></i> Non autorisée</span>
            <?php endif; ?>
        </div>
        <div class="mod-toolbar-right">
            <button onclick="maintAddMyIp()" class="mod-btn mod-btn-secondary mod-btn-sm">
                <i class="fa fa-plus"></i> Ajouter mon IP
            </button>
        </div>
    </div>

    <div class="mod-form-actions">
        <button onclick="maintSaveWhitelist()" class="mod-btn mod-btn-primary" id="btn-save-ip">
            <i class="fa fa-save"></i> Sauvegarder la whitelist
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     ZONE DANGEREUSE (RESET)
════════════════════════════════════════════════ -->
<div class="mod-form-panel" style="border:1px solid #7f1d1d;background:linear-gradient(180deg,#fff5f5 0%,#ffe4e6 100%);">
    <h2 style="color:#7f1d1d;"><i class="fa fa-radiation"></i> Zone dangereuse</h2>
    <p style="margin-top:0;color:#7f1d1d;font-weight:600;">
        Actions irréversibles réservées à l'administration. Accès volontairement difficile.
    </p>
    <details style="margin-top:12px;">
        <summary style="cursor:pointer;color:#991b1b;font-weight:700;">Afficher les actions destructives</summary>
        <div style="margin-top:14px;">
            <div class="mod-flash mod-flash-error" style="margin:0 0 14px 0;">
                <i class="fa fa-exclamation-triangle"></i>
                Ces actions suppriment définitivement des données. Aucun retour arrière possible.
            </div>
            <div class="mod-form-group full">
                <label for="danger-confirm-text">Confirmation manuelle</label>
                <input
                    type="text"
                    id="danger-confirm-text"
                    class="mod-input"
                    placeholder="Tapez: SUPPRIMER DEFINITIVEMENT"
                    autocomplete="off">
                <small>La phrase exacte est exigée avant l'exécution.</small>
            </div>
            <div class="mod-grid mod-grid-2">
                <button
                    type="button"
                    class="mod-btn"
                    style="background:#dc2626;color:#fff;border-color:#b91c1c;"
                    onclick="maintDangerReset('stats')">
                    <i class="fa fa-chart-line"></i> Réinitialiser uniquement les statistiques
                </button>
                <button
                    type="button"
                    class="mod-btn"
                    style="background:#991b1b;color:#fff;border-color:#7f1d1d;"
                    onclick="maintDangerReset('all')">
                    <i class="fa fa-skull-crossbones"></i> Effacer toutes les données métier
                </button>
            </div>
        </div>
    </details>
</div>

<!-- ═══════════════════════════════════════════════
     JAVASCRIPT (logique inchangée)
════════════════════════════════════════════════ -->
<script>
(function() {
    const API_URL = '<?= $apiUrl ?>';
    const MY_IP   = '<?= htmlspecialchars($visitorIp) ?>';

    function showToast(msg, type = 'success') {
        if (window.showAdminFlash) {
            window.showAdminFlash(msg, type);
            return;
        }
        const existing = document.getElementById('maint-flash');
        if (existing) existing.remove();
        const el = document.createElement('div');
        el.id = 'maint-flash';
        el.className = 'mod-flash mod-flash-' + (type === 'success' ? 'success' : 'error');
        el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;animation:slideUp .3s ease;min-width:280px;';
        el.innerHTML = '<i class="fa fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    async function apiCall(action, data = {}) {
        const fd = new FormData();
        fd.append('action', action);
        for (const [k, v] of Object.entries(data)) fd.append(k, v);
        try {
            const res  = await fetch(API_URL, { method: 'POST', body: fd });
            const text = await res.text();
            try { return JSON.parse(text); }
            catch (e) { return { success: false, message: 'Erreur serveur' }; }
        } catch (e) {
            return { success: false, message: 'Erreur réseau' };
        }
    }

    function updateUI(isActive) {
        const bar = document.getElementById('maint-status-bar');
        bar.className = 'mod-flash ' + (isActive ? 'mod-flash-error' : 'mod-flash-success');
        bar.querySelector('i').className = 'fa ' + (isActive ? 'fa-wrench' : 'fa-globe');
        document.getElementById('maint-label').textContent = isActive
            ? 'MODE MAINTENANCE ACTIF — Les visiteurs voient la page de maintenance'
            : 'SITE EN LIGNE — Accessible normalement aux visiteurs';

        document.getElementById('stat-mode').innerHTML     = isActive ? '<span style="color:#f87171">ON</span>' : '<span style="color:#34d399">OFF</span>';
        document.getElementById('stat-visitors').innerHTML = isActive
            ? '<span style="color:#f87171"><i class="fa fa-eye-slash"></i></span>'
            : '<span style="color:#34d399"><i class="fa fa-eye"></i></span>';

        const btnOn  = document.getElementById('btn-on');
        const btnOff = document.getElementById('btn-off');
        btnOn.style.boxShadow  = isActive ? '0 0 0 3px rgba(5,150,105,.25)'  : '';
        btnOff.style.boxShadow = !isActive ? '0 0 0 3px rgba(220,38,38,.25)' : '';
    }

    window.maintToggle = async function(val) {
        const result = await apiCall('toggle', { is_active: val });
        if (result.success) {
            updateUI(val === 1);
            showToast(val
                ? 'Maintenance activée ! Les visiteurs voient la page de maintenance.'
                : 'Site remis en ligne ! Les visiteurs voient le site normal.');
        } else {
            showToast(result.message || 'Erreur', 'error');
        }
    };

    window.maintSaveMessage = async function() {
        const btn = document.getElementById('btn-save-msg');
        const msg = document.getElementById('maint-message').value.trim();
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sauvegarde...';
        const result = await apiCall('save_message', { message: msg });
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save"></i> Sauvegarder le message';
        showToast(result.success ? 'Message sauvegardé !' : (result.message || 'Erreur'), result.success ? 'success' : 'error');
    };

    window.maintSaveWhitelist = async function() {
        const btn = document.getElementById('btn-save-ip');
        const ips = document.getElementById('maint-whitelist').value.trim();
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sauvegarde...';
        const result = await apiCall('save_whitelist', { allowed_ips: ips });
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save"></i> Sauvegarder la whitelist';
        if (result.success) {
            const ipList    = ips.split(',').map(s => s.trim());
            const isAllowed = ipList.includes(MY_IP);
            const badges = document.querySelectorAll('.maint-ip-badge');
            badges.forEach(b => {
                b.className = 'mod-badge maint-ip-badge ' + (isAllowed ? 'mod-badge-active' : 'mod-badge-error');
                b.innerHTML = isAllowed ? '<i class="fa fa-check"></i> Autorisée' : '<i class="fa fa-times"></i> Non autorisée';
            });
            showToast('Whitelist sauvegardée !');
        } else {
            showToast(result.message || 'Erreur', 'error');
        }
    };

    window.maintAddMyIp = function() {
        const ta      = document.getElementById('maint-whitelist');
        const current = ta.value.trim();
        const ips     = current ? current.split(',').map(s => s.trim()) : [];
        if (ips.includes(MY_IP)) { showToast('Votre IP est déjà dans la liste'); return; }
        ips.push(MY_IP);
        ta.value = ips.join(', ');
        showToast("IP ajoutée ! N'oubliez pas de sauvegarder.");
    };

    window.maintDangerReset = async function(scope) {
        const input  = document.getElementById('danger-confirm-text');
        const typed  = (input?.value || '').trim();
        const phrase = 'SUPPRIMER DEFINITIVEMENT';

        if (typed !== phrase) {
            showToast(`Phrase invalide. Tapez exactement: ${phrase}`, 'error');
            return;
        }

        const label = scope === 'all'
            ? 'EFFACER TOUTES LES DONNÉES MÉTIER'
            : 'EFFACER TOUTES LES STATISTIQUES';

        const secondCheck = prompt(`Dernière validation.\\nTapez exactement:\\n${label}`);
        if (secondCheck !== label) {
            showToast('Confirmation annulée (texte incorrect).', 'error');
            return;
        }

        const result = await apiCall('danger_reset', { scope, confirmation: typed, double_confirmation: secondCheck });
        if (result.success) {
            input.value = '';
            showToast(result.message || 'Reset terminé.');
        } else {
            showToast(result.message || 'Échec du reset.', 'error');
        }
    };
})();
</script>
