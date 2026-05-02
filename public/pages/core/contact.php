<?php
declare(strict_types=1);

$siteSettings = $siteSettings ?? [];
$extraCss = array_values(array_unique(array_merge($extraCss ?? [], ['/assets/css/contact.css'])));
$bodyClass = trim(($bodyClass ?? '') . ' page-contact');

$advisorName  = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Eduardo Desul'));
$advisorCity  = trim((string) ($advisorCity ?? setting('zone_city', APP_CITY ?: 'Bordeaux')));
if ($advisorCity === '') {
    $advisorCity = 'Bordeaux';
}
$advisorPhone = trim((string) ($advisorPhone ?? setting('advisor_phone', setting('profil_telephone', APP_PHONE))));
$advisorEmail = trim((string) ($advisorEmail ?? setting('advisor_email', setting('profil_email', APP_EMAIL))));
$advisorRsac = trim((string) setting('advisor_rsac', setting('profil_rsac', ADVISOR_RSAC)));
$advisorAddress = trim((string) (APP_ADDRESS ?: setting('agency_address', $siteSettings['address'] ?? $advisorCity)));
$mapQuery = trim((string) setting('agency_address', APP_ADDRESS ?: $advisorCity));

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

        // Track conversion
        ConversionTrackingService::track(
            ConversionTrackingService::TYPE_CONTACT_FORM,
            email: $email,
            firstName: $prenom,
            phone: trim((string)($_POST['telephone'] ?? null)),
            metadata: [
                'sujet' => trim((string)($_POST['sujet'] ?? 'Contact')),
                'message_length' => strlen($message),
            ]
        );

        redirect('/merci');
    } else {
        $contactFormError = 'Merci de remplir correctement les champs obligatoires.';
    }
}

$pageTitle = pcms('seo', 'page_title', 'Contact Eduardo Desul Immobilier à {{zone_city}}');
$metaDesc = pcms('seo', 'meta_description', 'Contactez Eduardo Desul Immobilier pour vendre, acheter ou estimer un bien à {{zone_city}} et dans la métropole bordelaise.');

$contactTitle = $siteSettings['contact_title'] ?? 'Contactez {{advisor_name}}';
$contactTitle = pcms('content', 'contact_title', $contactTitle);

$contactSubtitle = $siteSettings['contact_subtitle'] ?? 'Je vous réponds personnellement sous 24h.';
$contactSubtitle = pcms('content', 'contact_subtitle', $contactSubtitle);

$contactFormTitle = $siteSettings['contact_form_title'] ?? 'Envoyez-moi un message';
$contactFormTitle = pcms('content', 'contact_form_title', $contactFormTitle);

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

<section class="section section--alt page-contact__section">
    <div class="container">
        <div class="contact-layout">

            <div class="contact-form-box">
                <h2><?= e($contactFormTitle) ?></h2>
                <p class="contact-form-box__hint">Les champs marqués d’un astérisque sont obligatoires.</p>

                <?php if ($contactFormError): ?>
                    <div class="contact-alert contact-alert--error" role="alert"><?= e($contactFormError) ?></div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="/contact" novalidate>
                    <?= csrfField() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact-prenom">Prénom <span aria-hidden="true">*</span></label>
                            <input class="form-control" id="contact-prenom" type="text" name="prenom" placeholder="Prénom" required autocomplete="given-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-nom">Nom <span aria-hidden="true">*</span></label>
                            <input class="form-control" id="contact-nom" type="text" name="nom" placeholder="Nom" required autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact-email">Email <span aria-hidden="true">*</span></label>
                            <input class="form-control" id="contact-email" type="email" name="email" placeholder="vous@exemple.fr" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-tel">Téléphone</label>
                            <input class="form-control" id="contact-tel" type="tel" name="telephone" placeholder="06 …" autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-sujet">Sujet</label>
                        <select class="form-control" id="contact-sujet" name="sujet">
                            <option>Contact</option>
                            <option>Achat</option>
                            <option>Vente</option>
                            <option>Estimation</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-message">Message <span aria-hidden="true">*</span></label>
                        <textarea class="form-control" id="contact-message" name="message" placeholder="Votre message…" required rows="6"></textarea>
                    </div>

                    <div class="form-group contact-form__rgpd">
                        <label class="contact-checkbox">
                            <input type="checkbox" name="rgpd" value="1" required>
                            <span>J’accepte la <a href="/politique-confidentialite">politique de confidentialité</a>.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--lg btn--full">
                        Envoyer le message
                    </button>
                </form>
            </div>

            <aside class="contact-info" aria-label="Coordonnées et plan">

                <div class="contact-info-box">
                    <h3>Coordonnées</h3>
                    <?php if ($advisorAddress !== ''): ?>
                    <div class="info-item">
                        <span class="info-icon" aria-hidden="true">📍</span>
                        <div class="info-text">
                            <strong>Adresse</strong>
                            <p><?= e($advisorAddress) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($advisorPhone): ?>
                        <div class="info-item">
                            <span class="info-icon" aria-hidden="true">📞</span>
                            <div class="info-text">
                                <strong>Téléphone</strong>
                                <p><a href="tel:<?= e($contactPhoneHref) ?>"><?= e($advisorPhone) ?></a></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($advisorEmail !== ''): ?>
                    <div class="info-item">
                        <span class="info-icon" aria-hidden="true">✉️</span>
                        <div class="info-text">
                            <strong>Email</strong>
                            <p><a href="mailto:<?= e($advisorEmail) ?>"><?= e($advisorEmail) ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($advisorRsac !== ''): ?>
                    <div class="info-item">
                        <span class="info-icon" aria-hidden="true">#</span>
                        <div class="info-text">
                            <strong>RSAC</strong>
                            <p><?= e($advisorRsac) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="contact-info-box">
                    <h3>Disponibilité</h3>
                    <p>Réponse sous 24h ouvrées.</p>
                    <p>Rendez-vous sur demande.</p>
                </div>

                <div class="contact-map">
                    <?php if ($mapEmbed): ?>
                        <div class="contact-map__embed">
                            <?= $mapEmbed ?>
                        </div>
                    <?php else: ?>
                        <iframe
                            class="contact-map__iframe"
                            title="Carte — <?= e($mapQuery) ?>"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q=<?= urlencode($mapQuery) ?>&output=embed"></iframe>
                    <?php endif; ?>
                </div>

            </aside>

        </div>
    </div>
</section>
