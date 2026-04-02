<?php
$currentUri = $currentUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [
    ['href' => '/',         'label' => 'Accueil'],
    ['href' => '/biens',    'label' => 'Biens'],
    ['href' => '/services', 'label' => 'Services'],
    ['href' => '/blog',     'label' => 'Contenu',
     'sub' => [
        ['href' => '/blog',         'label' => 'Blog'],
        ['href' => '/actualites',   'label' => 'Actualités'],
        ['href' => '/guide-local',  'label' => 'Guide local'],
        ['href' => '/ressources',   'label' => 'Ressources'],
     ]
    ],
    ['href' => '/a-propos', 'label' => 'À propos'],
    ['href' => '/contact',  'label' => 'Contact'],
];
?>
<nav class="site-nav" id="site-nav" role="navigation" aria-label="Navigation principale">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <?php $active = isActive($item['href'], $currentUri); ?>
        <li class="nav__item <?= !empty($item['sub']) ? 'has-dropdown' : '' ?> <?= $active ?>">
            <a href="<?= e($item['href']) ?>" class="nav__link <?= $active ?>"
               <?= !empty($item['sub']) ? 'aria-haspopup="true" aria-expanded="false"' : '' ?>>
                <?= e($item['label']) ?>
                <?php if (!empty($item['sub'])): ?><span class="nav__arrow" aria-hidden="true">▾</span><?php endif; ?>
            </a>
            <?php if (!empty($item['sub'])): ?>
            <ul class="nav__dropdown" role="menu">
                <?php foreach ($item['sub'] as $sub): ?>
                <li role="none">
                    <a href="<?= e($sub['href']) ?>" class="dropdown__link" role="menuitem">
                        <?= e($sub['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<!-- Menu mobile -->
<div class="nav-mobile" id="nav-mobile" aria-hidden="true">
    <button class="nav-mobile__close" id="nav-close" aria-label="Fermer le menu">×</button>
    <ul>
        <?php foreach ($navItems as $item): ?>
        <li>
            <a href="<?= e($item['href']) ?>" class="<?= isActive($item['href'], $currentUri) ?>">
                <?= e($item['label']) ?>
            </a>
            <?php if (!empty($item['sub'])): ?>
            <ul class="mobile-sub">
                <?php foreach ($item['sub'] as $sub): ?>
                <li><a href="<?= e($sub['href']) ?>"><?= e($sub['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li><a href="/estimation-gratuite" class="btn btn--primary btn--full">Estimation gratuite</a></li>
    </ul>
</div>
<div class="nav-overlay" id="nav-overlay"></div>
