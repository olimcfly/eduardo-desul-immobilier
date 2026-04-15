<?php
declare(strict_types=1);

$siteName = trim((string) ($siteSettings['site_name'] ?? ($siteSettings['advisor_name'] ?? 'Mon site immobilier')));
$logo = trim((string) ($siteSettings['logo_url'] ?? ''));
$homeUrl = trim((string) ($siteSettings['home_url'] ?? '/'));
?>

<div class="site-brand">
    <a class="site-brand__link" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($logo !== ''): ?>
            <img class="site-brand__logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <span class="site-brand__name"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
    </a>
</div>