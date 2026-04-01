<?php
/**
 * TEMPLATE RENDERER: VENDRE PAGE
 * Page de vente de propriétés - Eduardo De Sul
 *
 * 7 Blocs :
 * 1. hero → Hero avec titre, sous-titre + 2 CTA
 * 2. pain_points → Défis du vendeur avec solutions
 * 3. advisor → Pourquoi nous choisir + avantages
 * 4. steps → Processus de vente en étapes
 * 5. guide → Ressources pour le vendeur
 * 6. social_proof → Avis et témoignages
 * 7. cta_final → Appel à action final
 */

// Vérifier que les blocs sont disponibles
if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Vendez votre bien à meilleur prix');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');
$ctaPrimaryText = htmlspecialchars($heroData['cta_primary_text'] ?? 'Estimer mon bien');
$ctaPrimaryUrl = htmlspecialchars($heroData['cta_primary_url'] ?? '/estimer');
$ctaSecondaryText = htmlspecialchars($heroData['cta_secondary_text'] ?? 'Parler avec un expert');
$ctaSecondaryUrl = htmlspecialchars($heroData['cta_secondary_url'] ?? '/contact');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.3), rgba(0,0,0,.3)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #722F37 0%, #4a1f26 100%);";
?>

<section style="<?php echo $heroBgStyle ?>min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px; color: #fff; position: relative;">
  <div style="max-width: 900px; text-align: center; z-index: 2;">
    <h1 style="font-size: clamp(32px, 6vw, 64px); font-weight: 700; line-height: 1.2; margin: 0 0 24px; font-family: 'Playfair Display', serif;">
      <?php echo $heroTitle; ?>
    </h1>

    <?php if ($heroSubtitle): ?>
    <p style="font-size: clamp(16px, 2vw, 20px); opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $heroSubtitle; ?>
    </p>
    <?php endif; ?>

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
// 2. PAIN POINTS
// ════════════════════════════════════════════════════════════

$painData = $pageBlocks['pain_points']['data'] ?? [];
$painHeadline = htmlspecialchars($painData['headline'] ?? 'Les défis du vendeur');
$painItems = $painData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $painHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
      <?php foreach ($painItems as $item): ?>
        <?php
          $icon = htmlspecialchars($item['icon'] ?? '');
          $title = htmlspecialchars($item['title'] ?? '');
          $solution = htmlspecialchars($item['solution'] ?? '');
        ?>
        <div style="background: #f5f2ed; padding: 40px; border-radius: 12px; border: 1px solid #e2d9ce;">
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

          <?php if ($solution): ?>
          <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
            <?php echo $solution; ?>
          </p>
          <?php endif; ?>
        </div>
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
$advisorBenefits = $advisorData['benefits'] ?? [];
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
          Votre expert
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

        <?php if (!empty($advisorBenefits)): ?>
        <ul style="list-style: none; padding: 0; margin: 0 0 30px;">
          <?php foreach ($advisorBenefits as $benefit): ?>
            <?php
              $benefitIcon = htmlspecialchars($benefit['icon'] ?? '✓');
              $benefitText = htmlspecialchars($benefit['text'] ?? '');
            ?>
            <?php if ($benefitText): ?>
            <li style="display: flex; align-items: center; margin-bottom: 12px; font-size: 15px; color: #333;">
              <span style="font-size: 20px; margin-right: 12px; color: #C9A84C;">
                <?php echo $benefitIcon; ?>
              </span>
              <?php echo $benefitText; ?>
            </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
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
// 4. STEPS
// ════════════════════════════════════════════════════════════

$stepsData = $pageBlocks['steps']['data'] ?? [];
$stepsHeadline = htmlspecialchars($stepsData['headline'] ?? 'Processus de vente');
$stepsItems = $stepsData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $stepsHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 30px;">
      <?php $stepCount = 1; foreach ($stepsItems as $step): ?>
        <?php
          $stepTitle = htmlspecialchars($step['title'] ?? '');
          $stepDesc = htmlspecialchars($step['description'] ?? '');
        ?>
        <div style="text-align: center;">
          <div style="background: #722F37; color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 20px;">
            <?php echo $stepCount; ?>
          </div>
          <?php if ($stepTitle): ?>
          <h3 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
            <?php echo $stepTitle; ?>
          </h3>
          <?php endif; ?>
          <?php if ($stepDesc): ?>
          <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
            <?php echo $stepDesc; ?>
          </p>
          <?php endif; ?>
        </div>
        <?php $stepCount++; endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 5. GUIDE
// ════════════════════════════════════════════════════════════

$guideData = $pageBlocks['guide']['data'] ?? [];
$guideHeadline = htmlspecialchars($guideData['headline'] ?? 'Ressources du vendeur');
$guideItems = $guideData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
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
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e2d9ce;">
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
// 6. SOCIAL PROOF
// ════════════════════════════════════════════════════════════

$proofData = $pageBlocks['social_proof']['data'] ?? [];
$stars = htmlspecialchars($proofData['stars'] ?? '4.8');
$count = htmlspecialchars($proofData['count'] ?? '150');
$proofCtaText = htmlspecialchars($proofData['cta_text'] ?? 'Voir nos avis');
$proofCtaUrl = htmlspecialchars($proofData['cta_url'] ?? '#');
?>

<section style="padding: 80px 24px; background: #1a1a1a; color: #fff; text-align: center;">
  <div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
      <div style="font-size: 48px; letter-spacing: 4px;">
        ★★★★★
      </div>
    </div>

    <p style="font-family: 'Playfair Display', serif; font-size: 48px; margin: 0 0 8px;">
      <?php echo $stars; ?>/5
    </p>

    <p style="font-size: 18px; opacity: 0.8; margin: 0 0 24px;">
      Basé sur <?php echo $count; ?> avis Google
    </p>

    <?php if ($proofCtaUrl): ?>
    <a href="<?php echo $proofCtaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #1a1a1a; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
      <?php echo $proofCtaText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 7. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Commencez dès aujourd\'hui');
$finalSubtext = htmlspecialchars($finalCtaData['subtext'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'Estimer mon bien');
$finalCtaUrl = htmlspecialchars($finalCtaData['cta_url'] ?? '/estimer');
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

    <p style="font-size: 14px; opacity: 0.8; margin: 0;">
      ✓ Estimation gratuite et sans engagement
    </p>
  </div>
</section>
