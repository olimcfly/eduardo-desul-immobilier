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
$activeUsers = ModuleService::getActiveUserPages(10);
?>
<div class="page-header">
    <h1><i class="fas fa-crown page-icon"></i> HUB <span class="page-title-accent">Superadmin</span></h1>
    <p>Activez ou désactivez les modules pour les utilisateurs et administrateurs.</p>
</div>

<div class="cards-container" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <section class="card" style="grid-column:1 / 2;">
        <h3 class="card-title" style="margin-bottom:16px;">Gestion des modules</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
            <tr style="text-align:left;border-bottom:1px solid #e5e7eb;">
                <th style="padding:8px;">Module</th>
                <th style="padding:8px;">Users</th>
                <th style="padding:8px;">Admins</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($modules as $module): ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:10px 8px;font-weight:600;"><?= htmlspecialchars($module['module_name']) ?></td>
                    <td style="padding:10px 8px;">
                        <label>
                            <input type="checkbox"
                                   class="module-toggle"
                                   data-target="users"
                                   data-module="<?= htmlspecialchars($module['module_name']) ?>"
                                   <?= !empty($module['enabled_for_users']) ? 'checked' : '' ?>>
                            Actif
                        </label>
                    </td>
                    <td style="padding:10px 8px;">
                        <label>
                            <input type="checkbox"
                                   class="module-toggle"
                                   data-target="admins"
                                   data-module="<?= htmlspecialchars($module['module_name']) ?>"
                                   <?= !empty($module['enabled_for_admins']) ? 'checked' : '' ?>>
                            Actif
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p id="module-save-feedback" style="margin-top:12px;color:#64748b;font-size:13px;"></p>
    </section>

    <section class="card" style="grid-column:2 / 3;">
        <h3 class="card-title" style="margin-bottom:10px;">Users actifs (10 min)</h3>
        <?php if (!$activeUsers): ?>
            <p style="color:#6b7280;">Aucun user actif détecté.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($activeUsers as $active): ?>
                    <article style="border:1px solid #e5e7eb;border-radius:10px;padding:10px;">
                        <div style="font-weight:600;"><?= htmlspecialchars($active['name'] ?: 'Utilisateur') ?></div>
                        <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($active['email']) ?></div>
                        <div style="font-size:12px;margin:6px 0;">Page: <code><?= htmlspecialchars($active['page_url']) ?></code></div>
                        <button class="request-access-btn"
                                data-user-id="<?= (int) $active['user_id'] ?>"
                                data-page-url="<?= htmlspecialchars($active['page_url']) ?>"
                                style="background:#111827;color:#fff;border:0;padding:8px 10px;border-radius:8px;cursor:pointer;">
                            Demander accès
                        </button>
                        <div class="request-status" style="margin-top:6px;font-size:12px;color:#6b7280;"></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    const feedback = document.getElementById('module-save-feedback');

    function setFeedback(text, isError) {
        if (!feedback) return;
        feedback.style.color = isError ? '#dc2626' : '#64748b';
        feedback.textContent = text;
    }

    function moduleRowState(moduleName) {
        const users = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="users"]');
        const admins = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="admins"]');
        return {
            users: !!(users && users.checked),
            admins: !!(admins && admins.checked),
        };
    }

    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const moduleName = this.getAttribute('data-module');
            const state = moduleRowState(moduleName);

            const body = new URLSearchParams();
            body.set('module_name', moduleName);
            body.set('enabled_for_users', state.users ? '1' : '0');
            body.set('enabled_for_admins', state.admins ? '1' : '0');

            fetch('/admin?module=superadmin&action=toggle_module', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.ok) {
                        setFeedback(json.message || 'Erreur lors de la sauvegarde.', true);
                        return;
                    }
                    setFeedback('Module "' + moduleName + '" mis à jour.', false);
                })
                .catch(function () {
                    setFeedback('Impossible d\'enregistrer la modification.', true);
                });
        });
    });

    document.querySelectorAll('.request-access-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const pageUrl = this.getAttribute('data-page-url') || '';
            const statusEl = this.parentElement.querySelector('.request-status');

            const body = new URLSearchParams();
            body.set('user_id', userId);
            body.set('page_url', pageUrl);

            fetch('/admin?module=superadmin&action=page_request', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.ok) throw new Error('request_failed');
                    statusEl.textContent = 'Demande envoyée (#' + json.request_id + '). En attente...';
                    pollRequestStatus(json.request_id, statusEl);
                })
                .catch(function () {
                    statusEl.textContent = 'Impossible d\'envoyer la demande.';
                });
        });
    });

    function pollRequestStatus(requestId, statusEl) {
        let attempts = 0;
        const timer = setInterval(function () {
            attempts += 1;
            fetch('/admin?module=superadmin&action=poll_request&request_id=' + encodeURIComponent(requestId))
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.ok || !json.request) {
                        if (attempts > 24) clearInterval(timer);
                        return;
                    }

                    const status = json.request.status;
                    if (status === 'allowed') {
                        statusEl.textContent = '✅ Autorisé par l\'utilisateur.';
                        clearInterval(timer);
                    } else if (status === 'denied') {
                        statusEl.textContent = '❌ Refusé par l\'utilisateur.';
                        clearInterval(timer);
                    } else {
                        statusEl.textContent = 'Demande envoyée (#' + requestId + '). En attente...';
                    }
                })
                .catch(function () {
                    if (attempts > 24) clearInterval(timer);
                });

            if (attempts > 24) {
                statusEl.textContent = 'Statut inconnu (timeout).';
                clearInterval(timer);
            }
        }, 5000);
    }
})();
</script>
