<?php
$pageTitle  = 'Mentions légales — ' . APP_NAME;
$metaDesc   = 'Mentions légales du site ' . APP_NAME . '.';
$metaRobots = 'noindex, nofollow';
$advisorPhone = trim((string) setting('advisor_phone', setting('profil_telephone', APP_PHONE)));
$advisorEmail = trim((string) setting('advisor_email', setting('profil_email', APP_EMAIL)));
$advisorRsac = trim((string) setting('advisor_rsac', setting('profil_rsac', ADVISOR_RSAC)));
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Mentions légales</span></nav>
        <h1>Mentions légales</h1>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:800px">
        <div class="article-content">
            <h2>Éditeur du site</h2>
            <p><strong><?= e(APP_NAME) ?></strong><br>
            Conseiller immobilier indépendant<br>
            <?= e(APP_ADDRESS) ?><br>
            <?php if ($advisorEmail !== ''): ?>Email : <a href="mailto:<?= e($advisorEmail) ?>"><?= e($advisorEmail) ?></a><br><?php endif; ?>
            <?php if ($advisorPhone !== ''): ?>Téléphone : <?= e($advisorPhone) ?><br><?php endif; ?>
            <?php if ($advisorRsac !== ''): ?>RCS / RSAC : <?= e($advisorRsac) ?><br><?php endif; ?>
            <?php if (APP_SIRET): ?>SIRET : <?= e(APP_SIRET) ?><?php endif; ?>
            </p>
            <h2>Activité réglementée</h2>
            <p><?= ADVISOR_NAME ?> exerce l'activité de transaction immobilière en qualité de mandataire indépendant, titulaire d'une carte professionnelle délivrée par la CCI de Bordeaux-Gironde conformément à la loi Hoguet n° 70-9 du 2 janvier 1970.</p>
            <h2>Hébergement</h2>
            <p>Ce site est hébergé par un prestataire d'hébergement professionnel.</p>
            <h2>Propriété intellectuelle</h2>
            <p>L'ensemble du contenu de ce site (textes, images, logos) est la propriété exclusive de <?= APP_NAME ?>, sauf mention contraire. Toute reproduction, même partielle, est interdite sans accord préalable.</p>
            <h2>Responsabilité</h2>
            <p>Les informations présentes sur ce site sont données à titre indicatif. <?= APP_NAME ?> ne saurait être tenu responsable des erreurs ou omissions éventuelles, ni des dommages résultant de l'utilisation de ces informations.</p>
            <h2>Contact</h2>
            <p>Pour toute question relative au site : <a href="mailto:<?= e($advisorEmail !== '' ? $advisorEmail : APP_EMAIL) ?>"><?= e($advisorEmail !== '' ? $advisorEmail : APP_EMAIL) ?></a></p>
            <p><em>Dernière mise à jour : <?= date('d/m/Y') ?></em></p>
        </div>
    </div>
</section>
