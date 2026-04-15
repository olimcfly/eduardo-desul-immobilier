<?php
$label = (string) ($sectionData['label'] ?? '');
$title = (string) ($sectionData['title'] ?? '');
$subtitle = (string) ($sectionData['subtitle'] ?? '');
$sectionId = (string) ($sectionData['section_id'] ?? 'distinction');

$leftTag = (string) ($sectionData['left_card_tag'] ?? '');
$leftTitle = (string) ($sectionData['left_card_title'] ?? '');
$leftItems = is_array($sectionData['left_card_items'] ?? null) ? $sectionData['left_card_items'] : [];

$rightTag = (string) ($sectionData['right_card_tag'] ?? '');
$rightTitle = (string) ($sectionData['right_card_title'] ?? '');
$rightItems = is_array($sectionData['right_card_items'] ?? null) ? $sectionData['right_card_items'] : [];
?>

<section class="section" id="<?= htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8') ?>">
    <div class="container">
        <div class="section__header text-center">
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

        <div class="grid-2 insight-grid">
            <article class="insight-card insight-card--gain" data-animate>
                <?php if ($leftTag !== ''): ?>
                    <span class="insight-card__tag"><?= htmlspecialchars($leftTag, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <?php if ($leftTitle !== ''): ?>
                    <h3><?= htmlspecialchars($leftTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php endif; ?>

                <?php if ($leftItems !== []): ?>
                    <ul>
                        <?php foreach ($leftItems as $item): ?>
                            <li><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="insight-card insight-card--risk" data-animate>
                <?php if ($rightTag !== ''): ?>
                    <span class="insight-card__tag"><?= htmlspecialchars($rightTag, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <?php if ($rightTitle !== ''): ?>
                    <h3><?= htmlspecialchars($rightTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php endif; ?>

                <?php if ($rightItems !== []): ?>
                    <ul>
                        <?php foreach ($rightItems as $item): ?>
                            <li><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>