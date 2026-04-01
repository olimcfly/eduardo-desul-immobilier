<?php
/**
 * Gestion des Utilisateurs — Super User uniquement
 * /admin/modules/system/users/index.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès direct interdit');
}

// Seul le Super User peut accéder
if (!isSuperUser()) {
    echo '<div class="es"><i class="fas fa-lock"></i><h3>Accès restreint</h3><p>Seul le Super Administrateur peut gérer les utilisateurs.</p></div>';
    return;
}
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';

$pdo = Database::getInstance();
$message = '';
$messageType = '';

// ── Traitement des actions POST ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Vérifier CSRF
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide.';
        $messageType = 'error';
    } else {

        // ── Créer un nouvel admin ────────────────────────────
        if ($action === 'create_admin') {
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $name  = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email invalide.';
                $messageType = 'error';
            } else {
                // Vérifier doublon
                $check = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $message = 'Cet email existe déjà.';
                    $messageType = 'error';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO admins (email, role, name, phone, is_active) VALUES (?, 'admin', ?, ?, 1)");
                    $stmt->execute([$email, $name, $phone]);
                    $newAdminId = $pdo->lastInsertId();

                    // Sauvegarder les modules autorisés
                    if (!empty($_POST['modules']) && is_array($_POST['modules'])) {
                        $ins = $pdo->prepare("INSERT INTO admin_module_permissions (admin_id, module_slug, is_allowed, granted_by) VALUES (?, ?, 1, ?)");
                        foreach ($_POST['modules'] as $slug) {
                            $ins->execute([$newAdminId, $slug, $_SESSION['admin_id']]);
                        }
                    }

                    $message = "Administrateur \"$name\" créé avec succès.";
                    $messageType = 'success';
                }
            }
        }

        // ── Mettre à jour les permissions ────────────────────
        elseif ($action === 'update_permissions') {
            $targetId = (int)($_POST['admin_id'] ?? 0);

            // Ne pas modifier un Super User
            $check = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
            $check->execute([$targetId]);
            $target = $check->fetch();

            if (!$target || $target['role'] === 'superuser') {
                $message = 'Action non autorisée.';
                $messageType = 'error';
            } else {
                // Supprimer les anciennes permissions
                $pdo->prepare("DELETE FROM admin_module_permissions WHERE admin_id = ?")->execute([$targetId]);

                // Ajouter les nouvelles
                if (!empty($_POST['modules']) && is_array($_POST['modules'])) {
                    $ins = $pdo->prepare("INSERT INTO admin_module_permissions (admin_id, module_slug, is_allowed, granted_by) VALUES (?, ?, 1, ?)");
                    foreach ($_POST['modules'] as $slug) {
                        $ins->execute([$targetId, $slug, $_SESSION['admin_id']]);
                    }
                }

                $message = 'Permissions mises à jour.';
                $messageType = 'success';
            }
        }

        // ── Activer / Désactiver un admin ────────────────────
        elseif ($action === 'toggle_active') {
            $targetId = (int)($_POST['admin_id'] ?? 0);
            $newState = (int)($_POST['is_active'] ?? 0);

            // Ne pas se désactiver soi-même
            if ($targetId === (int)$_SESSION['admin_id']) {
                $message = 'Vous ne pouvez pas vous désactiver vous-même.';
                $messageType = 'error';
            } else {
                $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ? AND role != 'superuser'")
                    ->execute([$newState, $targetId]);
                $message = $newState ? 'Utilisateur activé.' : 'Utilisateur désactivé.';
                $messageType = 'success';
            }
        }

        // ── Supprimer un admin ───────────────────────────────
        elseif ($action === 'delete_admin') {
            $targetId = (int)($_POST['admin_id'] ?? 0);

            if ($targetId === (int)$_SESSION['admin_id']) {
                $message = 'Vous ne pouvez pas supprimer votre propre compte.';
                $messageType = 'error';
            } else {
                $check = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
                $check->execute([$targetId]);
                $target = $check->fetch();

                if ($target && $target['role'] === 'superuser') {
                    $message = 'Impossible de supprimer un Super Administrateur.';
                    $messageType = 'error';
                } else {
                    $pdo->prepare("DELETE FROM admin_module_permissions WHERE admin_id = ?")->execute([$targetId]);
                    $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$targetId]);
                    $message = 'Utilisateur supprimé.';
                    $messageType = 'success';
                }
            }
        }
    }
}

// ── Charger la liste des admins ──────────────────────────────
$admins = $pdo->query("SELECT * FROM admins ORDER BY role DESC, name ASC")->fetchAll();

// ── Charger les permissions existantes ───────────────────────
$allPermissions = [];
try {
    $perms = $pdo->query("SELECT admin_id, module_slug FROM admin_module_permissions WHERE is_allowed = 1")->fetchAll();
    foreach ($perms as $p) {
        $allPermissions[$p['admin_id']][] = $p['module_slug'];
    }
} catch (Exception $e) {}

// ── Liste complète des modules gérables ──────────────────────
$manageableModules = [
    'Mon Site' => ['pages', 'secteurs', 'guide-local'],
    'Contenu' => ['articles', 'journal', 'ressources', 'sections'],
    'Design' => ['builder', 'templates', 'headers', 'footers', 'menus'],
    'Acquisition' => ['captures', 'leads', 'scoring', 'sequences', 'campagnes'],
    'Mes Biens' => ['properties', 'estimation', 'rdv', 'financement'],
    'Mes Clients' => ['crm', 'messagerie', 'emails'],
    'Visibilité' => ['seo', 'seo-semantic', 'local-seo', 'analytics'],
    'Réseaux' => ['reseaux-sociaux', 'facebook', 'instagram', 'linkedin', 'tiktok', 'gmb', 'image-editor', 'scraper-gmb'],
    'Stratégie' => ['launchpad', 'neuropersona', 'seo-strategie', 'analyse-marche'],
    'IA' => ['ai', 'ai-prompts', 'agents'],
    'Réglages' => ['modules', 'settings', 'api-keys', 'ai-settings', 'maintenance', 'license'],
];

// Labels lisibles pour les modules
$moduleLabels = [
    'pages'=>'Pages', 'secteurs'=>'Quartiers', 'guide-local'=>'Guide local',
    'articles'=>'Articles', 'journal'=>'Planning', 'ressources'=>'Ressources', 'sections'=>'Sections',
    'builder'=>'Éditeur de site', 'templates'=>'Modèles', 'headers'=>'Headers', 'footers'=>'Footers', 'menus'=>'Menus',
    'captures'=>'Pages capture', 'leads'=>'Leads', 'scoring'=>'Scoring', 'sequences'=>'Séquences', 'campagnes'=>'Campagnes',
    'properties'=>'Biens', 'estimation'=>'Estimations', 'rdv'=>'Rendez-vous', 'financement'=>'Financement',
    'crm'=>'Contacts', 'messagerie'=>'Messagerie', 'emails'=>'Emails auto',
    'seo'=>'SEO', 'seo-semantic'=>'Mots-clés', 'local-seo'=>'SEO Local', 'analytics'=>'Analytics',
    'reseaux-sociaux'=>'Vue réseaux', 'facebook'=>'Facebook', 'instagram'=>'Instagram', 'linkedin'=>'LinkedIn',
    'tiktok'=>'TikTok', 'gmb'=>'GMB', 'image-editor'=>'Éditeur images', 'scraper-gmb'=>'Scraper GMB',
    'launchpad'=>'Lancement', 'neuropersona'=>'Persona', 'seo-strategie'=>'SEO Stratégie', 'analyse-marche'=>'Analyse marché',
    'ai'=>'Assistant IA', 'ai-prompts'=>'Prompts', 'agents'=>'Agents IA',
    'modules'=>'Modules', 'settings'=>'Configuration', 'api-keys'=>'Clés API', 'ai-settings'=>'Config IA',
    'maintenance'=>'Maintenance', 'license'=>'Licence',
];
?>

<style>
.users-page { max-width: 1100px; }
.users-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; margin-top: 16px; }

.user-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    padding: 20px;
    position: relative;
    transition: box-shadow .2s;
}
.user-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.user-card.superuser { border-left: 4px solid #6366f1; }
.user-card.inactive { opacity: .55; }

.user-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.user-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 18px; color: #fff; flex-shrink: 0;
}
.user-avatar.su { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.user-avatar.admin { background: linear-gradient(135deg, #0891b2, #06b6d4); }
.user-name { font-weight: 700; font-size: 15px; }
.user-email { font-size: 12px; color: var(--text-3, #94a3b8); }
.user-role-badge {
    display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 4px; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px;
}
.user-role-badge.su { background: rgba(99,102,241,.15); color: #6366f1; }
.user-role-badge.admin { background: rgba(8,145,178,.15); color: #0891b2; }

.user-modules { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border, #e2e8f0); }
.user-modules-label { font-size: 11px; font-weight: 600; color: var(--text-3, #94a3b8); text-transform: uppercase; margin-bottom: 6px; }
.module-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.module-tag {
    font-size: 10px; padding: 2px 6px; border-radius: 4px;
    background: rgba(99,102,241,.08); color: var(--text-2, #64748b);
}

.user-actions { display: flex; gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border, #e2e8f0); }
.user-actions button, .user-actions .btn-action {
    font-size: 11px; padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border, #e2e8f0);
    background: var(--card-bg, #fff); cursor: pointer; font-weight: 600; transition: all .15s;
    text-decoration: none; color: var(--text-2, #64748b);
}
.user-actions button:hover, .user-actions .btn-action:hover { background: var(--hover, #f1f5f9); }
.user-actions .btn-danger { color: #dc2626; border-color: #fecaca; }
.user-actions .btn-danger:hover { background: #fef2f2; }

/* Modal */
.modal-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal {
    background: var(--card-bg, #fff); border-radius: 14px; padding: 28px;
    width: 90%; max-width: 680px; max-height: 85vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal h3 { margin: 0 0 20px; font-size: 18px; }
.modal label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: var(--text-2, #64748b); }
.modal input[type="text"], .modal input[type="email"], .modal input[type="tel"] {
    width: 100%; padding: 10px 12px; border: 1px solid var(--border, #e2e8f0);
    border-radius: 8px; font-size: 14px; margin-bottom: 12px; box-sizing: border-box;
}
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; }
.modal-actions button {
    padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px;
    cursor: pointer; border: none; transition: all .15s;
}
.modal-actions .btn-cancel { background: var(--hover, #f1f5f9); color: var(--text-2, #64748b); }
.modal-actions .btn-save { background: #6366f1; color: #fff; }
.modal-actions .btn-save:hover { background: #4f46e5; }

.modules-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px; margin-top: 8px;
}
.module-group h4 { font-size: 12px; color: var(--text-3, #94a3b8); margin: 0 0 6px; text-transform: uppercase; }
.module-group label {
    display: flex; align-items: center; gap: 6px; font-size: 13px;
    padding: 3px 0; cursor: pointer; font-weight: normal; color: var(--text-1, #1e293b);
}
.module-group input[type="checkbox"] { margin: 0; }

.select-all-bar { display: flex; gap: 10px; margin: 10px 0; }
.select-all-bar button {
    font-size: 11px; padding: 4px 10px; border-radius: 6px;
    border: 1px solid var(--border, #e2e8f0); background: var(--card-bg, #fff);
    cursor: pointer; font-weight: 600; color: var(--text-2, #64748b);
}

.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 500; }
.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="users-page">

<div class="page-hd anim">
    <div>
        <h1><i class="fas fa-users-gear" style="color:#6366f1;margin-right:8px"></i>Gestion des Utilisateurs</h1>
        <div class="page-hd-sub">Gérez les accès et permissions des administrateurs</div>
    </div>
    <button class="btn btn-p btn-sm" onclick="openModal('create')">
        <i class="fas fa-user-plus"></i> Nouvel administrateur
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
<?php endif; ?>

<!-- Stats rapides -->
<div class="grid-3 anim" style="margin-bottom: 16px">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
            <i class="fas fa-crown"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= count(array_filter($admins, fn($a) => ($a['role'] ?? '') === 'superuser')) ?></div>
            <div class="stat-label">Super User</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(8,145,178,.1);color:#0891b2">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= count(array_filter($admins, fn($a) => ($a['role'] ?? '') === 'admin')) ?></div>
            <div class="stat-label">Administrateurs</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626">
            <i class="fas fa-user-slash"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= count(array_filter($admins, fn($a) => !($a['is_active'] ?? 1))) ?></div>
            <div class="stat-label">Désactivés</div>
        </div>
    </div>
</div>

<!-- Liste des utilisateurs -->
<div class="users-grid anim d1">
    <?php foreach ($admins as $admin):
        $isSU = ($admin['role'] ?? '') === 'superuser';
        $isActive = ($admin['is_active'] ?? 1);
        $adminPerms = $allPermissions[$admin['id']] ?? [];
        $initial = strtoupper(mb_substr($admin['name'] ?? $admin['email'], 0, 1));
    ?>
    <div class="user-card<?= $isSU ? ' superuser' : '' ?><?= !$isActive ? ' inactive' : '' ?>">
        <div class="user-header">
            <div class="user-avatar <?= $isSU ? 'su' : 'admin' ?>"><?= $initial ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($admin['name'] ?? 'Sans nom') ?></div>
                <div class="user-email"><?= htmlspecialchars($admin['email']) ?></div>
                <span class="user-role-badge <?= $isSU ? 'su' : 'admin' ?>">
                    <?= $isSU ? 'Super User' : 'Admin' ?>
                </span>
                <?php if (!$isActive): ?>
                    <span class="user-role-badge" style="background:rgba(220,38,38,.15);color:#dc2626;">Désactivé</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$isSU): ?>
        <div class="user-modules">
            <div class="user-modules-label">
                <?= count($adminPerms) ?> module<?= count($adminPerms) > 1 ? 's' : '' ?> autorisé<?= count($adminPerms) > 1 ? 's' : '' ?>
            </div>
            <div class="module-tags">
                <?php if (empty($adminPerms)): ?>
                    <span class="module-tag" style="color:#dc2626">Aucun module</span>
                <?php else: ?>
                    <?php foreach (array_slice($adminPerms, 0, 8) as $slug): ?>
                        <span class="module-tag"><?= htmlspecialchars($moduleLabels[$slug] ?? $slug) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($adminPerms) > 8): ?>
                        <span class="module-tag">+<?= count($adminPerms) - 8 ?> autres</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="user-actions">
            <button onclick="openModal('permissions', <?= $admin['id'] ?>, <?= htmlspecialchars(json_encode($adminPerms)) ?>)">
                <i class="fas fa-key"></i> Modules
            </button>
            <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="action" value="toggle_active">
                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1' ?>">
                <button type="submit" class="<?= $isActive ? 'btn-danger' : '' ?>">
                    <i class="fas fa-<?= $isActive ? 'ban' : 'check-circle' ?>"></i>
                    <?= $isActive ? 'Désactiver' : 'Activer' ?>
                </button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="action" value="delete_admin">
                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                <button type="submit" class="btn-danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <?php else: ?>
        <div class="user-modules">
            <div class="user-modules-label">Accès complet</div>
            <div class="module-tags">
                <span class="module-tag" style="background:rgba(99,102,241,.15);color:#6366f1">Tous les modules</span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($admin['last_login'])): ?>
        <div style="font-size:11px;color:var(--text-3,#94a3b8);margin-top:8px">
            Dernière connexion : <?= date('d/m/Y H:i', strtotime($admin['last_login'])) ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

</div>

<!-- Modal Création -->
<div class="modal-overlay" id="modalCreate">
    <div class="modal">
        <h3><i class="fas fa-user-plus" style="color:#6366f1;margin-right:8px"></i>Nouvel administrateur</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="create_admin">

            <label>Nom complet</label>
            <input type="text" name="name" placeholder="Jean Dupont" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="jean@example.com" required>

            <label>Téléphone</label>
            <input type="tel" name="phone" placeholder="+33612345678">

            <label style="margin-top:16px;margin-bottom:8px">Modules autorisés</label>
            <div class="select-all-bar">
                <button type="button" onclick="toggleAll(this.closest('form'), true)">Tout sélectionner</button>
                <button type="button" onclick="toggleAll(this.closest('form'), false)">Tout désélectionner</button>
            </div>
            <div class="modules-grid">
                <?php foreach ($manageableModules as $group => $slugs): ?>
                <div class="module-group">
                    <h4><?= $group ?></h4>
                    <?php foreach ($slugs as $slug): ?>
                    <label>
                        <input type="checkbox" name="modules[]" value="<?= $slug ?>">
                        <?= $moduleLabels[$slug] ?? $slug ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn-save"><i class="fas fa-check"></i> Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Permissions -->
<div class="modal-overlay" id="modalPermissions">
    <div class="modal">
        <h3><i class="fas fa-key" style="color:#6366f1;margin-right:8px"></i>Modifier les modules</h3>
        <form method="POST" id="permissionsForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="update_permissions">
            <input type="hidden" name="admin_id" id="permAdminId" value="">

            <div class="select-all-bar">
                <button type="button" onclick="toggleAll(this.closest('form'), true)">Tout sélectionner</button>
                <button type="button" onclick="toggleAll(this.closest('form'), false)">Tout désélectionner</button>
            </div>
            <div class="modules-grid">
                <?php foreach ($manageableModules as $group => $slugs): ?>
                <div class="module-group">
                    <h4><?= $group ?></h4>
                    <?php foreach ($slugs as $slug): ?>
                    <label>
                        <input type="checkbox" name="modules[]" value="<?= $slug ?>" class="perm-checkbox" data-slug="<?= $slug ?>">
                        <?= $moduleLabels[$slug] ?? $slug ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(type, adminId, currentPerms) {
    closeModals();
    if (type === 'create') {
        document.getElementById('modalCreate').classList.add('active');
    } else if (type === 'permissions') {
        document.getElementById('permAdminId').value = adminId;
        // Reset toutes les checkboxes
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        // Cocher les permissions actuelles
        if (currentPerms && Array.isArray(currentPerms)) {
            currentPerms.forEach(slug => {
                const cb = document.querySelector('.perm-checkbox[data-slug="' + slug + '"]');
                if (cb) cb.checked = true;
            });
        }
        document.getElementById('modalPermissions').classList.add('active');
    }
}

function closeModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
}

function toggleAll(form, state) {
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = state);
}

// Fermer modal en cliquant à l'extérieur
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModals();
    });
});
</script>
