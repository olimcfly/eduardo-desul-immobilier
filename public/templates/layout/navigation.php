<?php
declare(strict_types=1);

$navigationItems = $navigationItems ?? [];

if (!is_array($navigationItems)) {
    $navigationItems = [];
}
?>

<nav class="site-nav" aria-label="Navigation principale">
    <?php if ($navigationItems !== []): ?>
        <ul class="site-nav__list">
            <?php foreach ($navigationItems as $item): ?>
                <?php
                $label = trim((string) ($item['label'] ?? ''));
                $url = trim((string) ($item['url'] ?? '#'));
                $isActive = (bool) ($item['active'] ?? false);
                ?>
                <li class="site-nav__item">
                    <a class="site-nav__link<?= $isActive ? ' is-active' : '' ?>" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</nav>