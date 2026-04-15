<?php
declare(strict_types=1);

$phone = trim((string) ($siteSettings['phone'] ?? ''));
$primaryLabel = trim((string) ($siteSettings['header_cta_label'] ?? 'Estimation gratuite'));
$primaryUrl = trim((string) ($siteSettings['header_cta_url'] ?? '/estimation'));
?>

<div class="header-ctas">
    <?php if ($phone !== ''): ?>
        <a class="header-ctas__phone" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $phone), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endif; ?>

    <?php if ($primaryLabel !== '' && $primaryUrl !== ''): ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars($primaryUrl, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($primaryLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endif; ?>
</div>