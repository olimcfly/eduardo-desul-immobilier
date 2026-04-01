<?php
/**
 * BLOCK RENDERER: RichText
 * Affiche du contenu HTML/texte enrichi (pour pages légales, etc.)
 */

if (!isset($blockData)) $blockData = [];

$content = $blockData['html_content'] ?? '';
$maxWidth = htmlspecialchars($blockData['max_width'] ?? '900px');
$padding = htmlspecialchars($blockData['padding'] ?? '40px 24px');
$bgColor = htmlspecialchars($blockData['bg_color'] ?? '');

$bgStyle = $bgColor ? "background: $bgColor;" : '';
?>

<div style="<?php echo $bgStyle ?>padding: <?php echo $padding; ?>;">
  <div style="max-width: <?php echo $maxWidth; ?>; margin: 0 auto; color: #2d3748; line-height: 1.8;">
    <?php echo $content; ?>
  </div>
</div>
