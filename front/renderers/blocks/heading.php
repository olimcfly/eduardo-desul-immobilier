<?php
/**
 * BLOCK RENDERER: Heading
 * Affiche un titre de section
 */

if (!isset($blockData)) $blockData = [];

$title = htmlspecialchars($blockData['title'] ?? '');
$subtitle = htmlspecialchars($blockData['subtitle'] ?? '');
?>

<div style="padding: 40px 24px; text-align: center;">
  <div style="max-width: 900px; margin: 0 auto;">
    <?php if ($title): ?>
    <h1 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a4d7a; margin-bottom: 12px;">
      <?php echo $title; ?>
    </h1>
    <?php endif; ?>

    <?php if ($subtitle): ?>
    <p style="font-size: 18px; color: #718096; margin: 0;">
      <?php echo $subtitle; ?>
    </p>
    <?php endif; ?>
  </div>
</div>
