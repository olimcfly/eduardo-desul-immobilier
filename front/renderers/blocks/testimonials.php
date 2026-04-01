<?php
/**
 * BLOCK RENDERER: Testimonials
 * Affiche des témoignages clients avec photo, nom et texte
 */

if (!isset($blockData)) $blockData = [];

$sectionTitle = htmlspecialchars($blockData['section_title'] ?? '');
$items = $blockData['items'] ?? [];
?>

<section style="padding: 80px 24px; background: #f9f6f3;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <?php if ($sectionTitle): ?>
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1a4d7a; margin-bottom: 50px;">
      <?php echo $sectionTitle; ?>
    </h2>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
      <?php foreach ($items as $item): ?>
        <?php
          $name = htmlspecialchars($item['name'] ?? '');
          $role = htmlspecialchars($item['role'] ?? '');
          $text = htmlspecialchars($item['text'] ?? '');
          $image = htmlspecialchars($item['image'] ?? '');
        ?>
        <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07); text-align: center;">
          <?php if ($image): ?>
          <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
          <?php endif; ?>

          <?php if ($text): ?>
          <p style="color: #718096; font-size: 15px; line-height: 1.8; margin-bottom: 20px; font-style: italic;">
            "<?php echo $text; ?>"
          </p>
          <?php endif; ?>

          <?php if ($name): ?>
          <p style="font-weight: 700; color: #1a4d7a; margin: 0;">
            <?php echo $name; ?>
          </p>
          <?php endif; ?>

          <?php if ($role): ?>
          <p style="color: #d4a574; font-size: 14px; margin: 4px 0 0;">
            <?php echo $role; ?>
          </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
