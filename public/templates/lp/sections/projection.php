<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Projetez-vous dans les prochains mois'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Un agenda rempli de rendez-vous vendeurs qualifiés',
    'Des mandats exclusifs signés régulièrement',
    'Une activité stable et prévisible',
    'Une image d’expert reconnu localement'
];

?>

<section class="lp-section lp-section--projection">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-projection__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-projection-item">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>