<?php
/**
 * renderers/politique-confidentialite.php
 * Politique de confidentialité – Eduardo Desul Immobilier
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

global $db;
if (!$db) $db = getDB();

// ── Header / Footer ──────────────────────────────────────
if (!function_exists('_loadHF_v5')) {
    function _loadHF_v5(PDO $db, string $type, ?int $specificId): ?array {
        $tables = ($type === 'header') ? ['headers', 'site_headers'] : ['footers', 'site_footers'];
        if ($specificId) {
            foreach ($tables as $tbl) {
                try {
                    $s = $db->prepare("SELECT * FROM `$tbl` WHERE id=? LIMIT 1");
                    $s->execute([$specificId]);
                    $row = $s->fetch(PDO::FETCH_ASSOC);
                    if ($row) return $row;
                } catch (Exception $e) {}
            }
        }
        foreach ($tables as $tbl) {
            try {
                $row = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($row) return $row;
            } catch (Exception $e) {}
        }
        return null;
    }
}

$hf = [
    'header' => _loadHF_v5($db, 'header', null),
    'footer' => _loadHF_v5($db, 'footer', null),
];

$headerHtml = ($hf['header'] && function_exists('renderHeader')) ? renderHeader($hf['header']) : '';
$footerHtml = ($hf['footer'] && function_exists('renderFooter')) ? renderFooter($hf['footer']) : '';

$extractedStyles = '';
$stripStyles = function(&$html) use (&$extractedStyles) {
    $html = preg_replace_callback(
        '/<style(\b[^>]*)>(.+?)<\/style>/is',
        function($m) use (&$extractedStyles) {
            $extractedStyles .= "\n<style{$m[1]}>{$m[2]}</style>";
            return '';
        },
        $html
    );
};
$stripStyles($headerHtml);
$stripStyles($footerHtml);

$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')   ? SITE_URL   : '');
$_siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialité | <?= htmlspecialchars($_siteName) ?></title>
    <meta name="description" content="Politique de confidentialité et protection des données personnelles du site Eduardo Desul Immobilier.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="<?= htmlspecialchars($_siteUrl) ?>/politique-confidentialite">

    <?php if (function_exists('eduardoHead')): echo eduardoHead(); endif; ?>

    <style id="ed-base-css">
    :root {
      --ed-primary: #1a4d7a;
      --ed-primary-dk: #0e3a5c;
      --ed-accent: #d4a574;
      --ed-accent-lt: #e8c49a;
      --ed-text: #2d3748;
      --ed-text-light: #718096;
      --ed-card-bg: #f9f6f3;
      --ed-border: #e2d9ce;
      --ed-bg: #f9f6f3;
      --ff-heading: 'Playfair Display', serif;
      --ff-body: 'DM Sans', sans-serif;
      --ed-radius: 8px;
      --ed-radius-lg: 12px;
      --ed-shadow: 0 2px 8px rgba(0,0,0,.07);
      --ed-shadow-lg: 0 8px 30px rgba(0,0,0,.12);
      --ed-transition: all .2s ease;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: var(--ff-body);
      color: var(--ed-text);
      background: #fff;
      line-height: 1.6;
      margin: 0;
    }
    .privacy-container {
      max-width: 860px;
      margin: 0 auto;
      padding: 48px 24px 80px;
    }
    .privacy-container h1 {
      font-family: var(--ff-heading);
      font-size: clamp(28px, 4vw, 40px);
      font-weight: 700;
      color: var(--ed-primary);
      margin-bottom: 8px;
      line-height: 1.2;
    }
    .privacy-container .last-updated {
      color: var(--ed-text-light);
      font-size: 14px;
      margin-bottom: 40px;
    }
    .privacy-container h2 {
      font-family: var(--ff-heading);
      font-size: 22px;
      font-weight: 700;
      color: var(--ed-primary);
      margin-top: 40px;
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 2px solid var(--ed-accent);
    }
    .privacy-container h3 {
      font-size: 17px;
      font-weight: 600;
      color: var(--ed-primary-dk);
      margin-top: 24px;
      margin-bottom: 8px;
    }
    .privacy-container p {
      margin-bottom: 14px;
      color: var(--ed-text);
      font-size: 15px;
      line-height: 1.7;
    }
    .privacy-container ul {
      margin: 8px 0 16px 24px;
      padding: 0;
    }
    .privacy-container li {
      margin-bottom: 6px;
      font-size: 15px;
      line-height: 1.6;
      color: var(--ed-text);
    }
    .privacy-container a {
      color: var(--ed-primary);
      text-decoration: underline;
    }
    .privacy-container a:hover {
      color: var(--ed-accent);
    }
    </style>

    <?php if (!empty($hf['header']['custom_css'])): ?>
        <style><?= $hf['header']['custom_css'] ?></style>
    <?php endif; ?>
    <?php if (!empty($hf['footer']['custom_css'])): ?>
        <style><?= $hf['footer']['custom_css'] ?></style>
    <?php endif; ?>

    <?= $extractedStyles ?>
</head>
<body>

<?php if ($headerHtml): echo $headerHtml; endif; ?>

<main id="main-content">
<div class="privacy-container">

<h1>Politique de confidentialité</h1>
<p class="last-updated">Dernière mise à jour : 24 mars 2026</p>

<p>Le site <strong>eduardo-desul-immobilier.fr</strong> est édité par Eduardo De Sul, agent commercial en immobilier indépendant partenaire du réseau eXp France. La protection de vos données personnelles est une priorité. Cette politique de confidentialité vous informe sur la manière dont vos données sont collectées, utilisées et protégées, conformément au Règlement Général sur la Protection des Données (RGPD – Règlement UE 2016/679) et à la loi Informatique et Libertés du 6 janvier 1978 modifiée.</p>

<h2>1. Responsable du traitement</h2>
<p>Le responsable du traitement des données personnelles est :</p>
<ul>
    <li><strong>Nom :</strong> Eduardo De Sul</li>
    <li><strong>Activité :</strong> Agent commercial en immobilier – partenaire eXp France</li>
    <li><strong>Adresse :</strong> 12A rue du Commandant Charcot, 33290 Blanquefort</li>
    <li><strong>Téléphone :</strong> 06 24 10 58 16</li>
    <li><strong>Email :</strong> <a href="mailto:contact@eduardo-desul-immobilier.fr">contact@eduardo-desul-immobilier.fr</a></li>
</ul>

<h2>2. Données personnelles collectées</h2>
<p>Nous collectons les données personnelles suivantes, selon les interactions que vous avez avec notre site :</p>

<h3>a) Données fournies volontairement</h3>
<p>Lorsque vous remplissez un formulaire de contact, de demande d'estimation ou de prise de rendez-vous :</p>
<ul>
    <li>Nom et prénom</li>
    <li>Adresse email</li>
    <li>Numéro de téléphone</li>
    <li>Contenu de votre message ou demande</li>
    <li>Adresse du bien concerné (le cas échéant)</li>
</ul>

<h3>b) Données collectées automatiquement</h3>
<p>Lors de votre navigation sur le site :</p>
<ul>
    <li>Adresse IP</li>
    <li>Type de navigateur et système d'exploitation</li>
    <li>Pages consultées et durée de la visite</li>
    <li>Source de trafic (moteur de recherche, lien direct, etc.)</li>
</ul>

<h2>3. Finalités du traitement</h2>
<p>Vos données personnelles sont utilisées pour les finalités suivantes :</p>
<ul>
    <li>Répondre à vos demandes de contact et de renseignements</li>
    <li>Vous proposer des biens immobiliers correspondant à vos critères</li>
    <li>Réaliser des estimations immobilières</li>
    <li>Assurer le suivi de la relation commerciale</li>
    <li>Respecter les obligations légales applicables à l'activité immobilière (notamment la lutte contre le blanchiment de capitaux)</li>
    <li>Améliorer le fonctionnement et le contenu du site grâce aux données de navigation</li>
</ul>

<h2>4. Base légale du traitement</h2>
<p>Le traitement de vos données repose sur les bases légales suivantes :</p>
<ul>
    <li><strong>Votre consentement</strong> (article 6.1.a du RGPD) : lorsque vous remplissez un formulaire de contact ou acceptez les cookies</li>
    <li><strong>L'exécution de mesures précontractuelles</strong> (article 6.1.b) : traitement de votre demande d'estimation ou de recherche de bien</li>
    <li><strong>L'intérêt légitime</strong> (article 6.1.f) : amélioration du site et analyse de la fréquentation</li>
    <li><strong>L'obligation légale</strong> (article 6.1.c) : respect des obligations en matière de lutte contre le blanchiment et le financement du terrorisme (LCB-FT)</li>
</ul>

<h2>5. Destinataires des données</h2>
<p>Vos données personnelles peuvent être transmises aux destinataires suivants :</p>
<ul>
    <li>Eduardo De Sul, en sa qualité de responsable du traitement</li>
    <li>Le réseau eXp France, dans le cadre du suivi des transactions immobilières</li>
    <li>Les prestataires techniques nécessaires au fonctionnement du site (hébergement, emailing)</li>
    <li>Les autorités compétentes, sur demande légale</li>
</ul>
<p>Vos données ne sont en aucun cas vendues à des tiers.</p>

<h2>6. Durée de conservation</h2>
<p>Vos données personnelles sont conservées pour les durées suivantes :</p>
<ul>
    <li><strong>Données de contact :</strong> 3 ans à compter du dernier contact</li>
    <li><strong>Données de transaction immobilière :</strong> 10 ans (obligation légale)</li>
    <li><strong>Données de navigation (cookies, logs) :</strong> 13 mois maximum</li>
</ul>

<h2>7. Vos droits</h2>
<p>Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits suivants :</p>
<ul>
    <li><strong>Droit d'accès</strong> (article 15) : obtenir la confirmation que vos données sont traitées et en recevoir une copie</li>
    <li><strong>Droit de rectification</strong> (article 16) : corriger des données inexactes ou incomplètes</li>
    <li><strong>Droit à l'effacement</strong> (article 17) : demander la suppression de vos données</li>
    <li><strong>Droit à la limitation du traitement</strong> (article 18) : restreindre temporairement l'utilisation de vos données</li>
    <li><strong>Droit à la portabilité</strong> (article 20) : recevoir vos données dans un format structuré et lisible</li>
    <li><strong>Droit d'opposition</strong> (article 21) : vous opposer au traitement de vos données pour des motifs légitimes</li>
    <li><strong>Droit de retirer votre consentement</strong> à tout moment</li>
</ul>
<p>Pour exercer ces droits, contactez-nous par email à <a href="mailto:contact@eduardo-desul-immobilier.fr">contact@eduardo-desul-immobilier.fr</a> ou par courrier à l'adresse indiquée ci-dessus. Nous nous engageons à répondre dans un délai d'un mois.</p>
<p>En cas de litige, vous pouvez adresser une réclamation à la CNIL (Commission Nationale de l'Informatique et des Libertés) : <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>.</p>

<h2>8. Cookies</h2>
<p>Le site utilise des cookies pour assurer son bon fonctionnement et analyser la fréquentation :</p>
<ul>
    <li><strong>Cookies essentiels :</strong> nécessaires au fonctionnement du site (session, sécurité). Ils ne requièrent pas votre consentement.</li>
    <li><strong>Cookies analytiques :</strong> utilisés pour mesurer l'audience du site (Google Analytics ou équivalent). Ces cookies sont déposés uniquement après votre consentement.</li>
</ul>
<p>Vous pouvez à tout moment modifier vos préférences en matière de cookies via les paramètres de votre navigateur.</p>

<h2>9. Sécurité des données</h2>
<p>Nous mettons en œuvre les mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre tout accès non autorisé, modification, divulgation ou destruction. Le site utilise le protocole HTTPS pour sécuriser les échanges de données.</p>

<h2>10. Hébergement</h2>
<p>Le site est hébergé par :</p>
<ul>
    <li><strong>o2switch</strong></li>
    <li><strong>Adresse :</strong> Chemin des Pardiaux, 63000 Clermont-Ferrand, France</li>
    <li><strong>Site web :</strong> <a href="https://www.o2switch.fr" target="_blank" rel="noopener">www.o2switch.fr</a></li>
</ul>

<h2>11. Modification de la politique</h2>
<p>Cette politique de confidentialité peut être mise à jour à tout moment. La date de dernière mise à jour est indiquée en haut de cette page. Nous vous invitons à la consulter régulièrement.</p>

<h2>12. Contact</h2>
<p>Pour toute question relative à cette politique de confidentialité ou au traitement de vos données personnelles, contactez-nous :</p>
<ul>
    <li><strong>Email :</strong> <a href="mailto:contact@eduardo-desul-immobilier.fr">contact@eduardo-desul-immobilier.fr</a></li>
    <li><strong>Téléphone :</strong> 06 24 10 58 16</li>
    <li><strong>Adresse :</strong> 12A rue du Commandant Charcot, 33290 Blanquefort</li>
</ul>

</div>
</main>

<?php if ($footerHtml): echo $footerHtml; endif; ?>

</body>
</html>