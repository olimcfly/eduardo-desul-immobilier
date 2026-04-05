<?php
$currentModule = $module ?? 'construire';

$menuGroups = [
    'Pilotage' => [
        ['module' => 'construire',  'label' => 'Construire',        'hint' => 'Poser les bases',           'icon' => 'fas fa-layer-group'],
        ['module' => 'attirer',     'label' => 'Attirer',           'hint' => 'Générer des vendeurs',      'icon' => 'fas fa-bullseye'],
        ['module' => 'capturer',    'label' => 'Capturer',          'hint' => 'Transformer en contacts',   'icon' => 'fas fa-inbox'],
        ['module' => 'convertir',   'label' => 'Convertir',         'hint' => 'Transformer en clients',    'icon' => 'fas fa-arrow-trend-up'],
        ['module' => 'optimiser',   'label' => 'Optimiser',         'hint' => 'Améliorer les résultats',   'icon' => 'fas fa-chart-line'],
    ],
    'Outils' => [
        ['module' => 'assistant',   'label' => 'Assistant IA',      'hint' => 'IA à votre service',        'icon' => 'fas fa-robot'],
        ['module' => 'biens',       'label' => 'Biens',             'hint' => 'Gestion du portefeuille',   'icon' => 'fas fa-house'],
        ['module' => 'gmb',         'label' => 'Google My Business','hint' => 'Avis et visibilité',        'icon' => 'fab fa-google'],
        ['module' => 'seo',         'label' => 'SEO',               'hint' => 'Positionnement Google',     'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'social',      'label' => 'Social',            'hint' => 'Publications & réseaux',    'icon' => 'fas fa-share-nodes'],
    ],
    'Contenu' => [
        ['module' => 'cms_blog',    'label' => 'Modifier le Blog',  'hint' => 'Sections CMS /blog',        'icon' => 'fas fa-blog', 'url' => '/admin/cms/edit/blog'],
    ],
    'Compte' => [
        ['module' => 'parametres',  'label' => 'Paramètres',        'hint' => 'Compte et préférences',     'icon' => 'fas fa-gear'],
    ],
];

$authUser = Auth::user();
if (($authUser['role'] ?? '') === 'superadmin') {
    $menuGroups['Compte'][] = ['module' => 'superadmin', 'label' => 'Superadmin', 'hint' => 'Modules & accès live', 'icon' => 'fas fa-crown'];
}
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <?php foreach ($menuGroups as $sectionLabel => $items): ?>
        <li class="nav-section-label"><?= htmlspecialchars($sectionLabel) ?></li>
        <?php foreach ($items as $item):
            $targetUrl = $item['url'] ?? ('/admin?module=' . rawurlencode($item['module']));
            $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
            $isActive = isset($item['url'])
                ? (rtrim($currentPath, '/') === rtrim($item['url'], '/'))
                : ($currentModule === $item['module']);
        ?>
        <li>
            <a href="<?= htmlspecialchars($targetUrl) ?>"
               class="menu-item<?= $isActive ? ' active' : '' ?>"
               data-module="<?= htmlspecialchars($item['module']) ?>"
               data-tooltip="<?= htmlspecialchars($item['label']) ?>">
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
