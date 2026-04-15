<?php
declare(strict_types=1);

$sectionTitle = trim((string) ($section['title'] ?? 'Ils vous parlent de leur expérience'));
$sectionSubtitle = trim((string) ($section['subtitle'] ?? 'Des retours utiles pour rassurer vendeurs et acheteurs.'));
$items = $section['items'] ?? [];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="section section-testimonials">
    <div class="container">
        <div class="section-heading">
            <?php if ($sectionTitle !== ''): ?>
                <h2><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php endif; ?>

            <?php if ($sectionSubtitle !== ''): ?>
                <p><?= htmlspecialchars($sectionSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <?php if ($items !== []): ?>
            <div class="testimonials-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    $quote = trim((string) ($item['quote'] ?? ''));
                    $name = trim((string) ($item['name'] ?? ''));
                    $role = trim((string) ($item['role'] ?? ''));
                    $location = trim((string) ($item['location'] ?? ''));
                    ?>
                    <article class="testimonial-card">
                        <?php if ($quote !== ''): ?>
                            <blockquote>
                                <p>“<?= htmlspecialchars($quote, ENT_QUOTES, 'UTF-8') ?>”</p>
                            </blockquote>
                        <?php endif; ?>

                        <?php if ($name !== '' || $role !== '' || $location !== ''): ?>
                            <div class="testimonial-meta">
                                <?php if ($name !== ''): ?>
                                    <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php endif; ?>

                                <?php if ($role !== '' || $location !== ''): ?>
                                    <span>
                                        <?= htmlspecialchars(trim($role . ($role !== '' && $location !== '' ? ' · ' : '') . $location), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>