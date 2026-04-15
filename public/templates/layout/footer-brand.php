<?php
declare(strict_types=1);

$advisorName = trim((string) ($siteSettings['advisor_name'] ?? 'Votre conseiller'));
$city = trim((string) ($siteSettings['city'] ?? ''));
$description = trim((string) ($siteSettings['footer_description'] ?? 'Accompagnement immobilier local sur mesure.'));
?>

<div class="footer-brand">
    <strong><?= htmlspecialchars($advisorName, ENT_QUOTES, 'UTF-8') ?></strong>

    <?php if ($city !== ''): ?>
        <span><?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>

    <?php if ($description !== ''): ?>
        <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</div>