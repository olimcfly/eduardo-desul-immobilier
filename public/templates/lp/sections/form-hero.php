<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Accédez à la méthode complète'), ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars((string) ($data['subtitle'] ?? 'Remplissez ce formulaire pour recevoir la suite et passer à l’étape suivante.'), ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--form-hero">
    <div class="container">
        <div class="lp-form-hero__content">
            <p class="lp-kicker">Étape 2</p>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
    </div>
</section>