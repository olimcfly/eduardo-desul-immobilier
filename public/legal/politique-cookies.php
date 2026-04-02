<?php
$pageTitle  = 'Politique cookies — Eduardo Desul Immobilier';
$metaRobots = 'noindex, nofollow';
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Cookies</span></nav>
        <h1>Politique cookies</h1>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:800px">
        <div class="article-content">
            <h2>Qu'est-ce qu'un cookie ?</h2>
            <p>Un cookie est un petit fichier texte déposé sur votre navigateur lors de votre visite sur un site. Il permet de mémoriser des informations sur votre navigation.</p>

            <h2>Cookies utilisés sur ce site</h2>
            <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                <thead style="background:var(--clr-bg)">
                    <tr>
                        <th style="padding:.75rem;text-align:left;border-bottom:2px solid var(--clr-border)">Cookie</th>
                        <th style="padding:.75rem;text-align:left;border-bottom:2px solid var(--clr-border)">Type</th>
                        <th style="padding:.75rem;text-align:left;border-bottom:2px solid var(--clr-border)">Durée</th>
                        <th style="padding:.75rem;text-align:left;border-bottom:2px solid var(--clr-border)">Finalité</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--clr-border)">
                        <td style="padding:.75rem">edo_immo_sess</td>
                        <td style="padding:.75rem">Technique</td>
                        <td style="padding:.75rem">Session</td>
                        <td style="padding:.75rem">Maintien de la session (formulaires, CSRF)</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--clr-border)">
                        <td style="padding:.75rem">edo_cookies_accepted</td>
                        <td style="padding:.75rem">Préférence</td>
                        <td style="padding:.75rem">1 an</td>
                        <td style="padding:.75rem">Mémorise votre choix concernant les cookies</td>
                    </tr>
                </tbody>
            </table>

            <h2>Gestion des cookies</h2>
            <p>Vous pouvez gérer vos préférences via le bandeau cookie présent lors de votre première visite. Vous pouvez également supprimer les cookies via les paramètres de votre navigateur.</p>

            <h2>Contact</h2>
            <p>Questions : <a href="mailto:<?= e(APP_EMAIL) ?>"><?= e(APP_EMAIL) ?></a></p>
            <p><em>Mise à jour : <?= date('d/m/Y') ?></em></p>
        </div>
    </div>
</section>
