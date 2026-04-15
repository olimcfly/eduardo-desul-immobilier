<?php
declare(strict_types=1);

$sectionTitle = trim((string) ($section['title'] ?? 'Biens mis en avant'));
$sectionSubtitle = trim((string) ($section['subtitle'] ?? 'Une sélection de biens pour illustrer votre positionnement local.'));
$items = $section['items'] ?? [];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="section section-featured-properties">
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
            <div class="properties-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    $title = trim((string) ($item['title'] ?? ''));
                    $description = trim((string) ($item['description'] ?? ''));
                    $price = trim((string) ($item['price'] ?? ''));
                    $location = trim((string) ($item['location'] ?? ''));
                    $image = trim((string) ($item['image'] ?? ''));
                    $link = trim((string) ($item['link'] ?? ''));
                    ?>
                    <article class="property-card">
                        <?php if ($image !== ''): ?>
                            <div class="property-card__media">
                                <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title !== '' ? $title : 'Bien immobilier', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        <?php endif; ?>

                        <div class="property-card__body">
                            <?php if ($price !== ''): ?>
                                <p class="property-card__price"><?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <?php if ($title !== ''): ?>
                                <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php endif; ?>

                            <?php if ($location !== ''): ?>
                                <p class="property-card__location"><?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <?php if ($description !== ''): ?>
                                <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <?php if ($link !== ''): ?>
                                <a class="btn btn-secondary" href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>">Voir le bien</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>