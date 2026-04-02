<?php
$current = $_SERVER['REQUEST_URI'];

$menu = [
    ['icon' => '📊', 'label' => 'Dashboard',   'href' => '/admin/',                'match' => '/admin/$'],
    ['icon' => '🏠', 'label' => 'Biens',        'href' => '/admin/biens/',          'match' => '/biens/'],
    ['icon' => '⭐', 'label' => 'Google My B.', 'href' => '/admin/gmb/',            'match' => '/gmb/'],
    ['icon' => '🔍', 'label' => 'SEO',          'href' => '/admin/seo/',            'match' => '/seo/'],
    ['icon' => '📱', 'label' => 'Social',       'href' => '/admin/social/',         'match' => '/social/'],
    ['icon' => '⚙️',  'label' => 'Paramètres',  'href' => '/admin/settings/',       'match' => '/settings/'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-icon">🏡</span>
        <span class="logo-text">ImmoAdmin</span>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $item): ?>
            <?php $active = str_contains($current, $item['match']) ? 'active' : ''; ?>
            <a href="<?= $item['href'] ?>" class="nav-item <?= $active ?>">
                <span class="nav-icon"><?= $item['icon'] ?></span>
                <span class="nav-label"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-meta">
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></div>
                <div class="user-role">Administrateur</div>
            </div>
        </div>
        <a href="/admin/logout" class="logout-btn" title="Déconnexion">&#x23FB;</a>
    </div>
</aside>
