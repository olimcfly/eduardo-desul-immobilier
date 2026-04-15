<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Ce qui change concrètement pour vous'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Des vendeurs qui viennent à vous naturellement',
    'Un discours clair qui vous positionne comme expert',
    'Un processus simple qui remplace la prospection à froid',
    'Plus de mandats exclusifs et moins de perte de temps'
];

?>

<section class="lp-section lp-section--transformation">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-transformation__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-transformation-item">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>