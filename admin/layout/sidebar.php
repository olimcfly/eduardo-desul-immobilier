<?php
/**
 * SIDEBAR — IMMO LOCAL+ (Mode Strict - 7 entrées)
 * /admin/layout/sidebar.php
 *
 * Spécifications:
 * - 7 entrées MAXIMUM (+ 1 Paramètres en bas)
 * - Icônes emoji + FontAwesome
 * - Responsive: 72px sur <1024px (icônes uniquement + tooltips CSS)
 * - Tooltips CSS pur
 * - État "active" : fond #EEF2FF, texte #6366F1
 */

$currentSection = $_GET['section'] ?? 'dashboard';

// Sidebar items: 7 sections principales
$sidebarSections = [
    [
        'emoji' => '🧱',
        'label' => 'Construire',
        'description' => 'Configurer votre activité',
        'url' => '?section=construire',
    ],
    [
        'emoji' => '🧲',
        'label' => 'Attirer',
        'description' => 'Générer des leads vendeurs',
        'url' => '?section=attirer',
    ],
    [
        'emoji' => '🔄',
        'label' => 'Convertir',
        'description' => 'Transformer en mandats',
        'url' => '?section=convertir',
    ],
    [
        'emoji' => '🏠',
        'label' => 'Vendre',
        'description' => 'Finaliser les transactions',
        'url' => '?section=vendre',
    ],
    [
        'emoji' => '⚡',
        'label' => 'Automatiser',
        'description' => 'Automatiser les tâches répétitives',
        'url' => '?section=automatiser',
    ],
    [
        'emoji' => '📊',
        'label' => 'Analyser',
        'description' => 'Piloter vos performances',
        'url' => '?section=analyser',
    ],
    [
        'emoji' => '🎯',
        'label' => 'Optimiser',
        'description' => 'Améliorer en continu',
        'url' => '?section=optimiser',
    ],
];

// Infos utilisateur (dynamiques)
$advisorName = 'Mon espace';
$advisorCity = '';
try {
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
} catch (Exception $e) {
    error_log('Sidebar: ' . $e->getMessage());
}
?>

<style>
/* ============================================
   IMMO LOCAL+ SIDEBAR — Mode Strict Minimaliste
   ============================================ */

:root {
    --color-primary: #6366F1;
    --color-primary-light: #EEF2FF;
    --color-white: #FFFFFF;
    --color-gray-50: #F9FAFB;
    --color-gray-100: #F3F4F6;
    --color-gray-200: #E5E7EB;
    --color-gray-600: #4B5563;
    --color-text-primary: #1F2937;
    --color-text-secondary: #6B7280;
    --color-shadow: rgba(0, 0, 0, 0.1);

    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;

    --radius: 0.5rem;
    --border: 1px solid var(--color-gray-200);
    --shadow: 0 1px 3px var(--color-shadow);
}

/* Sidebar Container */
.sidebar {
    display: flex;
    flex-direction: column;
    width: 280px;
    height: 100vh;
    background: var(--color-white);
    border-right: var(--border);
    padding: var(--spacing-md) 0;
    box-shadow: var(--shadow);
    overflow-y: auto;
    transition: width 0.3s ease;
}

@media (max-width: 1024px) {
    .sidebar {
        width: 72px;
    }
}

/* Logo / Branding */
.sidebar-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    text-decoration: none;
    color: var(--color-primary);
    font-weight: 600;
    font-size: 14px;
    font-family: Inter, system-ui, -apple-system, sans-serif;
}

@media (max-width: 1024px) {
    .sidebar-logo {
        font-size: 0;
    }

    .sidebar-logo::before {
        content: '🏠';
        font-size: 1.5rem;
    }
}

/* Navigation Container */
.sidebar-nav {
    flex: 1;
    padding: 0 var(--spacing-sm);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

/* Navigation Items */
.sidebar-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--color-text-primary);
    font-size: 14px;
    font-weight: 500;
    font-family: Inter, system-ui, -apple-system, sans-serif;
    border: var(--border);
    background: var(--color-white);
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    white-space: nowrap;
}

.sidebar-item:hover {
    background: var(--color-gray-50);
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.sidebar-item.active {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
    font-weight: 600;
}

/* Item Icon (Emoji) */
.sidebar-item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    width: 2rem;
    height: 2rem;
    flex-shrink: 0;
}

/* Item Label */
.sidebar-item-label {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    min-width: 0;
}

.sidebar-item-name {
    font-size: 14px;
    font-weight: 500;
}

.sidebar-item-desc {
    font-size: 12px;
    color: var(--color-text-secondary);
    display: none;
}

@media (min-width: 1024px) {
    .sidebar-item-desc {
        display: block;
    }
}

/* Tooltip for mobile (CSS-only) */
@media (max-width: 1024px) {
    .sidebar-item {
        width: 40px;
        height: 40px;
        padding: var(--spacing-sm);
        justify-content: center;
        gap: 0;
        border: none;
        background: transparent;
    }

    .sidebar-item-label {
        display: none;
    }

    .sidebar-item::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: var(--spacing-md);
        white-space: nowrap;
        background: var(--color-gray-600);
        color: var(--color-white);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius);
        font-size: 12px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        z-index: 1000;
    }

    .sidebar-item:hover::after {
        opacity: 1;
    }
}

/* Settings Item (bottom) */
.sidebar-footer {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg) var(--spacing-sm);
    margin-top: auto;
    border-top: var(--border);
    padding-top: var(--spacing-lg);
}

.sidebar-settings {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--color-text-secondary);
    font-size: 14px;
    font-weight: 500;
    border: var(--border);
    background: var(--color-gray-50);
    transition: all 0.2s ease;
}

.sidebar-settings:hover {
    background: var(--color-gray-100);
    color: var(--color-primary);
    border-color: var(--color-primary);
}

.sidebar-settings-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    width: 2rem;
    height: 2rem;
}

@media (max-width: 1024px) {
    .sidebar-settings {
        width: 40px;
        height: 40px;
        padding: var(--spacing-sm);
        border: none;
        background: transparent;
    }

    .sidebar-settings span {
        display: none;
    }

    .sidebar-settings::after {
        content: 'Paramètres';
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: var(--spacing-md);
        white-space: nowrap;
        background: var(--color-gray-600);
        color: var(--color-white);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius);
        font-size: 12px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .sidebar-settings:hover::after {
        opacity: 1;
    }
}
</style>

<aside class="sidebar" role="navigation" aria-label="Navigation principale">

    <!-- Logo -->
    <a href="?section=dashboard" class="sidebar-logo" title="IMMO LOCAL+ Dashboard">
        <span>🏠 IMMO LOCAL+</span>
    </a>

    <!-- Navigation principale (7 sections) -->
    <nav class="sidebar-nav">
        <?php foreach ($sidebarSections as $section):
            $isActive = ($currentSection === str_replace('?section=', '', $section['url']));
            $activeClass = $isActive ? ' active' : '';
            $sectionName = str_replace('?section=', '', $section['url']);
        ?>
            <a href="<?= htmlspecialchars($section['url']) ?>"
               class="sidebar-item<?= $activeClass ?>"
               data-tooltip="<?= htmlspecialchars($section['description']) ?>"
               title="<?= htmlspecialchars($section['label']) ?> — <?= htmlspecialchars($section['description']) ?>">
                <span class="sidebar-item-icon"><?= $section['emoji'] ?></span>
                <div class="sidebar-item-label">
                    <span class="sidebar-item-name"><?= htmlspecialchars($section['label']) ?></span>
                    <span class="sidebar-item-desc"><?= htmlspecialchars($section['description']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer: Utilisateur + Paramètres -->
    <div class="sidebar-footer">
        <div style="padding: 0 var(--spacing-md); font-size: 12px; color: var(--color-text-secondary);">
            <?php if ($advisorCity): ?>
                <strong><?= $advisorCity ?></strong>
            <?php endif; ?>
        </div>

        <a href="?section=parametres" class="sidebar-settings" title="Paramètres">
            <span class="sidebar-settings-icon">⚙️</span>
            <span>Paramètres</span>
        </a>
    </div>

</aside>
