<?php
/**
 * BLOCK RENDERER: Map
 * Affiche localisation avec adresse, téléphone et email
 */

if (!isset($blockData)) $blockData = [];

$address = htmlspecialchars($blockData['address'] ?? '');
$phone = htmlspecialchars($blockData['phone'] ?? '');
$email = htmlspecialchars($blockData['email'] ?? '');
?>

<section style="padding: 80px 24px;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
      <!-- Contact info -->
      <div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a4d7a; margin-bottom: 30px;">
          Nous trouver
        </h2>

        <?php if ($address): ?>
        <div style="margin-bottom: 20px;">
          <h3 style="font-weight: 700; color: #1a4d7a; margin-bottom: 8px;">📍 Adresse</h3>
          <p style="color: #718096;">
            <?php echo $address; ?>
          </p>
        </div>
        <?php endif; ?>

        <?php if ($phone): ?>
        <div style="margin-bottom: 20px;">
          <h3 style="font-weight: 700; color: #1a4d7a; margin-bottom: 8px;">📞 Téléphone</h3>
          <a href="tel:<?php echo str_replace(' ', '', $phone); ?>" style="color: #1a4d7a; text-decoration: none; font-weight: 600;">
            <?php echo $phone; ?>
          </a>
        </div>
        <?php endif; ?>

        <?php if ($email): ?>
        <div style="margin-bottom: 20px;">
          <h3 style="font-weight: 700; color: #1a4d7a; margin-bottom: 8px;">✉️ Email</h3>
          <a href="mailto:<?php echo $email; ?>" style="color: #1a4d7a; text-decoration: none; font-weight: 600;">
            <?php echo $email; ?>
          </a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Map placeholder -->
      <div style="background: #e2d9ce; border-radius: 12px; height: 400px; display: flex; align-items: center; justify-content: center; text-align: center; color: #718096;">
        <div>
          <div style="font-size: 48px; margin-bottom: 12px;">🗺️</div>
          <p>Carte Google Maps intégrée<br><small>(Configuration nécessaire)</small></p>
        </div>
      </div>
    </div>

    <!-- Responsive mobile -->
    <style>
      @media (max-width: 768px) {
        .map-grid {
          grid-template-columns: 1fr !important;
        }
      }
    </style>
  </div>
</section>
