<?php
/**
 * TEMPLATE RENDERER: SECTEURS PAGE
 * Page de présentation des secteurs d'intervention - Eduardo De Sul
 *
 * 4 Blocs :
 * 1. hero → Hero avec titre et sous-titre
 * 2. sectors_grid → Grille des secteurs avec descriptions
 * 3. advisor → Présentation du conseiller
 * 4. cta_final → Appel à action final
 */

if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Nos secteurs d\'intervention');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.3), rgba(0,0,0,.3)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #1a4d7a 0%, #0d2a47 100%);";
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
// 2. SECTORS GRID
// ════════════════════════════════════════════════════════════

$sectorsData = $pageBlocks['sectors_grid']['data'] ?? [];
$sectorsHeadline = htmlspecialchars($sectorsData['headline'] ?? 'Nos zones d\'expertise');
$sectorItems = $sectorsData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $sectorsHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px;">
      <?php foreach ($sectorItems as $sector): ?>
        <?php
          $icon = htmlspecialchars($sector['icon'] ?? '');
          $name = htmlspecialchars($sector['name'] ?? '');
          $slug = htmlspecialchars($sector['slug'] ?? '');
          $description = htmlspecialchars($sector['description'] ?? '');
        ?>
        <a href="/<?php echo $slug; ?>" style="text-decoration: none; color: inherit;">
          <div style="background: #f5f2ed; padding: 40px; border-radius: 12px; border: 1px solid #e2d9ce; transition: all 0.3s; cursor: pointer;">
            <?php if ($icon): ?>
            <div style="font-size: 48px; margin-bottom: 20px;">
              <?php echo $icon; ?>
            </div>
            <?php endif; ?>

            <?php if ($name): ?>
            <h3 style="font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
              <?php echo $name; ?>
            </h3>
            <?php endif; ?>

            <?php if ($description): ?>
            <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
              <?php echo $description; ?>
            </p>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. ADVISOR
// ════════════════════════════════════════════════════════════

$advisorData = $pageBlocks['advisor']['data'] ?? [];
$advisorPhoto = htmlspecialchars($advisorData['photo'] ?? '');
$advisorName = htmlspecialchars($advisorData['name'] ?? 'Eduardo De Sul');
$advisorTitle = htmlspecialchars($advisorData['title'] ?? 'Expert Immobilier');
$advisorIntro = htmlspecialchars($advisorData['intro'] ?? '');
$advisorCtaText = htmlspecialchars($advisorData['cta_text'] ?? 'Me contacter');
$advisorCtaUrl = htmlspecialchars($advisorData['cta_url'] ?? '/contact');
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
      <?php if ($advisorPhoto): ?>
      <div style="text-align: center;">
        <img src="<?php echo $advisorPhoto; ?>" alt="<?php echo $advisorName; ?>" style="max-width: 100%; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,.12);">
      </div>
      <?php endif; ?>

      <div>
        <p style="color: #C9A84C; font-weight: 700; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 1px;">
          Votre expert régional
        </p>

        <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px;">
          <?php echo $advisorName; ?>
        </h2>

        <?php if ($advisorTitle): ?>
        <p style="color: #666; font-size: 18px; margin: 0 0 24px;">
          <?php echo $advisorTitle; ?>
        </p>
        <?php endif; ?>

        <?php if ($advisorIntro): ?>
        <p style="color: #333; font-size: 16px; line-height: 1.8; margin: 0 0 30px;">
          <?php echo $advisorIntro; ?>
        </p>
        <?php endif; ?>

        <a href="<?php echo $advisorCtaUrl; ?>" style="display: inline-block; background: #1a4d7a; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
          <?php echo $advisorCtaText; ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Explorez nos secteurs');
$finalSubtext = htmlspecialchars($finalCtaData['subtext'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'Prendre rendez-vous');
$finalCtaUrl = htmlspecialchars($finalCtaData['cta_url'] ?? '/contact');
?>

<section style="padding: 100px 24px; background: #1a4d7a; color: #fff; text-align: center;">
  <div style="max-width: 800px; margin: 0 auto;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; margin: 0 0 20px;">
      <?php echo $finalHeadline; ?>
    </h2>

    <?php if ($finalSubtext): ?>
    <p style="font-size: 18px; opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $finalSubtext; ?>
    </p>
    <?php endif; ?>

    <a href="<?php echo $finalCtaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #000; padding: 14px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; margin-bottom: 24px; transition: background 0.2s;">
      <?php echo $finalCtaText; ?>
    </a>

    <p style="font-size: 14px; opacity: 0.8; margin: 0;">
      ✓ Expert local dans chaque secteur
    </p>
  </div>
</section>
