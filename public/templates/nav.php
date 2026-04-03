<?php
$currentUri = $currentUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [
    ['path' => '/',         'href' => url('/'),         'label' => 'Accueil'],
    ['path' => '/biens',    'href' => url('/biens'),    'label' => 'Biens'],
    ['path' => '/services', 'href' => url('/services'), 'label' => 'Services'],
    ['path' => '/blog',     'href' => url('/blog'),     'label' => 'Contenu',
     'sub' => [
        ['path' => '/blog',        'href' => url('/blog'),        'label' => 'Blog'],
        ['path' => '/actualites',  'href' => url('/actualites'),  'label' => 'Actualités'],
        ['path' => '/guide-local', 'href' => url('/guide-local'), 'label' => 'Guide local'],
        ['path' => '/ressources',  'href' => url('/ressources'),  'label' => 'Ressources'],
     ]
    ],
    ['path' => '/a-propos', 'href' => url('/a-propos'), 'label' => 'À propos'],
    ['path' => '/contact',  'href' => url('/contact'),  'label' => 'Contact'],
];
?>
<nav class="site-nav" id="site-nav" role="navigation" aria-label="Navigation principale">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <?php $active = isActive($item['path'], $currentUri); ?>
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
            <a href="<?= e($item['href']) ?>" class="<?= isActive($item['path'], $currentUri) ?>">
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
        <li><a href="<?= e(url('/estimation-gratuite')) ?>" class="btn btn--primary btn--full">Estimation gratuite</a></li>
    </ul>
</div>
<div class="nav-overlay" id="nav-overlay"></div>
