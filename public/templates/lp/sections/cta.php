<?php

declare(strict_types=1);

$title = htmlspecialchars((string) ($data['title'] ?? 'Passez à l’étape suivante'), ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars((string) ($data['subtitle'] ?? 'Accédez maintenant à la méthode complète.'), ENT_QUOTES, 'UTF-8');
$ctaLabel = htmlspecialchars((string) ($data['cta_label'] ?? 'Continuer'), ENT_QUOTES, 'UTF-8');

$formUrl = '/lp/neuroscript-form';
if (isset($pageData['form_url']) && is_string($pageData['form_url']) && $pageData['form_url'] !== '') {
    $formUrl = $pageData['form_url'];
}
$formUrl = htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--cta">
    <div class="container">

        <h2><?= $title ?></h2>
        <p><?= $subtitle ?></p>

        <a class="btn btn-primary" href="<?= $formUrl ?>">
            <?= $ctaLabel ?>
        </a>

    </div>
</section>