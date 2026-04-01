<?php
/**
 * TEMPLATE RENDERER: FINANCEMENT PAGE
 * Page d'aide au financement immobilier - Eduardo De Sul
 *
 * 6 Blocs :
 * 1. hero → Hero avec CTA
 * 2. intro → Introduction au financement
 * 3. steps → Processus de financement
 * 4. guide → Ressources et guides
 * 5. partner → Présentation partenaire bancaire
 * 6. cta_final → Appel à action final
 */

if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Financer votre projet immobilier');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');
$ctaText = htmlspecialchars($heroData['cta_text'] ?? 'Obtenir une simulation');
$ctaUrl = htmlspecialchars($heroData['cta_url'] ?? '/contact');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.3), rgba(0,0,0,.3)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #1a4d7a 0%, #0d2a47 100%);";
?>

<section style="<?php echo $heroBgStyle ?>min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px; color: #fff; position: relative;">
  <div style="max-width: 900px; text-align: center; z-index: 2;">
    <h1 style="font-size: clamp(32px, 6vw, 56px); font-weight: 700; line-height: 1.2; margin: 0 0 24px; font-family: 'Playfair Display', serif;">
      <?php echo $heroTitle; ?>
    </h1>
    <?php if ($heroSubtitle): ?>
    <p style="font-size: clamp(16px, 2vw, 18px); opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $heroSubtitle; ?>
    </p>
    <?php endif; ?>

    <a href="<?php echo $ctaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #000; padding: 14px 40px; border-radius: 8px; font-weight: 700; text-decoration: none;">
      <?php echo $ctaText; ?>
    </a>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 2. INTRO
// ════════════════════════════════════════════════════════════

$introData = $pageBlocks['intro']['data'] ?? [];
$introHeadline = htmlspecialchars($introData['headline'] ?? 'Comment ça marche');
$introDesc = htmlspecialchars($introData['description'] ?? '');
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 900px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 40px;">
      <?php echo $introHeadline; ?>
    </h2>

    <?php if ($introDesc): ?>
    <p style="font-size: 18px; line-height: 1.8; color: #333; text-align: center;">
      <?php echo $introDesc; ?>
    </p>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. STEPS
// ════════════════════════════════════════════════════════════

$stepsData = $pageBlocks['steps']['data'] ?? [];
$stepsHeadline = htmlspecialchars($stepsData['headline'] ?? 'Processus de financement');
$stepsItems = $stepsData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $stepsHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 30px;">
      <?php $stepNum = 1; foreach ($stepsItems as $step): ?>
        <?php
          $title = htmlspecialchars($step['title'] ?? '');
          $desc = htmlspecialchars($step['description'] ?? '');
        ?>
        <div style="text-align: center;">
          <div style="background: #1a4d7a; color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 20px;">
            <?php echo $stepNum; ?>
          </div>
          <?php if ($title): ?>
          <h3 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
            <?php echo $title; ?>
          </h3>
          <?php endif; ?>
          <?php if ($desc): ?>
          <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
            <?php echo $desc; ?>
          </p>
          <?php endif; ?>
        </div>
        <?php $stepNum++; endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. GUIDE
// ════════════════════════════════════════════════════════════

$guideData = $pageBlocks['guide']['data'] ?? [];
$guideHeadline = htmlspecialchars($guideData['headline'] ?? 'Ressources');
$guideItems = $guideData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $guideHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
      <?php foreach ($guideItems as $item): ?>
        <?php
          $icon = htmlspecialchars($item['icon'] ?? '');
          $title = htmlspecialchars($item['title'] ?? '');
          $description = htmlspecialchars($item['description'] ?? '');
        ?>
        <div style="background: #f5f2ed; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e2d9ce;">
          <?php if ($icon): ?>
          <div style="font-size: 48px; margin-bottom: 20px;">
            <?php echo $icon; ?>
          </div>
          <?php endif; ?>

          <?php if ($title): ?>
          <h3 style="font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
            <?php echo $title; ?>
          </h3>
          <?php endif; ?>

          <?php if ($description): ?>
          <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
            <?php echo $description; ?>
          </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 5. PARTNER
// ════════════════════════════════════════════════════════════

$partnerData = $pageBlocks['partner']['data'] ?? [];
$partnerHeadline = htmlspecialchars($partnerData['headline'] ?? 'Nos partenaires bancaires');
$partnerDesc = htmlspecialchars($partnerData['description'] ?? '');
$partnerLogo = htmlspecialchars($partnerData['logo'] ?? '');
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 900px; margin: 0 auto; text-align: center;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 30px;">
      <?php echo $partnerHeadline; ?>
    </h2>

    <?php if ($partnerDesc): ?>
    <p style="font-size: 16px; line-height: 1.8; color: #666; margin-bottom: 40px;">
      <?php echo $partnerDesc; ?>
    </p>
    <?php endif; ?>

    <?php if ($partnerLogo): ?>
    <div style="background: #fff; padding: 40px; border-radius: 12px; border: 1px solid #e2d9ce;">
      <img src="<?php echo $partnerLogo; ?>" alt="Partenaire bancaire" style="max-width: 300px; height: auto;">
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 6. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Simulez votre financement');
$finalSubtext = htmlspecialchars($finalCtaData['subtext'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'Demander une simulation');
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
      ✓ Gratuit et sans engagement
    </p>
  </div>
</section>
