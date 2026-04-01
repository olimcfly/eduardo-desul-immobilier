<?php
/**
 * BLOCK RENDERER: Form
 * Affiche un formulaire (contact ou estimation)
 * Les formulaires réels sont chargés depuis les modules dédiés
 */

if (!isset($blockData)) $blockData = [];

$formTitle = htmlspecialchars($blockData['form_title'] ?? 'Nous contacter');
$formDescription = htmlspecialchars($blockData['form_description'] ?? '');
$formType = $blockData['form_type'] ?? 'contact'; // 'contact' ou 'estimation'
?>

<section style="padding: 60px 24px; background: #f9f6f3;">
  <div style="max-width: 600px; margin: 0 auto;">
    <?php if ($formTitle): ?>
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1a4d7a; margin-bottom: 12px;">
      <?php echo $formTitle; ?>
    </h2>
    <?php endif; ?>

    <?php if ($formDescription): ?>
    <p style="text-align: center; color: #718096; margin-bottom: 30px;">
      <?php echo $formDescription; ?>
    </p>
    <?php endif; ?>

    <!-- Formulaire de contact générique -->
    <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
      <input type="text" name="name" placeholder="Votre nom" required style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px;">
      <input type="email" name="email" placeholder="Votre email" required style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px;">
      <input type="tel" name="phone" placeholder="Votre téléphone" style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px;">

      <?php if ($formType === 'estimation'): ?>
      <input type="text" name="property_type" placeholder="Type de bien (Maison, Appartement...)" style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px;">
      <input type="number" name="property_area" placeholder="Surface (m²)" style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px;">
      <?php endif; ?>

      <textarea name="message" placeholder="Votre message" rows="5" required style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>

      <button type="submit" style="background: #1a4d7a; color: #fff; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background 0.2s;">
        Envoyer
      </button>
    </form>

    <p style="text-align: center; color: #718096; font-size: 12px; margin-top: 20px;">
      ✅ Nous respectons votre vie privée. <a href="/politique-confidentialite" style="color: #1a4d7a; text-decoration: none;">Voir notre politique</a>.
    </p>
  </div>
</section>
