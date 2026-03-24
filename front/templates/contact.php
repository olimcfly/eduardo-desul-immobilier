<?php
/**
 * contact.php — /front/templates/contact.php
 * Template formulaire de contact
 * Variables attendues : $pageTitle, $settings (array site)
 */
$settings = $settings ?? [];
$phone    = $settings['phone']   ?? '06 24 10 58 16';
$email    = $settings['email']   ?? 'contact@eduardo-desul-immobilier.fr';
$address  = $settings['address'] ?? '12A rue du Commandant Charcot, 33290 Blanquefort';
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
            $headers = "From: noreply@eduardo-desul-immobilier.fr\r\nReply-To: $mail\r\nContent-Type: text/plain; charset=UTF-8";
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
      <h1><?= htmlspecialchars($pageTitle ?? 'Contactez Eduardo') ?></h1>
      <div class="separator"></div>
      <p>Réponse garantie sous 24h — Basé à Blanquefort, actif sur tout le Grand Bordeaux</p>
    </div>

    <?php if ($success): ?>
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:20px 24px;text-align:center;margin-bottom:32px">
      <i class="fas fa-check-circle" style="color:#059669;font-size:24px;margin-bottom:8px;display:block"></i>
      <div style="font-weight:700;color:#065f46">Message envoyé !</div>
      <div style="font-size:13px;color:#047857;margin-top:4px">Eduardo vous répondra dans les meilleurs délais.</div>
    </div>
    <?php elseif ($error): ?>
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:12px;padding:16px 20px;margin-bottom:24px;color:#b91c1c;font-size:14px">
      <i class="fas fa-exclamation-triangle" style="margin-right:8px"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="grid-2" style="gap:40px;align-items:start">

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
          <button type="submit" class="btn btn-primary" style="width:100%">
            <i class="fas fa-paper-plane"></i> Envoyer le message
          </button>
          <p style="font-size:11px;color:var(--text-3);text-align:center;margin-top:12px;margin-bottom:0">
            Vos données sont utilisées uniquement pour traiter votre demande — <a href="/mentions-legales">Mentions légales</a>
          </p>
        </form>
      </div>

      <!-- Infos contact -->
      <div>
        <div class="card card-body" style="margin-bottom:20px">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(26,77,122,.08);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-phone"></i>
            </div>
            <div>
              <div style="font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px">Téléphone</div>
              <a href="tel:<?= preg_replace('/\s/','',$phone) ?>" style="font-size:16px;font-weight:700;color:var(--primary)"><?= htmlspecialchars($phone) ?></a>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(26,77,122,.08);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-envelope"></i>
            </div>
            <div>
              <div style="font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px">Email</div>
              <a href="mailto:<?= htmlspecialchars($email) ?>" style="font-size:14px;font-weight:600;color:var(--primary)"><?= htmlspecialchars($email) ?></a>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(26,77,122,.08);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <div style="font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px">Adresse</div>
              <div style="font-size:13px;color:var(--text-2)"><?= htmlspecialchars($address) ?></div>
            </div>
          </div>
        </div>
        <div class="card card-body" style="background:var(--primary);color:#fff">
          <div style="font-size:13px;font-weight:700;color:var(--accent);margin-bottom:8px">
            <i class="fas fa-clock" style="margin-right:6px"></i>Disponibilités
          </div>
          <div style="font-size:13px;opacity:.9;line-height:1.8">
            Lundi – Vendredi : 9h – 19h<br>
            Samedi : 9h – 17h<br>
            <span style="opacity:.7;font-size:12px">Dimanche et jours fériés : sur RDV</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>