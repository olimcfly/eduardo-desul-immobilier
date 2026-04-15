<?php
declare(strict_types=1);

$sectionTitle = trim((string) ($section['title'] ?? 'Questions fréquentes'));
$sectionSubtitle = trim((string) ($section['subtitle'] ?? 'Répondez aux objections avant même le premier échange.'));
$items = $section['items'] ?? [];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="section section-faq">
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
            <div class="faq-list">
                <?php foreach ($items as $item): ?>
                    <?php
                    $question = trim((string) ($item['question'] ?? ''));
                    $answer = trim((string) ($item['answer'] ?? ''));
                    if ($question === '' && $answer === '') {
                        continue;
                    }
                    ?>
                    <details class="faq-item">
                        <?php if ($question !== ''): ?>
                            <summary><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></summary>
                        <?php endif; ?>

                        <?php if ($answer !== ''): ?>
                            <div class="faq-item__answer">
                                <p><?= nl2br(htmlspecialchars($answer, ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        <?php endif; ?>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>