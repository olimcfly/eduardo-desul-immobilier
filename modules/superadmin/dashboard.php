<?php
require_once __DIR__ . '/../../core/services/ModuleService.php';

$moduleDir = dirname(__DIR__);
$folders = array_filter(scandir($moduleDir) ?: [], static function ($entry) use ($moduleDir) {
    if ($entry === '.' || $entry === '..' || $entry === 'superadmin') {
        return false;
    }
    return is_dir($moduleDir . '/' . $entry) && is_file($moduleDir . '/' . $entry . '/accueil.php');
});

$modules = ModuleService::getAllSettings(array_values($folders));

// Liste des utilisateurs (hors superadmin)
$users = [];
try {
    $stmt = db()->query("SELECT id, name, email, role, is_active, created_at FROM users WHERE role != 'superadmin' ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('Superadmin dashboard users: ' . $e->getMessage());
}

// Infos superadmin connecté
$currentUser = Auth::user();
?>
<div class="page-header">
    <h1><i class="fas fa-crown page-icon"></i> HUB <span class="page-title-accent">Superadmin</span></h1>
    <p>Gestion des modules, des comptes et de votre profil.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <!-- MODULES -->
    <section class="card">
        <h3 class="card-title" style="margin-bottom:16px;"><i class="fas fa-puzzle-piece"></i> Modules</h3>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="padding:8px;text-align:left;">Module</th>
                    <th style="padding:8px;text-align:center;">Users</th>
                    <th style="padding:8px;text-align:center;">Admins</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($modules as $module): ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:10px 8px;font-weight:600;"><?= htmlspecialchars($module['module_name']) ?></td>
                    <td style="padding:10px 8px;text-align:center;">
                        <input type="checkbox" class="module-toggle" data-target="users"
                               data-module="<?= htmlspecialchars($module['module_name']) ?>"
                               <?= !empty($module['enabled_for_users']) ? 'checked' : '' ?>>
                    </td>
                    <td style="padding:10px 8px;text-align:center;">
                        <input type="checkbox" class="module-toggle" data-target="admins"
                               data-module="<?= htmlspecialchars($module['module_name']) ?>"
                               <?= !empty($module['enabled_for_admins']) ? 'checked' : '' ?>>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p id="module-save-feedback" style="margin-top:12px;font-size:13px;color:#64748b;min-height:20px;"></p>
    </section>

    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- MON PROFIL SUPERADMIN -->
        <section class="card">
            <h3 class="card-title" style="margin-bottom:14px;"><i class="fas fa-user-shield"></i> Mon profil</h3>
            <form id="superadmin-profile-form">
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:13px;color:#64748b;margin-bottom:4px;">Nom affiché</label>
                    <input type="text" id="superadmin-name" name="name"
                           value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"
                           style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:13px;color:#64748b;margin-bottom:4px;">Email</label>
                    <input type="text" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                           disabled style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#f9fafb;color:#9ca3af;box-sizing:border-box;">
                </div>
                <button type="submit"
                        style="background:#111827;color:#fff;border:0;padding:9px 20px;border-radius:8px;font-size:14px;cursor:pointer;">
                    Sauvegarder
                </button>
                <span id="profile-feedback" style="margin-left:12px;font-size:13px;color:#64748b;"></span>
            </form>
        </section>

        <!-- LISTE DES UTILISATEURS -->
        <section class="card">
            <h3 class="card-title" style="margin-bottom:14px;"><i class="fas fa-users"></i> Comptes utilisateurs</h3>
            <?php if (empty($users)): ?>
                <p style="color:#6b7280;font-size:14px;">Aucun utilisateur.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($users as $u): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;">
                        <div>
                            <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($u['name']) ?></div>
                            <div style="font-size:12px;color:#6b7280;"><?= htmlspecialchars($u['email']) ?></div>
                            <div style="font-size:11px;margin-top:2px;">
                                <span style="background:<?= $u['role'] === 'admin' ? '#dbeafe' : '#f3f4f6' ?>;color:<?= $u['role'] === 'admin' ? '#1d4ed8' : '#6b7280' ?>;padding:2px 8px;border-radius:20px;">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                                <?php if (!$u['is_active']): ?>
                                    <span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:20px;margin-left:4px;">Inactif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button class="toggle-active-btn"
                                    data-user-id="<?= (int)$u['id'] ?>"
                                    data-active="<?= (int)$u['is_active'] ?>"
                                    style="background:<?= $u['is_active'] ? '#fef9c3' : '#dcfce7' ?>;color:<?= $u['is_active'] ? '#854d0e' : '#166534' ?>;border:0;padding:6px 12px;border-radius:8px;font-size:12px;cursor:pointer;">
                                <?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<script>
(function () {
    const BASE = '/admin/';

    // ── Modules toggle ────────────────────────────────────────
    const feedback = document.getElementById('module-save-feedback');

    function setFeedback(text, isError) {
        if (!feedback) return;
        feedback.style.color = isError ? '#dc2626' : '#16a34a';
        feedback.textContent = text;
        setTimeout(() => { feedback.textContent = ''; }, 3000);
    }

    function moduleRowState(moduleName) {
        const users  = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="users"]');
        const admins = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="admins"]');
        return { users: !!(users && users.checked), admins: !!(admins && admins.checked) };
    }

    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const moduleName = this.getAttribute('data-module');
            const state = moduleRowState(moduleName);

            const body = new URLSearchParams();
            body.set('module_name', moduleName);
            body.set('enabled_for_users',  state.users  ? '1' : '0');
            body.set('enabled_for_admins', state.admins ? '1' : '0');

            fetch(BASE + '?module=superadmin&action=toggle_module', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                if (!json.ok) { setFeedback(json.message || 'Erreur.', true); return; }
                setFeedback('Module "' + moduleName + '" mis à jour.', false);
            })
            .catch(() => setFeedback('Erreur réseau.', true));
        });
    });

    // ── Profil superadmin ─────────────────────────────────────
    const profileForm = document.getElementById('superadmin-profile-form');
    const profileFeedback = document.getElementById('profile-feedback');

    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('superadmin-name').value.trim();
            if (!name) { profileFeedback.textContent = 'Le nom ne peut pas être vide.'; return; }

            const body = new URLSearchParams();
            body.set('name', name);

            fetch(BASE + '?module=superadmin&action=update_profile', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                profileFeedback.style.color = json.ok ? '#16a34a' : '#dc2626';
                profileFeedback.textContent = json.message || (json.ok ? 'Sauvegardé.' : 'Erreur.');
            })
            .catch(() => { profileFeedback.style.color = '#dc2626'; profileFeedback.textContent = 'Erreur réseau.'; });
        });
    }

    // ── Activer / Désactiver utilisateur ─────────────────────
    document.querySelectorAll('.toggle-active-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const active = this.getAttribute('data-active') === '1';

            const body = new URLSearchParams();
            body.set('user_id', userId);
            body.set('is_active', active ? '0' : '1');

            fetch(BASE + '?module=superadmin&action=toggle_user', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                if (json.ok) location.reload();
                else alert(json.message || 'Erreur.');
            })
            .catch(() => alert('Erreur réseau.'));
        });
    });
})();
</script>
