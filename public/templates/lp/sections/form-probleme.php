<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Avant d’aller plus loin, clarifions votre situation'), ENT_QUOTES, 'UTF-8');

$items = $data['items'] ?? [
    'Vous avez besoin de plus de mandats qualifiés',
    'Vous voulez arrêter de perdre du temps avec des prospects tièdes',
    'Vous cherchez un système simple à mettre en place',
];

if (!is_array($items)) {
    $items = [];
}
?>

<section class="lp-section lp-section--form-probleme">
    <div class="container">
        <div class="lp-form-probleme__content">
            <h2><?= $title ?></h2>

            <?php if ($items !== []): ?>
                <div class="lp-form-probleme__list">
                    <?php foreach ($items as $item): ?>
                        <div class="lp-form-probleme__item">
                            <p><?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>