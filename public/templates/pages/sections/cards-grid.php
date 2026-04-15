<?php
$label = (string) ($sectionData['label'] ?? '');
$title = (string) ($sectionData['title'] ?? '');
$subtitle = (string) ($sectionData['subtitle'] ?? '');
$items = is_array($sectionData['items'] ?? null) ? $sectionData['items'] : [];
$sectionId = (string) ($sectionData['section_id'] ?? 'realite-prospect');
?>

<section class="section section--alt" id="<?= htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8') ?>">
    <div class="container">
        <div class="section__header">
            <?php if ($label !== ''): ?>
                <span class="section-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>

            <?php if ($title !== ''): ?>
                <h2 class="section-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php endif; ?>

            <?php if ($subtitle !== ''): ?>
                <p class="section-subtitle"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <?php if ($items !== []): ?>
            <div class="grid-3">
                <?php foreach ($items as $item): ?>
                    <?php
                    $cardTitle = (string) ($item['title'] ?? '');
                    $cardText = (string) ($item['text'] ?? '');
                    if ($cardTitle === '' && $cardText === '') {
                        continue;
                    }
                    ?>
                    <article class="card" data-animate>
                        <div class="card__body">
                            <?php if ($cardTitle !== ''): ?>
                                <h3 class="card__title"><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php endif; ?>

                            <?php if ($cardText !== ''): ?>
                                <p class="card__text"><?= htmlspecialchars($cardText, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>