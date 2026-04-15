<?php
$label = (string) ($sectionData['label'] ?? '');
$title = (string) ($sectionData['title'] ?? '');
$subtitle = (string) ($sectionData['subtitle'] ?? '');
$steps = is_array($sectionData['steps'] ?? null) ? $sectionData['steps'] : [];
$primaryButtonLabel = (string) ($sectionData['primary_button_label'] ?? '');
$primaryButtonUrl = (string) ($sectionData['primary_button_url'] ?? '#');
$secondaryButtonLabel = (string) ($sectionData['secondary_button_label'] ?? '');
$secondaryButtonUrl = (string) ($sectionData['secondary_button_url'] ?? '#');
$sectionId = (string) ($sectionData['section_id'] ?? 'methode');
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

        <?php if ($steps !== []): ?>
            <div class="grid-5-steps">
                <?php foreach ($steps as $step): ?>
                    <?php
                    $number = (string) ($step['number'] ?? '');
                    $stepTitle = (string) ($step['title'] ?? '');
                    $stepText = (string) ($step['text'] ?? '');
                    ?>
                    <article class="step-card" data-animate>
                        <?php if ($number !== ''): ?>
                            <span class="step-card__num"><?= htmlspecialchars($number, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>

                        <?php if ($stepTitle !== ''): ?>
                            <h3><?= htmlspecialchars($stepTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php endif; ?>

                        <?php if ($stepText !== ''): ?>
                            <p><?= htmlspecialchars($stepText, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($primaryButtonLabel !== '' || $secondaryButtonLabel !== ''): ?>
            <div class="text-center mt-32">
                <?php if ($primaryButtonLabel !== ''): ?>
                    <a href="<?= htmlspecialchars($primaryButtonUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary">
                        <?= htmlspecialchars($primaryButtonLabel, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>

                <?php if ($secondaryButtonLabel !== ''): ?>
                    <a href="<?= htmlspecialchars($secondaryButtonUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--outline">
                        <?= htmlspecialchars($secondaryButtonLabel, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>