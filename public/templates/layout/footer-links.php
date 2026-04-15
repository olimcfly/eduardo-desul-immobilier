<?php
declare(strict_types=1);

$footerLinks = $footerLinks ?? [];

if (!is_array($footerLinks)) {
    $footerLinks = [];
}
?>

<div class="footer-links">
    <?php if ($footerLinks !== []): ?>
        <ul class="footer-links__list">
            <?php foreach ($footerLinks as $item): ?>
                <?php
                $label = trim((string) ($item['label'] ?? ''));
                $url = trim((string) ($item['url'] ?? '#'));
                ?>
                <li>
                    <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>