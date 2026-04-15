<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Ce que vous allez obtenir ensuite'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Une structure claire pour attirer des vendeurs',
    'Un angle de message plus convaincant',
    'Une suite logique pour passer à l’action',
];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="lp-section lp-section--form-plan">
    <div class="container">
        <div class="lp-form-plan__content">
            <h2><?= $title ?></h2>

            <?php if ($items !== []): ?>
                <div class="lp-form-plan__list">
                    <?php foreach ($items as $item): ?>
                        <div class="lp-form-plan__item">
                            <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>