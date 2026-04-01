<?php
/**
 * TEMPLATE RENDERER: CONTACT PAGE
 * Page de contact - Eduardo De Sul
 *
 * 5 Blocs :
 * 1. hero → Hero avec titre et sous-titre
 * 2. contact_info → Informations de contact
 * 3. contact_form → Formulaire de contact
 * 4. map → Localisation Google Maps
 * 5. social_proof → Avis clients
 */

// Vérifier que les blocs sont disponibles
if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Nous contacter');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.25), rgba(0,0,0,.25)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #722F37 0%, #4a1f26 100%);";
?>

<section style="<?php echo $heroBgStyle ?>min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px; color: #fff; position: relative;">
  <div style="max-width: 900px; text-align: center; z-index: 2;">
    <h1 style="font-size: clamp(32px, 6vw, 56px); font-weight: 700; line-height: 1.2; margin: 0 0 24px; font-family: 'Playfair Display', serif;">
      <?php echo $heroTitle; ?>
    </h1>

    <?php if ($heroSubtitle): ?>
    <p style="font-size: clamp(16px, 2vw, 18px); opacity: 0.95; margin: 0; line-height: 1.6;">
      <?php echo $heroSubtitle; ?>
    </p>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 2. CONTACT INFO
// ════════════════════════════════════════════════════════════

$infoData = $pageBlocks['contact_info']['data'] ?? [];
$phone = htmlspecialchars($infoData['phone'] ?? '+33 5 XX XX XX XX');
$email = htmlspecialchars($infoData['email'] ?? 'contact@example.com');
$address = htmlspecialchars($infoData['address'] ?? '');
$hours = htmlspecialchars($infoData['hours'] ?? '');
?>

<section style="padding: 80px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
      <!-- Téléphone -->
      <div style="text-align: center;">
        <div style="font-size: 48px; color: #1a4d7a; margin-bottom: 20px;">
          📞
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; font-family: 'Playfair Display', serif;">
          Téléphone
        </h3>
        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone); ?>" style="color: #722F37; text-decoration: none; font-size: 16px; font-weight: 600;">
          <?php echo $phone; ?>
        </a>
      </div>

      <!-- Email -->
      <div style="text-align: center;">
        <div style="font-size: 48px; color: #1a4d7a; margin-bottom: 20px;">
          ✉️
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; font-family: 'Playfair Display', serif;">
          Email
        </h3>
        <a href="mailto:<?php echo $email; ?>" style="color: #722F37; text-decoration: none; font-size: 16px; font-weight: 600; word-break: break-all;">
          <?php echo $email; ?>
        </a>
      </div>

      <!-- Adresse -->
      <?php if ($address): ?>
      <div style="text-align: center;">
        <div style="font-size: 48px; color: #1a4d7a; margin-bottom: 20px;">
          📍
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; font-family: 'Playfair Display', serif;">
          Localisation
        </h3>
        <p style="color: #666; font-size: 15px; margin: 0;">
          <?php echo $address; ?>
        </p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Horaires -->
    <?php if ($hours): ?>
    <div style="margin-top: 60px; background: #fff; padding: 40px; border-radius: 12px; border: 1px solid #e2d9ce; text-align: center;">
      <h3 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; font-family: 'Playfair Display', serif;">
        Horaires d'ouverture
      </h3>
      <p style="color: #666; font-size: 15px; line-height: 1.8; margin: 0; white-space: pre-wrap;">
        <?php echo $hours; ?>
      </p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. CONTACT FORM
// ════════════════════════════════════════════════════════════

$formData = $pageBlocks['contact_form']['data'] ?? [];
$formTitle = htmlspecialchars($formData['form_title'] ?? 'Envoyez-nous un message');
$formDesc = htmlspecialchars($formData['form_description'] ?? 'Nous vous répondrons dans les 24 heures');
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 700px; margin: 0 auto;">
    <div style="background: #f5f2ed; padding: 60px; border-radius: 16px; border: 1px solid #e2d9ce;">
      <?php if ($formTitle): ?>
      <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 700; color: #1a1a1a; margin: 0 0 12px;">
        <?php echo $formTitle; ?>
      </h2>
      <?php endif; ?>

      <?php if ($formDesc): ?>
      <p style="text-align: center; color: #666; font-size: 15px; margin: 0 0 40px;">
        <?php echo $formDesc; ?>
      </p>
      <?php endif; ?>

      <form method="POST" style="display: grid; gap: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <input type="text" name="name" placeholder="Votre nom" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
          <input type="email" name="email" placeholder="Votre email" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
        </div>

        <input type="tel" name="phone" placeholder="Votre téléphone" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">

        <select name="subject" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
          <option value="">Sujet de votre message</option>
          <option value="achat">Je souhaite acheter</option>
          <option value="vente">Je souhaite vendre</option>
          <option value="estimation">Je souhaite une estimation</option>
          <option value="autre">Autre</option>
        </select>

        <textarea name="message" placeholder="Votre message" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px; resize: vertical; min-height: 120px;"></textarea>

        <button type="submit" style="background: #722F37; color: #fff; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; transition: background 0.2s;">
          Envoyer mon message
        </button>

        <p style="text-align: center; color: #999; font-size: 13px; margin: 0;">
          ✓ Nous respectons votre vie privée
        </p>
      </form>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. MAP
// ════════════════════════════════════════════════════════════

$mapData = $pageBlocks['map']['data'] ?? [];
$mapAddress = htmlspecialchars($mapData['address'] ?? '');
$mapEmbed = $mapData['map_embed'] ?? '';
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <?php if ($mapAddress): ?>
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 700; color: #1a1a1a; margin-bottom: 40px;">
      Localisation
    </h2>
    <?php endif; ?>

    <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,.12); height: 500px;">
      <?php if ($mapEmbed): ?>
        <?php echo $mapEmbed; ?>
      <?php else: ?>
        <div style="background: #e2d9ce; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #999; font-size: 16px;">
          Carte Google Maps - Code iframe à configurer dans l'admin
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 5. SOCIAL PROOF
// ════════════════════════════════════════════════════════════

$proofData = $pageBlocks['social_proof']['data'] ?? [];
$stars = htmlspecialchars($proofData['stars'] ?? '4.8');
$count = htmlspecialchars($proofData['count'] ?? '150');
$proofCtaText = htmlspecialchars($proofData['cta_text'] ?? 'Voir nos avis');
$proofCtaUrl = htmlspecialchars($proofData['cta_url'] ?? '#');
?>

<section style="padding: 80px 24px; background: #fff; text-align: center;">
  <div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
      <div style="font-size: 48px; letter-spacing: 4px; color: #722F37;">
        ★★★★★
      </div>
    </div>

    <p style="font-family: 'Playfair Display', serif; font-size: 42px; color: #1a1a1a; margin: 0 0 8px;">
      <?php echo $stars; ?>/5
    </p>

    <p style="font-size: 18px; color: #666; margin: 0 0 24px;">
      Basé sur <?php echo $count; ?> avis Google
    </p>

    <?php if ($proofCtaUrl): ?>
    <a href="<?php echo $proofCtaUrl; ?>" style="display: inline-block; background: #722F37; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
      <?php echo $proofCtaText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>
