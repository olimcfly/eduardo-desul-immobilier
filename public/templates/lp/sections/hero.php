<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Obtenez plus de mandats vendeurs sans prospecter à froid'), ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars((string) ($data['subtitle'] ?? 'Une page claire, une promesse forte, une suite logique vers le formulaire.'), ENT_QUOTES, 'UTF-8');
$ctaLabel = htmlspecialchars((string) ($data['cta_label'] ?? 'Accéder à la suite'), ENT_QUOTES, 'UTF-8');

$formUrl = '/lp/neuroscript-form';
if (isset($pageData['form_url']) && is_string($pageData['form_url']) && $pageData['form_url'] !== '') {
    $formUrl = $pageData['form_url'];
}
$formUrl = htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--hero">
    <div class="container">
        <div class="lp-hero__content">
            <p class="lp-kicker">Méthode Neuroscript</p>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
            <a class="btn btn-primary" href="<?= $formUrl ?>"><?= $ctaLabel ?></a>
        </div>
    </div>
</section>