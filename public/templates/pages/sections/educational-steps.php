<?php
declare(strict_types=1);

$sectionTitle = trim((string) ($section['title'] ?? 'Comment ça se passe'));
$sectionSubtitle = trim((string) ($section['subtitle'] ?? 'Expliquez simplement votre méthode d’accompagnement.'));
$items = $section['items'] ?? [];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="section section-educational-steps">
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
            <div class="edu-steps">
                <?php foreach ($items as $index => $item): ?>
                    <?php
                    $title = trim((string) ($item['title'] ?? ''));
                    $text = trim((string) ($item['text'] ?? ''));
                    ?>
                    <article class="edu-step">
                        <div class="edu-step__number"><?= (int) $index + 1 ?></div>
                        <div class="edu-step__content">
                            <?php if ($title !== ''): ?>
                                <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php endif; ?>

                            <?php if ($text !== ''): ?>
                                <p><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>