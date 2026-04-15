<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Ce que ça vous coûte vraiment'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Des mois sans mandat exclusif',
    'Un chiffre d’affaires instable',
    'Une dépendance aux plateformes',
    'Une perte de crédibilité face aux vendeurs'
];

?>

<section class="lp-section lp-section--enjeux">
    <div class="container">

        <h2><?= $title ?></h2>

        <div class="lp-enjeux__list">
            <?php foreach ($items as $item): ?>
                <div class="lp-enjeu">
                    <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>