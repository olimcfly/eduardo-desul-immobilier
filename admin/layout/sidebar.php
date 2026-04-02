<?php
/**
 * SIDEBAR — IMMO LOCAL+ (Version Prestige SaaS)
 * /admin/layout/sidebar.php
 *
 * Spécifications:
 * - 7 entrées de menu principales avec sous-menus
 * - Icônes FontAwesome 6
 * - État "actif" basé sur l'URL courante
 * - Responsive: Collapse en icônes sur mobile (<768px)
 * - Badge "Nouveau" pour les fonctionnalités récentes
 */

$currentModule = $_GET['page'] ?? $_GET['module'] ?? 'dashboard';

// Menu principal avec sous-menus (7 entrées max selon spécifications)
$sidebarMenu = [
    [
        'id'          => 'dashboard',
        'label'       => 'Tableau de bord',
        'icon'        => 'fa-tachometer-alt',
        'url'         => '?page=dashboard',
        'description' => 'Vue d\'ensemble de vos activités',
        'badge'       => null,
    ],
    [
        'id'          => 'estimation',
        'label'       => 'Estimations',
        'icon'        => 'fa-calculator',
        'url'         => '?page=estimation',
        'description' => 'Créer et gérer des estimations immobilières',
        'badge'       => null,
    ],
    [
        'id'          => 'properties',
        'label'       => 'Biens',
        'icon'        => 'fa-home',
        'url'         => '?page=properties',
        'description' => 'Liste des biens en gestion',
        'badge'       => null,
        'submenu'     => [
            ['label' => 'Liste des biens', 'url' => '?page=properties', 'icon' => 'fa-list'],
            ['label' => 'Ajouter un bien', 'url' => '?page=properties-edit', 'icon' => 'fa-plus'],
            ['label' => 'Prise de RDV', 'url' => '?page=rdv', 'icon' => 'fa-calendar-alt'],
        ],
    ],
    [
        'id'          => 'crm',
        'label'       => 'Clients',
        'icon'        => 'fa-users',
        'url'         => '?page=crm',
        'description' => 'Gestion des propriétaires et locataires',
        'badge'       => null,
        'submenu'     => [
            ['label' => 'Liste des clients', 'url' => '?page=crm', 'icon' => 'fa-list'],
            ['label' => 'Prospects', 'url' => '?page=leads', 'icon' => 'fa-user-tie'],
        ],
    ],
    [
        'id'          => 'calendar',
        'label'       => 'Agenda',
        'icon'        => 'fa-calendar-alt',
        'url'         => '?page=calendar',
        'description' => 'Rendez-vous et tâches',
        'badge'       => null,
    ],
    [
        'id'          => 'reports',
        'label'       => 'Rapports',
        'icon'        => 'fa-chart-line',
        'url'         => '?page=reports',
        'description' => 'Statistiques et exports',
        'badge'       => null,
    ],
    [
        'id'          => 'settings',
        'label'       => 'Paramètres',
        'icon'        => 'fa-cog',
        'url'         => '?page=settings',
        'description' => 'Configuration du compte',
        'badge'       => null,
    ],
];

// Infos utilisateur (dynamiques)
$advisorName = 'Mon espace';
$advisorCity = '';
try {
    if (!empty($pdo)) {
        $stmt = $pdo->query("SELECT field_key, field_value FROM advisor_context
                             WHERE field_key IN ('advisor_name', 'advisor_city')");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['field_key'] === 'advisor_name') {
                $advisorName = htmlspecialchars($row['field_value']);
            }
            if ($row['field_key'] === 'advisor_city') {
                $advisorCity = htmlspecialchars($row['field_value']);
            }
        }
    }
} catch (Exception $e) {
    error_log('Sidebar: ' . $e->getMessage());
}
?>

<style>
/* ============================================
   IMMO LOCAL+ SIDEBAR — Version Prestige
   ============================================ */

:root {
    --sidebar-bg: #ffffff;
    --sidebar-border: #e5e7eb;
    --sidebar-text: #1f2937;
    --sidebar-text-secondary: #6b7280;
    --sidebar-hover-bg: #f3f4f6;
    --sidebar-active-bg: #eef2ff;
    --sidebar-active-border: #4f7df3;
    --sidebar-active-text: #4f7df3;
    --sidebar-width: 280px;
    --sidebar-width-mobile: 72px;
}

/* Sidebar Container */
.sb-prestige {
    display: flex;
    flex-direction: column;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border);
    padding: 1rem 0;
    overflow-y: auto;
    overflow-x: hidden;
    transition: width 0.3s ease;
}

@media (max-width: 768px) {
    .sb-prestige {
        width: var(--sidebar-width-mobile);
        height: auto;
        flex-direction: row;
        border-right: none;
        border-bottom: 1px solid var(--sidebar-border);
        padding: 0.5rem;
        overflow-x: auto;
        overflow-y: hidden;
    }
}

/* Header */
.sb-header {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--sidebar-border);
}

.sb-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--sidebar-active-text);
    font-weight: 600;
    font-size: 14px;
    transition: opacity 0.2s ease;
}

.sb-logo:hover {
    opacity: 0.8;
}

.sb-logo i {
    font-size: 1.5rem;
}

@media (max-width: 768px) {
    .sb-header {
        order: -1;
        flex-shrink: 0;
        padding: 0.5rem;
        margin-bottom: 0;
        border-bottom: none;
    }

    .sb-logo {
        font-size: 0;
    }

    .sb-logo i {
        font-size: 1.25rem;
    }
}

/* Navigation */
.sb-menu {
    flex: 1;
    padding: 0 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .sb-menu {
        flex-direction: row;
        gap: 0.25rem;
    }
}

.sb-menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .sb-menu ul {
        flex-direction: row;
    }
}

/* Menu Item */
.sb-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid transparent;
    text-decoration: none;
    color: var(--sidebar-text);
    font-size: 14px;
    font-weight: 500;
    background: var(--sidebar-bg);
    transition: all 0.2s ease;
    position: relative;
    cursor: pointer;
    margin: 0.75rem 0.5rem;
}

.sb-item:hover {
    background: var(--sidebar-hover-bg);
    border-color: var(--sidebar-border);
    color: var(--sidebar-active-text);
}

.sb-item.active {
    background: var(--sidebar-active-bg);
    border-left: 4px solid var(--sidebar-active-border);
    color: var(--sidebar-active-text);
    font-weight: 600;
    padding-left: calc(1rem - 4px);
}

/* Item Icon */
.sb-item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    font-size: 1rem;
}

/* Item Label */
.sb-item-label {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.sb-item-label-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.sb-item-name {
    font-weight: 500;
}

.sb-item-description {
    font-size: 12px;
    color: var(--sidebar-text-secondary);
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    transition: opacity 0.2s ease, max-height 0.2s ease;
}

.sb-item:hover .sb-item-description {
    opacity: 1;
    max-height: 60px;
}

/* Badge */
.sb-badge {
    display: inline-block;
    background: #ef4444;
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

/* Submenu Toggle */
.sb-submenu-toggle {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 12px;
    padding: 0;
    display: flex;
    align-items: center;
    transition: transform 0.2s ease;
}

.sb-submenu-toggle.open {
    transform: rotate(90deg);
}

/* Submenu */
.sb-submenu {
    display: none;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0.5rem 0 0 0;
    padding-left: 2.5rem;
    list-style: none;
}

.sb-submenu.open {
    display: flex;
}

.sb-submenu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    color: var(--sidebar-text-secondary);
    font-size: 13px;
    font-weight: 400;
    background: transparent;
    border: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.sb-submenu-item:hover {
    background: var(--sidebar-hover-bg);
    color: var(--sidebar-active-text);
}

.sb-submenu-item.active {
    background: var(--sidebar-active-bg);
    color: var(--sidebar-active-text);
    font-weight: 500;
}

.sb-submenu-item i {
    width: 16px;
    text-align: center;
    font-size: 0.875rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sb-item {
        width: 50px;
        height: 50px;
        padding: 0.5rem;
        justify-content: center;
        gap: 0;
        border-radius: 0.375rem;
    }

    .sb-item.active {
        padding: 0.5rem;
        border-left: none;
        border-bottom: 3px solid var(--sidebar-active-border);
    }

    .sb-item-label {
        display: none;
    }

    .sb-submenu-toggle {
        display: none;
    }

    .sb-submenu {
        display: none !important;
    }
}

/* Footer */
.sb-footer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.5rem 0.5rem;
    margin-top: auto;
    border-top: 1px solid var(--sidebar-border);
}

@media (max-width: 768px) {
    .sb-footer {
        display: none;
    }
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    font-size: 12px;
    color: var(--sidebar-text-secondary);
}

.user-info i {
    font-size: 1.5rem;
    color: var(--sidebar-active-text);
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--sidebar-border);
    background: var(--sidebar-hover-bg);
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}
</style>

<aside class="sb-prestige" role="navigation" aria-label="Navigation principale">

    <!-- Logo -->
    <div class="sb-header">
        <a href="?page=dashboard" class="sb-logo" title="IMMO LOCAL+ Dashboard">
            <i class="fas fa-home"></i>
            <span>IMMO LOCAL+</span>
        </a>
    </div>

    <!-- Navigation principale -->
    <nav class="sb-menu">
        <ul>
            <?php foreach ($sidebarMenu as $item):
                $isActive = ($currentModule === $item['id'] ||
                           (isset($item['submenu']) && in_array($currentModule, array_column($item['submenu'], 'url'))) ||
                           strpos($_GET['page'] ?? '', $item['id']) === 0);
                $hasSubmenu = isset($item['submenu']);
            ?>
                <li>
                    <?php if ($hasSubmenu): ?>
                        <button class="sb-item <?= $isActive ? 'active' : '' ?>"
                                data-toggle="submenu-<?= $item['id'] ?>"
                                title="<?= htmlspecialchars($item['description'] ?? '') ?>">
                            <i class="fas <?= $item['icon'] ?> sb-item-icon"></i>
                            <div class="sb-item-label">
                                <div class="sb-item-label-text">
                                    <span class="sb-item-name"><?= htmlspecialchars($item['label']) ?></span>
                                    <?php if (!empty($item['description'])): ?>
                                        <span class="sb-item-description"><?= htmlspecialchars($item['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['badge']): ?>
                                    <span class="sb-badge"><?= htmlspecialchars($item['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                            <i class="fas fa-chevron-right sb-submenu-toggle <?= $isActive ? 'open' : '' ?>"></i>
                        </button>
                        <ul class="sb-submenu <?= $isActive ? 'open' : '' ?>" id="submenu-<?= $item['id'] ?>">
                            <?php foreach ($item['submenu'] as $subitem):
                                $subActive = (isset($_GET['page']) && $_GET['page'] === ltrim($subitem['url'], '?page='));
                            ?>
                                <li>
                                    <a href="<?= htmlspecialchars($subitem['url']) ?>"
                                       class="sb-submenu-item <?= $subActive ? 'active' : '' ?>">
                                        <i class="fas <?= $subitem['icon'] ?>"></i>
                                        <span><?= htmlspecialchars($subitem['label']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($item['url']) ?>"
                           class="sb-item <?= $isActive ? 'active' : '' ?>"
                           title="<?= htmlspecialchars($item['description'] ?? $item['label']) ?>">
                            <i class="fas <?= $item['icon'] ?> sb-item-icon"></i>
                            <div class="sb-item-label">
                                <div class="sb-item-label-text">
                                    <span class="sb-item-name"><?= htmlspecialchars($item['label']) ?></span>
                                    <?php if (!empty($item['description'])): ?>
                                        <span class="sb-item-description"><?= htmlspecialchars($item['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['badge']): ?>
                                    <span class="sb-badge"><?= htmlspecialchars($item['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Footer: Utilisateur + Déconnexion -->
    <div class="sb-footer">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <div><?= htmlspecialchars($advisorName) ?></div>
                <?php if ($advisorCity): ?>
                    <div style="font-size: 11px; color: var(--sidebar-text-secondary);"><?= htmlspecialchars($advisorCity) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <a href="?logout=1" class="logout-btn" title="Déconnexion">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </div>

</aside>

<script>
// Gestion des sous-menus
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('[data-toggle]');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const targetId = this.getAttribute('data-toggle');
            const submenu = document.getElementById(targetId);
            if (submenu) {
                submenu.classList.toggle('open');
                this.querySelector('.sb-submenu-toggle').classList.toggle('open');
            }
        });
    });
});
</script>
