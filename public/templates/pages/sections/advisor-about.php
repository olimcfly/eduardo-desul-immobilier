<?php
$label = (string) ($sectionData['label'] ?? '');
$title = (string) ($sectionData['title'] ?? '');
$content = (string) ($sectionData['content'] ?? '');
$benefits = is_array($sectionData['benefits'] ?? null) ? $sectionData['benefits'] : [];
$imageUrl = (string) ($sectionData['image_url'] ?? '');
$imageAlt = (string) ($sectionData['image_alt'] ?? '');
$buttonLabel = (string) ($sectionData['button_label'] ?? '');
$buttonUrl = (string) ($sectionData['button_url'] ?? '#');
?>

<section class="section section--alt">
    <div class="container grid-2 about-split">
        <div data-animate>
            <?php if ($label !== ''): ?>
                <span class="section-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>

            <?php if ($title !== ''): ?>
                <h2 class="section-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php endif; ?>

            <?php if ($content !== ''): ?>
                <p><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($benefits !== []): ?>
                <ul class="benefits-list">
                    <?php foreach ($benefits as $benefit): ?>
                        <li><?= htmlspecialchars((string) $benefit, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($buttonLabel !== ''): ?>
                <a href="<?= htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--outline">
                    <?= htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($imageUrl !== ''): ?>
            <figure class="about-photo">
                <img
                    src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($imageAlt, ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy">
            </figure>
        <?php endif; ?>
    </div>
</section>