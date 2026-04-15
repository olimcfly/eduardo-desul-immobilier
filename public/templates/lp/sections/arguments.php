<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Une méthode structurée pour attirer des vendeurs'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Un positionnement clair qui rassure les vendeurs',
    'Une stratégie locale ciblée',
    'Un système automatisé de génération de leads',
    'Un parcours simple qui convertit naturellement'
];

?>

<section class="lp-section lp-section--arguments">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-arguments__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-argument">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>