<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Vous êtes à une étape de la suite'), ENT_QUOTES, 'UTF-8');
$text = htmlspecialchars((string) ($data['text'] ?? 'Remplissez le formulaire pour recevoir la méthode et voir comment structurer votre acquisition plus proprement.'), ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--form-guide">
    <div class="container">
        <div class="lp-form-guide__content">
            <h2><?= $title ?></h2>
            <p><?= $text ?></p>
        </div>
    </div>
</section>