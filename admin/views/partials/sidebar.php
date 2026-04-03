<?php
$currentModule = $module ?? 'construire';
$menuGroups = [
    'Pilotage' => [
        ['module' => 'construire', 'label' => 'Construire', 'hint' => 'Poser les bases', 'icon' => 'fas fa-layer-group'],
        ['module' => 'attirer', 'label' => 'Attirer', 'hint' => 'Générer des vendeurs', 'icon' => 'fas fa-bullseye'],
        ['module' => 'capturer', 'label' => 'Capturer', 'hint' => 'Transformer en contacts', 'icon' => 'fas fa-inbox'],
        ['module' => 'convertir', 'label' => 'Convertir', 'hint' => 'Transformer en clients', 'icon' => 'fas fa-arrow-trend-up'],
        ['module' => 'optimiser', 'label' => 'Optimiser', 'hint' => 'Améliorer les résultats', 'icon' => 'fas fa-chart-line'],
    ],
    'Outils' => [
        ['module' => 'assistant', 'label' => 'Assistant IA', 'hint' => 'IA à votre service', 'icon' => 'fas fa-robot'],
        ['module' => 'biens', 'label' => 'Biens', 'hint' => 'Gestion du portefeuille', 'icon' => 'fas fa-house'],
        ['module' => 'gmb', 'label' => 'Google My Business', 'hint' => 'Avis et visibilité', 'icon' => 'fab fa-google'],
        ['module' => 'seo', 'label' => 'SEO', 'hint' => 'Positionnement Google', 'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'social', 'label' => 'Social', 'hint' => 'Publications & réseaux', 'icon' => 'fas fa-share-nodes'],
    ],
    'Compte' => [
        ['module' => 'parametres', 'label' => 'Paramètres', 'hint' => 'Compte et préférences', 'icon' => 'fas fa-gear'],
    ],
];
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <?php foreach ($menuGroups as $section => $items): ?>
            <li class="nav-section-label"><?= htmlspecialchars($section) ?></li>

            <?php foreach ($items as $item): ?>
                <?php $isActive = $currentModule === $item['module']; ?>
                <li>
                    <a href="/admin/?module=<?= urlencode($item['module']) ?>"
                       class="menu-item<?= $isActive ? ' active' : '' ?>"
                       data-module="<?= htmlspecialchars($item['module']) ?>"
                       data-tooltip="<?= htmlspecialchars($item['label']) ?>"
                       <?= $isActive ? 'aria-current="page"' : '' ?>>
                        <span class="menu-icon"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                        <span class="menu-label"><?= htmlspecialchars($item['label']) ?>
                            <small class="menu-hint"><?= htmlspecialchars($item['hint']) ?></small>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</nav>
