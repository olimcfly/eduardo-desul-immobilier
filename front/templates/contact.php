<?php
/**
 * contact.php — /front/templates/contact.php
 * Template formulaire de contact
 * Variables attendues : $pageTitle, $settings (array site)
 */
$settings = $settings ?? [];
$phone    = $settings['phone']   ?? _ss('phone', '');
$email    = $settings['email']   ?? _ss('email', '');
$address  = $settings['address'] ?? _ss('address', '');
$success  = $_GET['sent'] ?? false;
$error    = '';

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $mail    = trim($_POST['email']   ?? '');
    $tel     = trim($_POST['phone']   ?? '');
    $subject = trim($_POST['subject'] ?? 'Contact site');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$mail || !$message) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $sent = false;
        if (class_exists('EmailService')) {
            try {
                $es   = new EmailService();
                $body = "Nouveau message de contact :\n\nNom : $name\nEmail : $mail\nTél : $tel\n\nMessage :\n$message";
                $sent = $es->send($email, $subject, $body);
            } catch (Exception $e) { /* fallback mail() */ }
        }
        if (!$sent) {
            $noreplyDomain = parse_url(_ss('site_url', ''), PHP_URL_HOST) ?: 'exemple.fr';
            $headers = "From: noreply@$noreplyDomain\r\nReply-To: $mail\r\nContent-Type: text/plain; charset=UTF-8";
            $sent    = mail($email, "[$subject] $name", "Nom : $name\nEmail : $mail\nTél : $tel\n\nMessage :\n$message", $headers);
        }
        if ($sent) {
            header('Location: ?sent=1');
            exit;
        }
        $error = 'Une erreur est survenue. Veuillez réessayer ou nous appeler directement.';
    }
}
?>
<section class="section">
  <div class="container container--narrow">

    <div class="section-title">
      <h1><?= htmlspecialchars($pageTitle ?? _ss('contact_title', 'Contactez-nous')) ?></h1>
      <div class="separator"></div>
      <p><?= htmlspecialchars(_ss('contact_subtitle', 'Nous vous répondons dans les meilleurs délais.')) ?></p>
    </div>

    <?php if ($success): ?>
    <div class="contact-alert contact-alert--success">
      <i class="fas fa-check-circle contact-alert__icon"></i>
      <div class="contact-alert__title">Message envoyé !</div>
      <div class="contact-alert__text">Nous vous répondrons dans les meilleurs délais.</div>
    </div>
    <?php elseif ($error): ?>
    <div class="contact-alert contact-alert--error">
      <i class="fas fa-exclamation-triangle contact-alert__icon-inline"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="grid-2 contact-layout">

      <!-- Formulaire -->
      <div class="card card-body">
        <form method="POST" action="">
          <div class="form-group">
            <label>Nom complet *</label>
            <input type="text" name="name" placeholder="Votre nom" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="votre@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input type="tel" name="phone" placeholder="06 XX XX XX XX" value="<?= htmlspecialchars($_POST['phone']??'') ?>">
          </div>
          <div class="form-group">
            <label>Objet</label>
            <select name="subject">
              <option value="Estimation immobilière">Estimation immobilière</option>
              <option value="Projet d'achat">Projet d'achat</option>
              <option value="Projet de vente">Projet de vente</option>
              <option value="Investissement locatif">Investissement locatif</option>
              <option value="Autre demande">Autre demande</option>
            </select>
          </div>
          <div class="form-group">
            <label>Message *</label>
            <textarea name="message" placeholder="Décrivez votre projet en quelques mots…" required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary contact-submit-btn">
            <i class="fas fa-paper-plane"></i> Envoyer le message
          </button>
          <p class="contact-legal-note">
            Vos données sont utilisées uniquement pour traiter votre demande — <a href="/mentions-legales">Mentions légales</a>
          </p>
        </form>
      </div>

      <!-- Infos contact -->
      <div>
        <div class="card card-body contact-info-card">
          <div class="contact-info-row">
            <div class="contact-info-icon-wrap">
              <i class="fas fa-phone"></i>
            </div>
            <div>
              <div class="contact-info-label">Téléphone</div>
              <a href="tel:<?= preg_replace('/\s/','',$phone) ?>" class="contact-info-value contact-info-value--phone"><?= htmlspecialchars($phone) ?></a>
            </div>
          </div>
          <div class="contact-info-row">
            <div class="contact-info-icon-wrap">
              <i class="fas fa-envelope"></i>
            </div>
            <div>
              <div class="contact-info-label">Email</div>
              <a href="mailto:<?= htmlspecialchars($email) ?>" class="contact-info-value contact-info-value--email"><?= htmlspecialchars($email) ?></a>
            </div>
          </div>
          <div class="contact-info-row contact-info-row--last">
            <div class="contact-info-icon-wrap">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <div class="contact-info-label">Adresse</div>
              <div class="contact-info-address"><?= htmlspecialchars($address) ?></div>
            </div>
          </div>
        </div>
        <div class="card card-body contact-hours-card">
          <div class="contact-hours-title">
            <i class="fas fa-clock contact-hours-title__icon"></i>Disponibilités
          </div>
          <div class="contact-hours-content">
            <?= _ss('business_hours', "Lundi – Vendredi : 9h – 19h<br>Samedi : sur RDV") ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
