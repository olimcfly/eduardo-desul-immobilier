<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Une approche simple, sans risque'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Aucune compétence technique requise',
    'Mise en place rapide',
    'Adapté à votre activité locale',
    'Vous gardez le contrôle à chaque étape'
];

?>

<section class="lp-section lp-section--reassurance">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-reassurance__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-reassurance-item">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>