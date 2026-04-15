<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Pourquoi vous n’obtenez pas assez de mandats ?'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Vous dépendez encore du bouche-à-oreille',
    'Vos prospects ne sont pas réellement qualifiés',
    'Vous perdez du temps avec des contacts peu sérieux',
    'Vous manquez de visibilité locale',
];

?>

<section class="lp-section lp-section--problemes">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-problemes__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-probleme">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>