<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Merci, votre demande a bien été envoyée'), ENT_QUOTES, 'UTF-8');
$text = htmlspecialchars((string) ($data['text'] ?? 'Nous avons bien reçu vos informations. Vous pouvez maintenant passer à la suite.'), ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--confirmation">
    <div class="container">
        <div class="lp-confirmation__content">
            <p class="lp-kicker">Étape 3</p>
            <h1><?= $title ?></h1>
            <p><?= $text ?></p>
        </div>
    </div>
</section>