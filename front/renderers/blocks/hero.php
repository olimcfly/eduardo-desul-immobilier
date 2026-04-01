<?php
/**
 * BLOCK RENDERER: Hero
 * Affiche une section hero avec titre, sous-titre, image de fond et bouton
 */

if (!isset($blockData)) $blockData = [];

$title = htmlspecialchars($blockData['title'] ?? 'Titre principal');
$subtitle = htmlspecialchars($blockData['subtitle'] ?? '');
$bgImage = htmlspecialchars($blockData['background_image'] ?? '');
$bgColor = htmlspecialchars($blockData['background_color'] ?? '#1a4d7a');
$btnText = htmlspecialchars($blockData['button_text'] ?? '');
$btnUrl = htmlspecialchars($blockData['button_url'] ?? '#');

$bgStyle = $bgImage
    ? "background: linear-gradient(rgba(0,0,0,.4), rgba(0,0,0,.4)), url('$bgImage') center/cover no-repeat;"
    : "background: $bgColor;";
?>

<section style="<?php echo $bgStyle ?>min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 80px 24px;">
  <div style="max-width: 900px; color: #fff;">
    <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(32px, 5vw, 56px); font-weight: 700; line-height: 1.2; margin-bottom: 20px;">
      <?php echo $title; ?>
    </h1>
    <?php if ($subtitle): ?>
    <p style="font-size: clamp(16px, 2vw, 22px); opacity: .9; max-width: 700px; margin: 0 auto 30px; line-height: 1.6;">
      <?php echo $subtitle; ?>
    </p>
    <?php endif; ?>
    <?php if ($btnText && $btnUrl !== '#'): ?>
    <a href="<?php echo $btnUrl; ?>" style="display: inline-block; background: #d4a574; color: #fff; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 16px; text-decoration: none; transition: background 0.2s;">
      <?php echo $btnText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>
