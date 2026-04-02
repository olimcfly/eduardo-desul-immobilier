<?php
/**
 * contact.php — /front/templates/contact.php
 * Template formulaire de contact (version enrichie)
 */

$settings = $settings ?? [];
$advisor = [
    'full_name'    => $settings['advisor_name'] ?? _ss('advisor_name', _ss('site_name', 'Conseiller Immobilier')),
    'phone'        => $settings['phone'] ?? _ss('phone', ''),
    'email'        => $settings['email'] ?? _ss('email', ''),
    'city'         => $settings['city'] ?? _ss('city', ''),
    'photo_url'    => $settings['advisor_photo'] ?? _ss('advisor_photo', ''),
    'linkedin_url' => $settings['linkedin_url'] ?? _ss('linkedin_url', ''),
    'facebook_url' => $settings['facebook_url'] ?? _ss('facebook_url', ''),
];

$pageTitle = $pageTitle ?? 'Contact — ' . ($advisor['full_name'] ?: 'Conseiller Immobilier');

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $subject   = trim($_POST['subject'] ?? 'info');
    $message   = trim($_POST['message'] ?? '');
    $website   = trim($_POST['website'] ?? ''); // honeypot
    $rgpd      = isset($_POST['rgpd']);
    $availability = $_POST['availability'] ?? [];

    if ($website !== '') {
        $error = 'Impossible d\'envoyer votre demande.';
    } elseif (!$firstName || !$lastName || !$email || !$message) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!$rgpd) {
        $error = 'Merci d\'accepter la politique de confidentialité.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (mb_strlen($message) > 1000) {
        $error = 'Le message ne doit pas dépasser 1000 caractères.';
    } else {
        $subjectMap = [
            'sell' => 'Vendre mon bien',
            'buy' => 'Acheter un bien',
            'estimate' => 'Faire estimer',
            'info' => 'Demande d\'information',
        ];
        $subjectLabel = $subjectMap[$subject] ?? 'Contact';
        $availText = is_array($availability) ? implode(', ', array_map('strval', $availability)) : '';

        $mailBody = "Nouveau message de contact :\n\n"
            . "Nom : {$firstName} {$lastName}\n"
            . "Email : {$email}\n"
            . "Téléphone : {$phone}\n"
            . "Sujet : {$subjectLabel}\n"
            . "Disponibilités : {$availText}\n\n"
            . "Message :\n{$message}\n";

        $recipient = $advisor['email'] ?: $email;
        $sent = false;

        if (class_exists('EmailService')) {
            try {
                $es = new EmailService();
                $sent = $es->send($recipient, "[Contact] {$subjectLabel}", $mailBody);
            } catch (Exception $e) {
                $sent = false;
            }
        }

        if (!$sent) {
            $noreplyDomain = parse_url(_ss('site_url', ''), PHP_URL_HOST) ?: 'exemple.fr';
            $headers = "From: noreply@{$noreplyDomain}\r\n"
                . "Reply-To: {$email}\r\n"
                . "Content-Type: text/plain; charset=UTF-8";
            $sent = mail($recipient, "[Contact] {$subjectLabel}", $mailBody, $headers);
        }

        if ($sent) {
            $success = true;
        } else {
            $error = 'Une erreur est survenue. Veuillez réessayer ou nous appeler directement.';
        }
    }
}
?>

<link rel="stylesheet" href="/front/assets/css/contact.css">

<div class="contact-page">
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero__inner">
                <div class="contact-hero__content">
                    <span class="section-tag">Réponse sous 24h</span>
                    <h1>Parlons de votre <span>projet</span></h1>
                    <p>Que vous souhaitiez vendre, acheter ou simplement avoir un avis sur votre bien, je suis disponible pour vous accompagner.</p>
                </div>
                <div class="contact-hero__card">
                    <div class="advisor-card">
                        <?php if (!empty($advisor['photo_url'])): ?>
                        <img src="<?= htmlspecialchars($advisor['photo_url']) ?>" alt="<?= htmlspecialchars($advisor['full_name']) ?>" class="advisor-card__photo">
                        <?php endif; ?>
                        <div class="advisor-card__body">
                            <h3><?= htmlspecialchars($advisor['full_name'] ?: 'Votre conseiller') ?></h3>
                            <?php if (!empty($advisor['city'])): ?>
                            <p class="advisor-card__title">Conseiller immobilier — <?= htmlspecialchars($advisor['city']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($advisor['phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($advisor['phone']) ?>" class="advisor-contact-item"><?= htmlspecialchars($advisor['phone']) ?></a>
                            <?php endif; ?>
                            <?php if (!empty($advisor['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($advisor['email']) ?>" class="advisor-contact-item"><?= htmlspecialchars($advisor['email']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-main">
        <div class="container contact-grid">
            <div class="contact-form-wrapper">
                <?php if ($success): ?>
                <div class="contact-success" id="contactSuccess">
                    <h3>Message envoyé !</h3>
                    <p>Merci pour votre message. Je vous répondrai rapidement.</p>
                    <button class="btn btn--outline" onclick="resetContactForm()">Envoyer un autre message</button>
                </div>
                <?php else: ?>

                <?php if ($error): ?>
                <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="contact-form" id="contactForm" novalidate>
                    <div class="subject-selector">
                        <?php foreach ([
                            ['sell', 'Vendre mon bien'],
                            ['buy', 'Acheter un bien'],
                            ['estimate', 'Faire estimer'],
                            ['info', 'Avoir des infos'],
                        ] as [$val, $label]): ?>
                        <label class="subject-option">
                            <input type="radio" name="subject" value="<?= $val ?>" <?= (($_POST['subject'] ?? 'info') === $val) ? 'checked' : '' ?>>
                            <span class="subject-option__inner"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-row">
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Prénom" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Nom" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <textarea id="message" name="message" class="form-control form-control--textarea" placeholder="Décrivez votre projet ou votre question…" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <div class="char-counter"><span id="charCount">0</span>/1000</div>
                    </div>

                    <div class="availability-grid">
                        <?php
                        $selectedAvailability = is_array($_POST['availability'] ?? null) ? $_POST['availability'] : [];
                        foreach ([
                            'morning' => 'Matin (9h–12h)',
                            'afternoon' => 'Après-midi (14h–18h)',
                            'evening' => 'Soir (18h–19h)',
                            'weekend' => 'Week-end',
                        ] as $val => $label): ?>
                        <label class="avail-option">
                            <input type="checkbox" name="availability[]" value="<?= $val ?>" <?= in_array($val, $selectedAvailability, true) ? 'checked' : '' ?>>
                            <span><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <label class="rgpd-check">
                        <input type="checkbox" name="rgpd" required <?= isset($_POST['rgpd']) ? 'checked' : '' ?>>
                        <span>J'accepte que mes données soient utilisées pour me recontacter dans le cadre de ma demande. <a href="/politique-confidentialite" target="_blank">Politique de confidentialité</a></span>
                    </label>

                    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

                    <button type="submit" class="btn-contact-submit">Envoyer mon message</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script src="/front/assets/js/contact.js"></script>
