<?php
/**
 * BLOCK RENDERER: CTA (Call To Action)
 * Section d'appel à l'action avec titre, description et bouton
 */

if (!isset($blockData)) $blockData = [];

$headline = htmlspecialchars($blockData['headline'] ?? $blockData['title'] ?? '');
$description = htmlspecialchars($blockData['description'] ?? '');
$btnText = htmlspecialchars($blockData['button_text'] ?? 'En savoir plus');
$btnUrl = htmlspecialchars($blockData['button_url'] ?? '#');
$bgColor = htmlspecialchars($blockData['background_color'] ?? '#f8f5f0');
?>

<section style="background: <?php echo $bgColor; ?>; padding: 60px 24px; text-align: center;">
  <div style="max-width: 700px; margin: 0 auto;">
    <?php if ($headline): ?>
    <h2 style="font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1a4d7a; margin-bottom: 16px;">
      <?php echo $headline; ?>
    </h2>
    <?php endif; ?>

    <?php if ($description): ?>
    <p style="color: #718096; font-size: 18px; margin-bottom: 30px; line-height: 1.6;">
      <?php echo $description; ?>
    </p>
    <?php endif; ?>

    <?php if ($btnText && $btnUrl !== '#'): ?>
    <a href="<?php echo $btnUrl; ?>" style="display: inline-block; background: #1a4d7a; color: #fff; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 16px; text-decoration: none; transition: background 0.2s;">
      <?php echo $btnText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>
