<?php
/**
 * BLOCK RENDERER: Steps
 * Affiche un processus en étapes numérotées
 */

if (!isset($blockData)) $blockData = [];

$sectionTitle = htmlspecialchars($blockData['section_title'] ?? '');
$items = $blockData['items'] ?? [];
?>

<section style="padding: 80px 24px;">
  <div style="max-width: 1000px; margin: 0 auto;">
    <?php if ($sectionTitle): ?>
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1a4d7a; margin-bottom: 50px;">
      <?php echo $sectionTitle; ?>
    </h2>
    <?php endif; ?>

    <div style="position: relative;">
      <?php foreach ($items as $index => $item): ?>
        <?php
          $stepNum = $index + 1;
          $title = htmlspecialchars($item['title'] ?? '');
          $description = htmlspecialchars($item['description'] ?? '');
        ?>
        <div style="display: flex; gap: 30px; margin-bottom: 40px; position: relative;">
          <!-- Numéro étape -->
          <div style="flex-shrink: 0;">
            <div style="width: 60px; height: 60px; background: #1a4d7a; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
              <?php echo $stepNum; ?>
            </div>
          </div>

          <!-- Contenu étape -->
          <div style="flex: 1; padding-top: 8px;">
            <?php if ($title): ?>
            <h3 style="font-size: 20px; font-weight: 700; color: #1a4d7a; margin-bottom: 8px;">
              <?php echo $title; ?>
            </h3>
            <?php endif; ?>

            <?php if ($description): ?>
            <p style="color: #718096; line-height: 1.6;">
              <?php echo $description; ?>
            </p>
            <?php endif; ?>
          </div>

          <!-- Ligne de connexion (sauf dernière) -->
          <?php if ($index < count($items) - 1): ?>
          <div style="position: absolute; left: 29px; top: 60px; width: 2px; height: 40px; background: #d4a574;"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
