<?php
/**
 * TEMPLATE RENDERER: HOME PAGE
 * Page d'accueil haut de gamme - Eduardo De Sul
 *
 * 6 Blocs :
 * 1. hero → Hero principal avec 2 CTA
 * 2. services → 3 services (Acheter, Vendre, Estimer)
 * 3. advisor_intro → Présentation du conseiller
 * 4. social_proof → Avis Google
 * 5. sectors → Secteurs d'intervention
 * 6. cta_final → Appel à action final
 */

// Vérifier que les blocs sont disponibles
if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Votre conseiller immobilier à Bordeaux');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');
$ctaPrimaryText = htmlspecialchars($heroData['cta_primary_text'] ?? 'Chercher un bien');
$ctaPrimaryUrl = htmlspecialchars($heroData['cta_primary_url'] ?? '/acheter');
$ctaSecondaryText = htmlspecialchars($heroData['cta_secondary_text'] ?? 'Vendre mon bien');
$ctaSecondaryUrl = htmlspecialchars($heroData['cta_secondary_url'] ?? '/vendre');
$heroBadge = htmlspecialchars($heroData['badge'] ?? 'Depuis 15 ans');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #722F37 0%, #1a1a1a 100%);";
?>

<section style="<?php echo $heroBgStyle ?>min-height: 90vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px; color: #fff; position: relative;">
  <div style="max-width: 900px; text-align: center; z-index: 2;">
    <!-- Badge -->
    <?php if ($heroBadge): ?>
    <div style="display: inline-block; background: rgba(201, 168, 76, 0.2); border: 1px solid #C9A84C; padding: 8px 20px; border-radius: 30px; margin-bottom: 24px; font-size: 14px; color: #C9A84C;">
      ✓ <?php echo $heroBadge; ?>
    </div>
    <?php endif; ?>

    <!-- Titre -->
    <h1 style="font-size: clamp(32px, 6vw, 64px); font-weight: 700; line-height: 1.2; margin: 24px 0 16px; font-family: 'Playfair Display', serif;">
      <?php echo $heroTitle; ?>
    </h1>

    <!-- Sous-titre -->
    <?php if ($heroSubtitle): ?>
    <p style="font-size: clamp(16px, 2vw, 20px); opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $heroSubtitle; ?>
    </p>
    <?php endif; ?>

    <!-- CTA Buttons -->
    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
      <a href="<?php echo $ctaPrimaryUrl; ?>" style="background: #C9A84C; color: #000; padding: 14px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: background 0.2s;">
        <?php echo $ctaPrimaryText; ?>
      </a>
      <a href="<?php echo $ctaSecondaryUrl; ?>" style="background: transparent; border: 2px solid #C9A84C; color: #C9A84C; padding: 12px 38px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.2s;">
        <?php echo $ctaSecondaryText; ?>
      </a>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 2. SERVICES
// ════════════════════════════════════════════════════════════

$servicesData = $pageBlocks['services']['data'] ?? [];
$servicesHeadline = htmlspecialchars($servicesData['headline'] ?? 'Nos services');
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $servicesHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px;">
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <?php
          $icon = htmlspecialchars($servicesData["card_{$i}_icon"] ?? '');
          $title = htmlspecialchars($servicesData["card_{$i}_title"] ?? '');
          $description = htmlspecialchars($servicesData["card_{$i}_description"] ?? '');
          $link = htmlspecialchars($servicesData["card_{$i}_link"] ?? '#');
        ?>
        <a href="<?php echo $link; ?>" style="text-decoration: none; color: inherit;">
          <div style="background: #f5f2ed; padding: 40px; border-radius: 12px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid #e2d9ce;">
            <?php if ($icon): ?>
            <div style="font-size: 48px; margin-bottom: 20px;">
              <?php echo $icon; ?>
            </div>
            <?php endif; ?>

            <?php if ($title): ?>
            <h3 style="font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
              <?php echo $title; ?>
            </h3>
            <?php endif; ?>

            <?php if ($description): ?>
            <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
              <?php echo $description; ?>
            </p>
            <?php endif; ?>
          </div>
        </a>
      <?php endfor; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. ADVISOR INTRO
// ════════════════════════════════════════════════════════════

$advisorData = $pageBlocks['advisor_intro']['data'] ?? [];
$advisorPhoto = htmlspecialchars($advisorData['photo'] ?? '');
$advisorName = htmlspecialchars($advisorData['name'] ?? 'Eduardo De Sul');
$advisorTitle = htmlspecialchars($advisorData['title'] ?? 'Conseiller Immobilier');
$advisorBio = htmlspecialchars($advisorData['bio_short'] ?? '');
$advisorCtaText = htmlspecialchars($advisorData['cta_text'] ?? 'Me contacter');
$advisorCtaUrl = htmlspecialchars($advisorData['cta_url'] ?? '/contact');
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
      <!-- Photo -->
      <?php if ($advisorPhoto): ?>
      <div style="text-align: center;">
        <img src="<?php echo $advisorPhoto; ?>" alt="<?php echo $advisorName; ?>" style="max-width: 100%; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,.12);">
      </div>
      <?php endif; ?>

      <!-- Info -->
      <div>
        <p style="color: #C9A84C; font-weight: 700; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 1px;">
          Votre conseiller
        </p>

        <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin: 0 0 8px;">
          <?php echo $advisorName; ?>
        </h2>

        <?php if ($advisorTitle): ?>
        <p style="color: #666; font-size: 18px; margin: 0 0 24px;">
          <?php echo $advisorTitle; ?>
        </p>
        <?php endif; ?>

        <?php if ($advisorBio): ?>
        <p style="color: #333; font-size: 16px; line-height: 1.8; margin: 0 0 30px;">
          <?php echo $advisorBio; ?>
        </p>
        <?php endif; ?>

        <a href="<?php echo $advisorCtaUrl; ?>" style="display: inline-block; background: #722F37; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
          <?php echo $advisorCtaText; ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. SOCIAL PROOF
// ════════════════════════════════════════════════════════════

$proofData = $pageBlocks['social_proof']['data'] ?? [];
$stars = htmlspecialchars($proofData['stars'] ?? '4.8');
$count = htmlspecialchars($proofData['count'] ?? '150');
$proofCtaText = htmlspecialchars($proofData['cta_text'] ?? 'Voir nos avis');
$proofCtaUrl = htmlspecialchars($proofData['cta_url'] ?? '#');
?>

<section style="padding: 80px 24px; background: #1a1a1a; color: #fff; text-align: center;">
  <div style="max-width: 900px; margin: 0 auto;">
    <!-- Stars -->
    <div style="margin-bottom: 20px;">
      <div style="font-size: 48px; letter-spacing: 4px;">
        ★★★★★
      </div>
    </div>

    <!-- Rating -->
    <p style="font-family: 'Playfair Display', serif; font-size: 48px; margin: 0 0 8px;">
      <?php echo $stars; ?>/5
    </p>

    <!-- Count -->
    <p style="font-size: 18px; opacity: 0.8; margin: 0 0 24px;">
      Basé sur <?php echo $count; ?> avis Google
    </p>

    <!-- CTA -->
    <?php if ($proofCtaUrl): ?>
    <a href="<?php echo $proofCtaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #1a1a1a; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
      <?php echo $proofCtaText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 5. SECTORS
// ════════════════════════════════════════════════════════════

$sectorsData = $pageBlocks['sectors']['data'] ?? [];
$sectorsHeadline = htmlspecialchars($sectorsData['headline'] ?? 'Nos secteurs d\'intervention');
$sectorsItems = $sectorsData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $sectorsHeadline; ?>
    </h2>

    <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center;">
      <?php foreach ($sectorsItems as $sector): ?>
        <?php
          $sectorName = htmlspecialchars($sector['name'] ?? '');
          $sectorSlug = htmlspecialchars($sector['slug'] ?? '');
        ?>
        <?php if ($sectorName): ?>
        <a href="/<?php echo $sectorSlug; ?>" style="padding: 12px 24px; background: #f5f2ed; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
          <?php echo $sectorName; ?>
        </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 6. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Prêt à faire votre prochain pas ?');
$finalSubtext = htmlspecialchars($finalCtaData['subtext'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'Prendre rendez-vous');
$finalCtaUrl = htmlspecialchars($finalCtaData['cta_url'] ?? '/contact');
$finalReassurance = htmlspecialchars($finalCtaData['reassurance'] ?? 'Sans engagement');
?>

<section style="padding: 100px 24px; background: #722F37; color: #fff; text-align: center;">
  <div style="max-width: 800px; margin: 0 auto;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; margin: 0 0 20px;">
      <?php echo $finalHeadline; ?>
    </h2>

    <?php if ($finalSubtext): ?>
    <p style="font-size: 18px; opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $finalSubtext; ?>
    </p>
    <?php endif; ?>

    <a href="<?php echo $finalCtaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #1a1a1a; padding: 14px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; margin-bottom: 24px; transition: background 0.2s;">
      <?php echo $finalCtaText; ?>
    </a>

    <?php if ($finalReassurance): ?>
    <p style="font-size: 14px; opacity: 0.8; margin: 0;">
      ✓ <?php echo $finalReassurance; ?>
    </p>
    <?php endif; ?>
  </div>
</section>
