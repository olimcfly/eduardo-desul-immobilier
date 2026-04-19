<?php
if (!function_exists('isActive')) {
    function isActive($path, $currentUri = null) {
        if ($currentUri === null) {
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }
        return $currentUri === $path || strpos($currentUri, $path) === 0;
    }
}

if (!function_exists('url')) {
    function url($path) {
        return $path;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [

    // ── Accueil ──────────────────────────────────────────────────
    [
        'path'  => '/',
        'href'  => url('/'),
        'label' => 'Accueil',
    ],

    // ── Nos biens ────────────────────────────────────────────────
    [
        'path'  => '/biens',
        'href'  => url('/biens'),
        'label' => 'Nos biens',
        'sub'   => [
            ['path' => '/biens',              'href' => url('/biens'),              'label' => 'Tous les biens'],
            ['path' => '/biens/maisons',      'href' => url('/biens/maisons'),      'label' => 'Maisons'],
            ['path' => '/biens/appartements', 'href' => url('/biens/appartements'), 'label' => 'Appartements'],
            ['path' => '/biens/prestige',     'href' => url('/biens/prestige'),     'label' => 'Prestige'],
            ['path' => '/biens/vendus',       'href' => url('/biens/vendus'),       'label' => 'Biens vendus'],
        ],
    ],

    // ── Secteurs ─────────────────────────────────────────────────
    [
        'path'  => '/secteurs',
        'href'  => url('/secteurs'),
        'label' => 'Secteurs',
        'wide'  => true,
        'sub'   => [
            // ── Section Villes ────────────────────────────────────
            ['type' => 'label', 'label' => 'Villes'],
            ['path' => '/secteurs/villes/bordeaux',  'href' => url('/secteurs/villes/bordeaux'),  'label' => 'Bordeaux'],
            ['path' => '/secteurs/villes/merignac',  'href' => url('/secteurs/villes/merignac'),  'label' => 'Mérignac'],
            ['path' => '/secteurs/villes/talence',   'href' => url('/secteurs/villes/talence'),   'label' => 'Talence'],
            ['path' => '/secteurs/villes/pessac',    'href' => url('/secteurs/villes/pessac'),    'label' => 'Pessac'],
            ['path' => '/secteurs/villes/begles',    'href' => url('/secteurs/villes/begles'),    'label' => 'Bègles'],
            ['path' => '/secteurs/villes/le-bouscat','href' => url('/secteurs/villes/le-bouscat'),'label' => 'Le Bouscat'],
            ['path' => '/secteurs/villes/cauderan',  'href' => url('/secteurs/villes/cauderan'),  'label' => 'Caudéran'],

            // ── Section Quartiers ─────────────────────────────────
            ['type' => 'label', 'label' => 'Quartiers de Bordeaux'],
            ['path' => '/secteurs/quartiers/chartrons',     'href' => url('/secteurs/quartiers/chartrons'),     'label' => 'Chartrons'],
            ['path' => '/secteurs/quartiers/saint-michel',  'href' => url('/secteurs/quartiers/saint-michel'),  'label' => 'Saint-Michel'],
            ['path' => '/secteurs/quartiers/cauderan',      'href' => url('/secteurs/quartiers/cauderan'),      'label' => 'Caudéran'],
            ['path' => '/secteurs/quartiers/bastide',       'href' => url('/secteurs/quartiers/bastide'),       'label' => 'La Bastide'],
            ['path' => '/secteurs/quartiers/saint-seurin',  'href' => url('/secteurs/quartiers/saint-seurin'),  'label' => 'Saint-Seurin'],
            ['path' => '/secteurs/quartiers/nansouty',      'href' => url('/secteurs/quartiers/nansouty'),      'label' => 'Nansouty'],
            ['path' => '/secteurs/quartiers/bordeaux-centre','href' => url('/secteurs/quartiers/bordeaux-centre'),'label' => 'Bordeaux Centre'],

            // ── Voir tous ─────────────────────────────────────────
            ['type' => 'divider'],
            ['path' => '/secteurs', 'href' => url('/secteurs'), 'label' => '→ Voir tous les secteurs'],
        ],
    ],

    // ── Vendre ───────────────────────────────────────────────────
    [
        'path'  => '/estimation-gratuite',
        'href'  => false,
        'label' => 'Vendre',
        'sub'   => [
            ['path' => '/vendre', 'href' => url('/vendre'), 'label' => 'Vendre mon bien'],
            ['path' => '/estimation-gratuite', 'href' => url('/estimation-gratuite'), 'label' => 'Estimation gratuite'],
            ['path' => '/services', 'href' => url('/ressources/guide-vendeur'), 'label' => 'Méthode de vente'],
            ['path' => '/services', 'href' => url('/services'), 'label' => 'Mise en valeur du bien'],
            ['path' => '/services', 'href' => url('/services#faq-vendeur'), 'label' => 'FAQ vendeur'],
        ],
    ],

    // ── Acheter ──────────────────────────────────────────────────
    [
        'path'  => '/acheter',
        'href'  => url('/acheter'),
        'label' => 'Acheter',
        'sub'   => [
            ['path' => '/acheter', 'href' => url('/acheter'), 'label' => 'Acheter un bien'],
            ['path' => '/biens', 'href' => url('/biens'), 'label' => 'Nos biens disponibles'],
            ['path' => '/acheter', 'href' => url('/ressources/guide-acheteur'), 'label' => 'Accompagnement acquéreur'],
            ['path' => '/acheter', 'href' => url('/contact?sujet=Recherche+personnalisee'), 'label' => 'Recherche personnalisée'],
            ['path' => '/acheter', 'href' => url('/acheter#faq-acheteur'), 'label' => 'FAQ acheteur'],
        ],
    ],

    // ── Financement ──────────────────────────────────────────────
    [
        'path'  => '/financement',
        'href'  => url('/financement'),
        'label' => 'Financement',
        'sub'   => [
            ['path' => '/financement', 'href' => url('/financement'), 'label' => 'Financer mon projet'],
            ['path' => '/financement', 'href' => url('/financement#formulaire-financement'), 'label' => 'Déposer ma demande'],
            ['path' => '/financement', 'href' => url('/financement#etapes-financement'), 'label' => 'Comment ça fonctionne'],
            ['path' => '/financement', 'href' => url('/financement#faq-financement'), 'label' => 'FAQ financement'],
        ],
    ],

    // ── Guide local ──────────────────────────────────────────────
    [
        'path'  => '/guide-local',
        'href'  => url('/guide-local'),
        'label' => 'Guide local',
    ],

    // ── Blog ─────────────────────────────────────────────────────
    [
        'path'  => '/blog',
        'href'  => url('/blog'),
        'label' => 'Blog',
    ],

    // ── À propos ─────────────────────────────────────────────────
    [
        'path'  => '/a-propos',
        'href'  => url('/a-propos'),
        'label' => 'À propos',
    ],
];
?>

<!-- ── Navigation desktop ─────────────────────────────────────── -->
<nav class="nav" id="nav" aria-label="Menu principal">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <li class="nav__item <?= !empty($item['sub']) ? 'has-submenu' : '' ?>">

            <?php if (!empty($item['sub'])): ?>
                <?php if ($item['href'] !== false && $item['href'] !== null): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="nav__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                        <span><?= htmlspecialchars($item['label']) ?></span>
                        <span class="nav__caret" aria-hidden="true">▾</span>
                    </a>
                <?php else: ?>
                    <button class="nav__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>"
                            type="button" aria-haspopup="true" aria-expanded="false">
                        <span><?= htmlspecialchars($item['label']) ?></span>
                        <span class="nav__caret" aria-hidden="true">▾</span>
                    </button>
                <?php endif; ?>

                <ul class="submenu <?= !empty($item['wide']) ? 'submenu--wide' : '' ?>">
                    <?php foreach ($item['sub'] as $sub): ?>
                        <?php if (($sub['type'] ?? '') === 'label'): ?>
                            <li class="submenu__section-label"><?= htmlspecialchars($sub['label']) ?></li>
                        <?php elseif (($sub['type'] ?? '') === 'divider'): ?>
                            <li class="submenu__divider" role="separator"></li>
                        <?php elseif (($sub['href'] ?? null) === null): ?>
                            <li><span class="submenu__link disabled-link"><?= htmlspecialchars($sub['label']) ?></span></li>
                        <?php else: ?>
                            <li>
                                <a href="<?= htmlspecialchars($sub['href']) ?>"
                                   class="submenu__link <?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>">
                                    <?= htmlspecialchars($sub['label']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

            <?php elseif (($item['href'] ?? null) === null): ?>
                <span class="nav__link disabled-link"><?= htmlspecialchars($item['label']) ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav__link <?= ($item['path'] === '/' ? $currentUri === '/' : isActive($item['path'], $currentUri)) ? 'active' : '' ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endif; ?>

        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<!-- ── Navigation mobile ──────────────────────────────────────── -->
<div class="nav-mobile" id="nav-mobile" aria-hidden="true">
    <button class="nav-mobile__close" id="nav-close" aria-label="Fermer le menu">×</button>

    <ul class="nav-mobile__list">
        <?php foreach ($navItems as $item): ?>
        <li class="nav-mobile__item">

            <?php if (!empty($item['sub'])): ?>
                <button class="nav-mobile__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>"
                        type="button" aria-expanded="false">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <span class="nav-mobile__caret" aria-hidden="true">▾</span>
                </button>
                <ul class="mobile-sub" hidden>
                    <?php foreach ($item['sub'] as $sub): ?>
                        <?php if (($sub['type'] ?? '') === 'label'): ?>
                            <li class="mobile-sub__label"><?= htmlspecialchars($sub['label']) ?></li>
                        <?php elseif (($sub['type'] ?? '') === 'divider'): ?>
                            <li class="mobile-sub__divider"></li>
                        <?php elseif (($sub['href'] ?? null) === null): ?>
                            <li><span class="disabled-link"><?= htmlspecialchars($sub['label']) ?></span></li>
                        <?php else: ?>
                            <li>
                                <a href="<?= htmlspecialchars($sub['href']) ?>"
                                   class="<?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>">
                                    <?= htmlspecialchars($sub['label']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

            <?php elseif (($item['href'] ?? null) === null): ?>
                <span class="nav-mobile__link disabled-link"><?= htmlspecialchars($item['label']) ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav-mobile__link <?= ($item['path'] === '/' ? $currentUri === '/' : isActive($item['path'], $currentUri)) ? 'active' : '' ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endif; ?>

        </li>
        <?php endforeach; ?>

        <li class="nav-mobile__cta-wrap">
            <a href="<?= htmlspecialchars(url('/avis-de-valeur')) ?>" class="btn btn--outline btn--full" style="margin-bottom:.5rem">
                Avis de valeur
            </a>
            <a href="<?= htmlspecialchars(url('/prendre-rendez-vous')) ?>" class="btn btn--primary btn--full">
                Prendre RDV
            </a>
        </li>

        <?php if (defined('APP_PHONE') && APP_PHONE): ?>
        <li style="padding:0 1rem .5rem">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--full">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.25rem" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 14a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= htmlspecialchars(APP_PHONE) ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="nav-overlay" id="nav-overlay"></div>