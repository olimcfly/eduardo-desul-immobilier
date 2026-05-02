<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/services/ModuleService.php';

$modules = ModuleService::getAllSettings(ModuleService::listAvailableModules());

$users = [];
try {
    $stmt = db()->query("SELECT id, name, email, role, is_active, created_at FROM users WHERE role != 'superadmin' ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Superadmin dashboard users: ' . $e->getMessage());
}

$currentUser = Auth::user();
$devModeOn = setting('site_development_mode', '1') === '1';
$modCount = count($modules);
$userCount = count($users);
$activeUsers = count(array_filter($users, static fn ($u) => !empty($u['is_active'])));
?>
<style>
.sa-hub { max-width: 1180px; margin: 0 auto; }
.sa-hero {
    background: linear-gradient(135deg, #1a0f2e 0%, #2d1b4e 40%, #4a3570 100%);
    border-radius: 16px;
    padding: 1.35rem 1.5rem 1.5rem;
    color: #fff;
    box-shadow: 0 14px 36px rgba(26, 15, 46, .35);
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
}
.sa-hero::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -8%;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(201, 168, 76, .22) 0%, transparent 70%);
    pointer-events: none;
}
.sa-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #f5e6b8;
    border: 1px solid rgba(201, 168, 76, .45);
    background: rgba(201, 168, 76, .12);
    border-radius: 999px;
    padding: .28rem .75rem;
    margin-bottom: .65rem;
}
.sa-hero h1 {
    margin: 0 0 .45rem;
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
}
.sa-hero p { margin: 0; color: rgba(255, 255, 255, .78); font-size: .95rem; line-height: 1.55; max-width: 640px; }
.sa-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    margin-top: 1rem;
}
.sa-pill {
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 10px;
    padding: .4rem .75rem;
    font-size: .82rem;
    font-weight: 600;
    color: rgba(255, 255, 255, .92);
}
.sa-pill strong { color: #c9a84c; font-weight: 800; margin-right: .25rem; }

.sa-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .sa-grid { grid-template-columns: 1fr; }
}

.sa-card {
    background: #fff;
    border: 1px solid #e8ecf4;
    border-radius: 14px;
    padding: 1.1rem 1.15rem 1.2rem;
    box-shadow: 0 4px 18px rgba(15, 34, 55, .06);
}
.sa-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: 1rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid #f1f5f9;
}
.sa-card-head h2 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 700;
    color: #0f2237;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.sa-card-head h2 i { color: #c9a84c; font-size: 1.05rem; }

.sa-table-wrap { overflow-x: auto; margin: 0 -.15rem; }
.sa-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .86rem;
}
.sa-table th {
    text-align: left;
    padding: .55rem .65rem;
    color: #64748b;
    font-weight: 600;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.sa-table td {
    padding: .65rem .65rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.sa-table tr:last-child td { border-bottom: 0; }
.sa-mod-name { font-weight: 600; color: #1e293b; }
.sa-toggle-cell { text-align: center; }
.sa-toggle-cell input[type="checkbox"] {
    width: 1.15rem;
    height: 1.15rem;
    accent-color: #0f2237;
    cursor: pointer;
}
.sa-legend {
    font-size: .78rem;
    color: #64748b;
    margin-top: .75rem;
    line-height: 1.45;
}

.sa-stack { display: flex; flex-direction: column; gap: 1.25rem; }

.sa-switch-row {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: .35rem 0;
}
.sa-switch {
    position: relative;
    width: 44px;
    height: 26px;
    flex-shrink: 0;
}
.sa-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.sa-switch-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #cbd5e1;
    border-radius: 999px;
    transition: background .2s;
}
.sa-switch-slider::before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
    transition: transform .2s;
}
.sa-switch input:checked + .sa-switch-slider {
    background: linear-gradient(90deg, #0f2237, #1a3a5c);
}
.sa-switch input:checked + .sa-switch-slider::before {
    transform: translateX(18px);
}
.sa-switch-label strong { display: block; font-size: .92rem; color: #0f172a; margin-bottom: .2rem; }
.sa-switch-label span { font-size: .8rem; color: #64748b; line-height: 1.45; }

.sa-form .sa-field { margin-bottom: .85rem; }
.sa-form label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: .35rem;
}
.sa-form input[type="text"] {
    width: 100%;
    padding: .55rem .75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: .9rem;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.sa-form input:focus {
    outline: none;
    border-color: #c9a84c;
    box-shadow: 0 0 0 3px rgba(201, 168, 76, .15);
}
.sa-form input:disabled { background: #f8fafc; color: #94a3b8; }
.sa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
    color: #fff;
    border: 0;
    padding: .55rem 1.1rem;
    border-radius: 10px;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
}
.sa-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(15, 34, 55, .2); }

.sa-user-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .85rem;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    background: #fbfcfe;
    transition: border-color .15s, box-shadow .15s;
}
.sa-user-card:hover {
    border-color: #e2e8f0;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
}
.sa-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0f2237, #334155);
    color: #c9a84c;
    font-weight: 800;
    font-size: .85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sa-user-main { flex: 1; min-width: 0; }
.sa-user-name { font-weight: 700; font-size: .9rem; color: #0f172a; }
.sa-user-email { font-size: .78rem; color: #64748b; word-break: break-all; }
.sa-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .35rem; }
.sa-badge {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: .2rem .5rem;
    border-radius: 6px;
}
.sa-badge--admin { background: #dbeafe; color: #1d4ed8; }
.sa-badge--user { background: #f1f5f9; color: #475569; }
.sa-badge--off { background: #fee2e2; color: #b91c1c; }
.sa-user-actions { flex-shrink: 0; }
.sa-btn-user {
    border: 0;
    padding: .45rem .75rem;
    border-radius: 8px;
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s;
}
.sa-btn-user:hover { opacity: .92; }
.sa-btn-user--deactivate { background: #fef9c3; color: #854d0e; }
.sa-btn-user--activate { background: #dcfce7; color: #166534; }

.sa-feedback { font-size: .8rem; min-height: 1.25rem; margin-top: .5rem; color: #64748b; }
.sa-note {
    margin-top: 1.25rem;
    padding: .85rem 1rem;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: .82rem;
    color: #475569;
    line-height: 1.5;
}
.sa-note strong { color: #0f2237; }
</style>

<div class="sa-hub">
    <header class="sa-hero">
        <div class="sa-hero-badge"><i class="fas fa-crown"></i> Espace superadministrateur</div>
        <h1>Pilotage plateforme</h1>
        <p>Activez ou masquez les modules par rôle, contrôlez le bandeau « site en développement », gérez les comptes et mettez à jour votre identité d’administration.</p>
        <div class="sa-hero-meta">
            <span class="sa-pill"><strong><?= (int) $modCount ?></strong> modules</span>
            <span class="sa-pill"><strong><?= (int) $userCount ?></strong> comptes</span>
            <span class="sa-pill"><strong><?= (int) $activeUsers ?></strong> actifs</span>
            <span class="sa-pill"><strong><?= $devModeOn ? 'ON' : 'OFF' ?></strong> mode dev</span>
        </div>
    </header>

    <div class="sa-grid">
        <section class="sa-card" aria-labelledby="sa-modules-title">
            <div class="sa-card-head">
                <h2 id="sa-modules-title"><i class="fas fa-puzzle-piece"></i> Visibilité des modules</h2>
            </div>
            <p class="sa-legend" style="margin-top:0;margin-bottom:.85rem">Coches <strong>Users</strong> : comptes rôle « user » (site public / propriétaire). <strong>Admins</strong> : équipe back-office.</p>
            <div class="sa-table-wrap">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th class="sa-toggle-cell">Users</th>
                            <th class="sa-toggle-cell">Admins</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td class="sa-mod-name"><?= htmlspecialchars((string) ($module['module_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="sa-toggle-cell">
                                <input type="checkbox" class="module-toggle" data-target="users"
                                       data-module="<?= htmlspecialchars((string) ($module['module_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       <?= !empty($module['enabled_for_users']) ? 'checked' : '' ?>
                                       aria-label="Module <?= htmlspecialchars((string) ($module['module_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> pour les utilisateurs">
                            </td>
                            <td class="sa-toggle-cell">
                                <input type="checkbox" class="module-toggle" data-target="admins"
                                       data-module="<?= htmlspecialchars((string) ($module['module_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       <?= !empty($module['enabled_for_admins']) ? 'checked' : '' ?>
                                       aria-label="Module <?= htmlspecialchars((string) ($module['module_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> pour les admins">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p id="module-save-feedback" class="sa-feedback" role="status" aria-live="polite"></p>
        </section>

        <div class="sa-stack">
            <section class="sa-card" aria-labelledby="sa-dev-title">
                <div class="sa-card-head">
                    <h2 id="sa-dev-title"><i class="fas fa-screwdriver-wrench"></i> Paramètres site</h2>
                </div>
                <div class="sa-switch-row">
                    <label class="sa-switch" for="dev-mode-toggle">
                        <input type="checkbox" id="dev-mode-toggle" <?= $devModeOn ? 'checked' : '' ?>>
                        <span class="sa-switch-slider" aria-hidden="true"></span>
                    </label>
                    <div class="sa-switch-label">
                        <strong>Bandeau « Site en développement »</strong>
                        <span>Affiché en haut de l’admin pour rappeler que la configuration est en cours. Idéal avant la mise en production.</span>
                    </div>
                </div>
                <p id="dev-mode-feedback" class="sa-feedback" role="status" aria-live="polite"></p>
            </section>

            <section class="sa-card" aria-labelledby="sa-profile-title">
                <div class="sa-card-head">
                    <h2 id="sa-profile-title"><i class="fas fa-user-shield"></i> Votre profil superadmin</h2>
                </div>
                <form id="superadmin-profile-form" class="sa-form">
                    <div class="sa-field">
                        <label for="superadmin-name">Nom affiché</label>
                        <input type="text" id="superadmin-name" name="name"
                               value="<?= htmlspecialchars((string) ($currentUser['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               autocomplete="name" maxlength="120">
                    </div>
                    <div class="sa-field">
                        <label for="superadmin-email-readonly">E-mail (lecture seule)</label>
                        <input type="text" id="superadmin-email-readonly"
                               value="<?= htmlspecialchars((string) ($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               disabled readonly>
                    </div>
                    <button type="submit" class="sa-btn"><i class="fas fa-floppy-disk"></i> Enregistrer</button>
                    <span id="profile-feedback" class="sa-feedback" role="status" aria-live="polite"></span>
                </form>
            </section>

            <section class="sa-card" aria-labelledby="sa-users-title">
                <div class="sa-card-head">
                    <h2 id="sa-users-title"><i class="fas fa-users"></i> Comptes (hors superadmin)</h2>
                </div>
                <?php if ($users === []): ?>
                    <p style="margin:0;color:#64748b;font-size:.9rem;">Aucun utilisateur enregistré.</p>
                <?php else: ?>
                    <div class="sa-stack" style="gap:.65rem;">
                    <?php foreach ($users as $u):
                        $rawName = trim((string) ($u['name'] ?? ''));
                        $initials = strtoupper(substr($rawName !== '' ? $rawName : (string) ($u['email'] ?? ''), 0, 2));
                        if ($initials === '') {
                            $initials = '—';
                        }
                        $isAdmin = ($u['role'] ?? '') === 'admin';
                        ?>
                        <div class="sa-user-card">
                            <div class="sa-user-avatar" aria-hidden="true"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="sa-user-main">
                                <div class="sa-user-name"><?= htmlspecialchars((string) ($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="sa-user-email"><?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="sa-badges">
                                    <span class="sa-badge <?= $isAdmin ? 'sa-badge--admin' : 'sa-badge--user' ?>"><?= htmlspecialchars((string) ($u['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (empty($u['is_active'])): ?>
                                        <span class="sa-badge sa-badge--off">Inactif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="sa-user-actions">
                                <button type="button" class="sa-btn-user <?= !empty($u['is_active']) ? 'sa-btn-user--deactivate' : 'sa-btn-user--activate' ?> toggle-active-btn"
                                        data-user-id="<?= (int) ($u['id'] ?? 0) ?>"
                                        data-active="<?= !empty($u['is_active']) ? '1' : '0' ?>">
                                    <?= !empty($u['is_active']) ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <div class="sa-note">
        <strong>Accès session utilisateur :</strong> depuis le site connecté en tant que « user », une demande d’accès admin peut s’afficher si vous initiez une requête côté superadmin (API <code>page_request</code> / <code>poll_request</code>). Les comptes inactifs ne peuvent plus se connecter.
    </div>
</div>

<script>
(function () {
    const BASE = '/admin';

    const devModeFeedback = document.getElementById('dev-mode-feedback');
    const devModeToggle = document.getElementById('dev-mode-toggle');

    if (devModeToggle) {
        devModeToggle.addEventListener('change', function () {
            const enabled = this.checked ? '1' : '0';
            const body = new URLSearchParams();
            body.set('site_development_mode', enabled);

            fetch(BASE + '?module=superadmin&action=update_setting', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                if (!json.ok) {
                    devModeFeedback.style.color = '#b91c1c';
                    devModeFeedback.textContent = json.message || 'Erreur.';
                    return;
                }
                devModeFeedback.style.color = '#15803d';
                devModeFeedback.textContent = enabled === '1' ? 'Bandeau activé.' : 'Bandeau désactivé.';
                setTimeout(() => { devModeFeedback.textContent = ''; }, 3200);
            })
            .catch(() => {
                devModeFeedback.style.color = '#b91c1c';
                devModeFeedback.textContent = 'Erreur réseau.';
            });
        });
    }

    const feedback = document.getElementById('module-save-feedback');

    function setFeedback(text, isError) {
        if (!feedback) return;
        feedback.style.color = isError ? '#b91c1c' : '#15803d';
        feedback.textContent = text;
        setTimeout(() => { feedback.textContent = ''; }, 3200);
    }

    function moduleRowState(moduleName) {
        const users = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="users"]');
        const admins = document.querySelector('.module-toggle[data-module="' + moduleName + '"][data-target="admins"]');
        return { users: !!(users && users.checked), admins: !!(admins && admins.checked) };
    }

    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const moduleName = this.getAttribute('data-module');
            const state = moduleRowState(moduleName);
            const body = new URLSearchParams();
            body.set('module_name', moduleName);
            body.set('enabled_for_users', state.users ? '1' : '0');
            body.set('enabled_for_admins', state.admins ? '1' : '0');

            fetch(BASE + '?module=superadmin&action=toggle_module', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                if (!json.ok) {
                    setFeedback(json.message || 'Erreur.', true);
                    return;
                }
                setFeedback('Module « ' + moduleName + ' » mis à jour.', false);
            })
            .catch(() => setFeedback('Erreur réseau.', true));
        });
    });

    const profileForm = document.getElementById('superadmin-profile-form');
    const profileFeedback = document.getElementById('profile-feedback');

    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('superadmin-name').value.trim();
            if (!name) {
                profileFeedback.style.color = '#b91c1c';
                profileFeedback.textContent = 'Le nom ne peut pas être vide.';
                return;
            }
            const body = new URLSearchParams();
            body.set('name', name);

            fetch(BASE + '?module=superadmin&action=update_profile', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
            .then(r => r.json())
            .then(json => {
                profileFeedback.style.color = json.ok ? '#15803d' : '#b91c1c';
                profileFeedback.textContent = json.message || (json.ok ? 'Enregistré.' : 'Erreur.');
            })
            .catch(() => {
                profileFeedback.style.color = '#b91c1c';
                profileFeedback.textContent = 'Erreur réseau.';
            });
        });
    }

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
                if (json.ok) {
                    location.reload();
                } else {
                    window.alert(json.message || 'Erreur.');
                }
            })
            .catch(() => window.alert('Erreur réseau.'));
        });
    });
})();
</script>
