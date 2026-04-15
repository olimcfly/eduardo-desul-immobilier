<?php
declare(strict_types=1);

$siteSettings = $siteSettings ?? [];

$advisorName  = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Votre conseiller'));
$advisorCity  = $advisorCity ?? ($siteSettings['city'] ?? ($_ENV['APP_CITY'] ?? 'Votre ville'));
$advisorPhone = $advisorPhone ?? ($siteSettings['phone'] ?? ($_ENV['APP_PHONE'] ?? ''));
$advisorEmail = $advisorEmail ?? ($siteSettings['email'] ?? ($_ENV['APP_EMAIL'] ?? ''));
$advisorAddress = $siteSettings['address'] ?? ($_ENV['APP_ADDRESS'] ?? $advisorCity);

$contactFormError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email   = trim((string)($_POST['email'] ?? ''));
    $prenom  = trim((string)($_POST['prenom'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if (
        $email !== '' &&
        $prenom !== '' &&
        filter_var($email, FILTER_VALIDATE_EMAIL) &&
        $message !== ''
    ) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_CONTACT,
            'pipeline'    => LeadService::SOURCE_CONTACT,
            'stage'       => 'a_traiter',
            'first_name'  => $prenom,
            'last_name'   => trim((string)($_POST['nom'] ?? '')),
            'email'       => $email,
            'phone'       => trim((string)($_POST['telephone'] ?? '')),
            'intent'      => trim((string)($_POST['sujet'] ?? 'Contact')),
            'notes'       => $message,
            'consent'     => !empty($_POST['rgpd']),
        ]);

        redirect('/merci');
    } else {
        $contactFormError = 'Merci de remplir correctement les champs obligatoires.';
    }
}

$pageTitle = "Contact — {$advisorName} | Immobilier {$advisorCity}";
$metaDesc  = "Contactez {$advisorName}, conseiller immobilier à {$advisorCity}. Réponse rapide et accompagnement personnalisé.";

$contactTitle     = $siteSettings['contact_title'] ?? "Contactez {$advisorName}";
$contactSubtitle  = $siteSettings['contact_subtitle'] ?? "Je vous réponds personnellement sous 24h.";
$contactFormTitle = $siteSettings['contact_form_title'] ?? "Envoyez-moi un message";

$contactPhoneHref = preg_replace('/\s+/', '', (string)$advisorPhone);
$mapEmbed = $siteSettings['contact_map_embed'] ?? '';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a><span>Contact</span>
        </nav>
        <h1><?= e($contactTitle) ?></h1>
        <p><?= e($contactSubtitle) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="contact-layout">

            <!-- FORM -->
            <div class="contact-form-box">
                <h2><?= e($contactFormTitle) ?></h2>

                <?php if ($contactFormError): ?>
                    <div class="error-box"><?= e($contactFormError) ?></div>
                <?php endif; ?>

                <form method="POST" action="/contact">
                    <?= csrfField() ?>

                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="tel" name="telephone" placeholder="Téléphone">

                    <select name="sujet">
                        <option>Contact</option>
                        <option>Achat</option>
                        <option>Vente</option>
                        <option>Estimation</option>
                    </select>

                    <textarea name="message" placeholder="Votre message..." required></textarea>

                    <label>
                        <input type="checkbox" name="rgpd" required>
                        J'accepte la politique de confidentialité
                    </label>

                    <button type="submit" class="btn btn--primary">
                        Envoyer
                    </button>
                </form>
            </div>

            <!-- INFOS -->
            <div class="contact-info">

                <div class="contact-info-box">
                    <h3>Coordonnées</h3>

                    <p>📍 <?= e($advisorAddress) ?></p>

                    <?php if ($advisorPhone): ?>
                        <p>📞 <a href="tel:<?= e($contactPhoneHref) ?>"><?= e($advisorPhone) ?></a></p>
                    <?php endif; ?>

                    <p>✉️ <a href="mailto:<?= e($advisorEmail) ?>"><?= e($advisorEmail) ?></a></p>
                </div>

                <div class="contact-info-box">
                    <h3>Disponibilité</h3>
                    <p>Réponse sous 24h</p>
                    <p>Rendez-vous possible sur demande</p>
                </div>

                <div class="map">
                    <?php if ($mapEmbed): ?>
                        <?= $mapEmbed ?>
                    <?php else: ?>
                        <iframe src="https://maps.google.com/maps?q=<?= urlencode($advisorCity) ?>&output=embed"></iframe>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</section>