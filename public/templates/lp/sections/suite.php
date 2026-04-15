<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Voici la suite'), ENT_QUOTES, 'UTF-8');
$text = htmlspecialchars((string) ($data['text'] ?? 'Surveillez votre messagerie ou poursuivez vers l’étape suivante selon votre tunnel.'), ENT_QUOTES, 'UTF-8');

$buttonLabel = htmlspecialchars((string) ($data['button_label'] ?? 'Continuer'), ENT_QUOTES, 'UTF-8');

$buttonUrl = '/';
if (isset($data['button_url']) && is_string($data['button_url']) && $data['button_url'] !== '') {
    $buttonUrl = $data['button_url'];
} elseif (isset($pageData['next_url']) && is_string($pageData['next_url']) && $pageData['next_url'] !== '') {
    $buttonUrl = $pageData['next_url'];
}
$buttonUrl = htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--suite">
    <div class="container">
        <div class="lp-suite__content">
            <h2><?= $title ?></h2>
            <p><?= $text ?></p>
            <a class="btn btn-primary" href="<?= $buttonUrl ?>"><?= $buttonLabel ?></a>
        </div>
    </div>
</section>