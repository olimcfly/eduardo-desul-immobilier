<?php
// Section : Zone de danger
$maintenanceEnabled = is_file(STORAGE_PATH . '/cache/maintenance.flag');
?>
<div class="danger-zone-wrapper">

    <div class="danger-intro">
        <i class="fas fa-triangle-exclamation"></i>
        <p>Les actions ci-dessous sont <strong>irréversibles</strong>.
           Procédez avec la plus grande prudence.</p>
    </div>

    <!-- Export données -->
    <div class="danger-card danger-card-safe">
        <div class="danger-card-body">
            <div class="danger-card-icon"><i class="fas fa-file-arrow-down"></i></div>
            <div>
                <div class="danger-card-title">Exporter mes données</div>
                <div class="danger-card-desc">
                    Téléchargez l'ensemble de vos données (contacts, biens, paramètres)
                    au format JSON ou CSV.
                </div>
            </div>
        </div>
        <div class="danger-card-actions">
            <button class="btn-danger-safe" onclick="exportData('json')">
                <i class="fas fa-code"></i> JSON
            </button>
            <button class="btn-danger-safe" onclick="exportData('csv')">
                <i class="fas fa-table"></i> CSV
            </button>
        </div>
    </div>

    <!-- Vider le cache -->
    <div class="danger-card danger-card-safe">
        <div class="danger-card-body">
            <div class="danger-card-icon"><i class="fas fa-broom"></i></div>
            <div>
                <div class="danger-card-title">Vider le cache</div>
                <div class="danger-card-desc">
                    Supprime les fichiers de cache et force la régénération.
                </div>
            </div>
        </div>
        <div class="danger-card-actions">
            <button class="btn-danger-safe" onclick="clearCache()">
                <i class="fas fa-rotate"></i> Vider
            </button>
        </div>
    </div>

    <!-- Mode maintenance -->
    <div class="danger-card danger-card-warn">
        <div class="danger-card-body">
            <div class="danger-card-icon"><i class="fas fa-screwdriver-wrench"></i></div>
            <div>
                <div class="danger-card-title">Mode maintenance du site</div>
                <div class="danger-card-desc">
                    Active une page de maintenance publique (HTTP 503) tout en conservant l'accès à l'admin.
                </div>
            </div>
        </div>
        <div class="danger-card-actions">
            <button class="btn-danger-warn" id="maintenance-toggle-btn" onclick="toggleMaintenance()">
                <i class="fas fa-power-off"></i>
                <?= $maintenanceEnabled ? 'Désactiver la maintenance' : 'Activer la maintenance' ?>
            </button>
        </div>
    </div>

    <!-- Réinitialiser paramètres -->
    <div class="danger-card danger-card-warn">
        <div class="danger-card-body">
            <div class="danger-card-icon"><i class="fas fa-arrow-rotate-left"></i></div>
            <div>
                <div class="danger-card-title">Réinitialiser les paramètres</div>
                <div class="danger-card-desc">
                    Remet tous les paramètres à leurs valeurs par défaut.
                    Vos biens et contacts ne seront <strong>pas supprimés</strong>.
                </div>
            </div>
        </div>
        <div class="danger-card-actions">
            <button class="btn-danger-warn"
                    onclick="confirmDangerAction('reset_settings', 'Réinitialiser tous les paramètres ?')">
                <i class="fas fa-rotate-left"></i> Réinitialiser
            </button>
        </div>
    </div>

    <!-- Supprimer toutes les données -->
    <div class="danger-card danger-card-critical">
        <div class="danger-card-body">
            <div class="danger-card-icon"><i class="fas fa-skull-crossbones"></i></div>
            <div>
                <div class="danger-card-title">Supprimer toutes les données</div>
                <div class="danger-card-desc">
                    Supprime définitivement tous les biens, contacts, campagnes
                    et paramètres. <strong>Aucune récupération possible.</strong>
                </div>
            </div>
        </div>
        <div class="danger-card-actions">
            <button class="btn-danger-critical"
                    onclick="confirmDangerAction('delete_all', 'Supprimer TOUTES les données ?', true)">
                <i class="fas fa-trash-can"></i> Tout supprimer
            </button>
        </div>
    </div>

</div>

<!-- ── Modal de confirmation ────────────────────────────────── -->
<div class="danger-modal" id="danger-modal" style="display:none">
    <div class="danger-modal-box">
        <div class="danger-modal-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 id="danger-modal-title">Confirmer l'action</h3>
        <p id="danger-modal-desc">Cette action est irréversible.</p>
        <div id="danger-modal-confirm-wrap" style="display:none; margin-bottom:12px">
            <label style="font-size:13px; color:#e74c3c; font-weight:600">
                Tapez <strong>SUPPRIMER</strong> pour confirmer :
            </label>
            <input type="text" id="danger-confirm-input" placeholder="SUPPRIMER"
                   style="width:100%; margin-top:6px; padding:8px 12px; border:2px solid #e74c3c;
                          border-radius:8px; font-size:14px; outline:none">
        </div>
        <div class="danger-modal-btns">
            <button class="btn-cancel" onclick="closeDangerModal()">Annuler</button>
            <button class="btn-danger-confirm" id="danger-confirm-btn" disabled>
                <i class="fas fa-check"></i> Confirmer
            </button>
        </div>
    </div>
</div>

<script>
let _dangerAction  = null;
let _requireTyping = false;
let _maintenanceEnabled = <?= $maintenanceEnabled ? 'true' : 'false' ?>;

function confirmDangerAction(action, title, requireTyping = false) {
    _dangerAction  = action;
    _requireTyping = requireTyping;

    document.getElementById('danger-modal-title').textContent = title;
    document.getElementById('danger-modal-desc').textContent  =
        requireTyping
            ? 'Cette action supprimera définitivement toutes vos données.'
            : 'Cette action est irréversible. Continuez ?';

    const wrap  = document.getElementById('danger-modal-confirm-wrap');
    const input = document.getElementById('danger-confirm-input');
    const btn   = document.getElementById('danger-confirm-btn');

    if (requireTyping) {
        wrap.style.display = 'block';
        input.value = '';
        btn.disabled = true;
        input.addEventListener('input', () => {
            btn.disabled = input.value.trim() !== 'SUPPRIMER';
        });
    } else {
        wrap.style.display = 'none';
        btn.disabled = false;
    }

    document.getElementById('danger-modal').style.display = 'flex';
}

function closeDangerModal() {
    document.getElementById('danger-modal').style.display = 'none';
    _dangerAction = null;
}

document.getElementById('danger-confirm-btn').addEventListener('click', function () {
    if (!_dangerAction) return;
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement…';

    fetch('/admin/api/settings/danger.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: _dangerAction }),
    })
    .then(r => r.json())
    .then(data => {
        closeDangerModal();
        if (data.success) {
            if (_dangerAction === 'delete_all') {
                window.location.href = '/admin/login';
                return;
            }
            // Toast via parent
            if (window.showToast) showToast(data.message || 'Action effectuée.', 'success');
        } else {
            if (window.showToast) showToast(data.error || 'Erreur.', 'error');
        }
    })
    .catch(() => {
        closeDangerModal();
        if (window.showToast) showToast('Erreur réseau.', 'error');
    });
});

function exportData(format) {
    window.location.href = `/admin/api/settings/export.php?format=${format}`;
}

function clearCache() {
    fetch('/admin/api/settings/danger.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'clear_cache' }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.showToast)
            showToast(data.success ? 'Cache vidé.' : data.error, data.success ? 'success' : 'error');
    });
}

function refreshMaintenanceButton() {
    const btn = document.getElementById('maintenance-toggle-btn');
    if (!btn) return;

    btn.innerHTML = _maintenanceEnabled
        ? '<i class="fas fa-power-off"></i> Désactiver la maintenance'
        : '<i class="fas fa-power-off"></i> Activer la maintenance';
}

function toggleMaintenance() {
    const action = _maintenanceEnabled ? 'maintenance_off' : 'maintenance_on';
    const btn = document.getElementById('maintenance-toggle-btn');
    if (btn) {
        btn.disabled = true;
    }

    fetch('/admin/api/settings/danger.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            _maintenanceEnabled = !_maintenanceEnabled;
            refreshMaintenanceButton();
        }
        if (window.showToast) {
            showToast(data.success ? (data.message || 'Action effectuée.') : (data.error || 'Erreur.'), data.success ? 'success' : 'error');
        }
    })
    .catch(() => {
        if (window.showToast) {
            showToast('Erreur réseau.', 'error');
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
        }
    });
}

refreshMaintenanceButton();
</script>
