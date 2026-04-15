<?php
$pageTitle  = 'Politique de confidentialité — Pascal Hamm Immobilier';
$metaDesc   = 'Politique de confidentialité et protection des données personnelles.';
$metaRobots = 'noindex, nofollow';
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Confidentialité</span></nav>
        <h1>Politique de confidentialité</h1>
        <p>Comment nous collectons, utilisons et protégeons vos données personnelles.</p>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:800px">
        <div class="article-content">
            <h2>Responsable du traitement</h2>
            <p><?= e(APP_NAME) ?> — <?= e(APP_ADDRESS) ?> — <a href="mailto:<?= e(APP_EMAIL) ?>"><?= e(APP_EMAIL) ?></a></p>

            <h2>Données collectées</h2>
            <p>Nous collectons les données suivantes via les formulaires du site :</p>
            <ul>
                <li>Nom et prénom</li>
                <li>Adresse email</li>
                <li>Numéro de téléphone (facultatif)</li>
                <li>Informations relatives à votre bien ou projet immobilier</li>
            </ul>

            <h2>Finalités du traitement</h2>
            <p>Vos données sont utilisées pour :</p>
            <ul>
                <li>Répondre à vos demandes de contact ou d'estimation</li>
                <li>Vous envoyer les informations demandées</li>
                <li>Vous recontacter dans le cadre de votre projet immobilier</li>
                <li>Améliorer nos services (données anonymisées)</li>
            </ul>

            <h2>Base légale</h2>
            <p>Le traitement de vos données repose sur votre consentement explicite, recueilli via nos formulaires.</p>

            <h2>Durée de conservation</h2>
            <p>Vos données sont conservées pendant 3 ans à compter de votre dernier contact, puis supprimées.</p>

            <h2>Vos droits</h2>
            <p>Conformément au RGPD, vous disposez des droits suivants :</p>
            <ul>
                <li>Droit d'accès à vos données</li>
                <li>Droit de rectification</li>
                <li>Droit à l'effacement (droit à l'oubli)</li>
                <li>Droit à la limitation du traitement</li>
                <li>Droit à la portabilité</li>
                <li>Droit d'opposition</li>
            </ul>
            <p>Pour exercer ces droits : <a href="mailto:<?= e(APP_EMAIL) ?>"><?= e(APP_EMAIL) ?></a></p>

            <h2>Cookies</h2>
            <p>Ce site utilise des cookies. Consultez notre <a href="/politique-cookies">politique cookies</a>.</p>

            <p><em>Dernière mise à jour : <?= date('d/m/Y') ?></em></p>
        </div>
    </div>
</section>
