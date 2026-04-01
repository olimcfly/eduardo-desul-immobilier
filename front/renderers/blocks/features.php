<?php
/**
 * BLOCK RENDERER: Features/Services
 * Affiche une grille de services/bénéfices avec icône, titre et description
 */

if (!isset($blockData)) $blockData = [];

$sectionTitle = htmlspecialchars($blockData['section_title'] ?? '');
$sectionSubtitle = htmlspecialchars($blockData['section_subtitle'] ?? '');
$items = $blockData['items'] ?? [];
$bgColor = htmlspecialchars($blockData['bg_color'] ?? '');

$bgStyle = $bgColor ? "background: $bgColor;" : '';
?>

<section style="<?php echo $bgStyle ?>padding: 80px 24px;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <?php if ($sectionTitle): ?>
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1a4d7a; margin-bottom: 16px;">
      <?php echo $sectionTitle; ?>
    </h2>
    <?php endif; ?>

    <?php if ($sectionSubtitle): ?>
    <p style="text-align: center; color: #718096; font-size: 18px; margin-bottom: 50px; max-width: 700px; margin-left: auto; margin-right: auto;">
      <?php echo $sectionSubtitle; ?>
    </p>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px;">
      <?php foreach ($items as $item): ?>
        <?php
          $icon = htmlspecialchars($item['icon'] ?? '');
          $itemTitle = htmlspecialchars($item['title'] ?? $item['label'] ?? '');
          $itemText = $item['description'] ?? $item['text'] ?? '';
        ?>
        <div style="text-align: center;">
          <?php if ($icon): ?>
          <div style="font-size: 48px; margin-bottom: 20px;">
            <?php echo $icon; ?>
          </div>
          <?php endif; ?>

          <?php if ($itemTitle): ?>
          <h3 style="font-size: 20px; font-weight: 700; color: #1a4d7a; margin-bottom: 12px;">
            <?php echo $itemTitle; ?>
          </h3>
          <?php endif; ?>

          <?php if ($itemText): ?>
          <p style="color: #718096; font-size: 15px; line-height: 1.6;">
            <?php echo htmlspecialchars($itemText); ?>
          </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
