<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Ce que vous voulez vraiment'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Recevoir des demandes vendeurs qualifiées',
    'Avoir un flux constant de mandats',
    'Travailler uniquement avec des prospects sérieux',
    'Développer votre activité sans prospecter à froid'
];

?>

<section class="lp-section lp-section--motivations">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-motivations__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-motivation">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>