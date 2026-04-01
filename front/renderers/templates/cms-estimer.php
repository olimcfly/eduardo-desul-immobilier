<?php
/**
 * TEMPLATE RENDERER: ESTIMER PAGE
 * Page d'estimation gratuite de bien immobilier - Eduardo De Sul
 *
 * 6 Blocs :
 * 1. hero → Hero avec titre et sous-titre
 * 2. form_estimation → Formulaire d'estimation du bien
 * 3. method → Explication de la méthode d'estimation
 * 4. why_free → Pourquoi l'estimation est gratuite
 * 5. social_proof → Avis clients
 * 6. cta_final → Appel à action final
 */

// Vérifier que les blocs sont disponibles
if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Estimez votre bien gratuitement');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.25), rgba(0,0,0,.25)), url('$heroBg') center/cover no-repeat;"
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
// 2. FORM ESTIMATION
// ════════════════════════════════════════════════════════════

$formData = $pageBlocks['form_estimation']['data'] ?? [];
$formTitle = htmlspecialchars($formData['form_title'] ?? 'Estimation gratuite');
$formDesc = htmlspecialchars($formData['form_description'] ?? 'Remplissez le formulaire pour obtenir une estimation personnalisée');
?>

<section style="padding: 80px 24px; background: #fff;">
  <div style="max-width: 800px; margin: 0 auto;">
    <div style="background: #f5f2ed; padding: 60px; border-radius: 16px; border: 1px solid #e2d9ce;">
      <?php if ($formTitle): ?>
      <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 700; color: #1a1a1a; margin: 0 0 12px;">
        <?php echo $formTitle; ?>
      </h2>
      <?php endif; ?>

      <?php if ($formDesc): ?>
      <p style="text-align: center; color: #666; font-size: 16px; margin: 0 0 40px; line-height: 1.6;">
        <?php echo $formDesc; ?>
      </p>
      <?php endif; ?>

      <!-- Formulaire d'estimation -->
      <form method="POST" style="display: grid; gap: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <input type="text" name="property_type" placeholder="Type de bien (Maison, Appart...)" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
          <input type="text" name="location" placeholder="Localisation" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <input type="number" name="surface" placeholder="Surface (m²)" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
          <input type="number" name="rooms" placeholder="Nombre de pièces" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
        </div>

        <textarea name="description" placeholder="Description du bien (état, aménagements...)" style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px; resize: vertical; min-height: 100px;"></textarea>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <input type="text" name="name" placeholder="Votre nom" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
          <input type="email" name="email" placeholder="Votre email" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">
        </div>

        <input type="tel" name="phone" placeholder="Votre téléphone" required style="padding: 12px; border: 1px solid #d4c5b0; border-radius: 8px; font-size: 15px;">

        <button type="submit" style="background: #1a4d7a; color: #fff; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; transition: background 0.2s;">
          Obtenir mon estimation gratuite
        </button>

        <p style="text-align: center; color: #999; font-size: 13px; margin: 0;">
          ✓ Estimation gratuite et sans engagement
        </p>
      </form>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. METHOD
// ════════════════════════════════════════════════════════════

$methodData = $pageBlocks['method']['data'] ?? [];
$methodHeadline = htmlspecialchars($methodData['headline'] ?? 'Comment ça marche ?');
$methodItems = $methodData['items'] ?? [];
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $methodHeadline; ?>
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px;">
      <?php $stepNum = 1; foreach ($methodItems as $item): ?>
        <?php
          $icon = htmlspecialchars($item['icon'] ?? '');
          $title = htmlspecialchars($item['title'] ?? '');
          $description = htmlspecialchars($item['description'] ?? '');
        ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e2d9ce; position: relative;">
          <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); background: #1a4d7a; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
            <?php echo $stepNum; ?>
          </div>

          <?php if ($icon): ?>
          <div style="font-size: 48px; margin: 20px 0;">
            <?php echo $icon; ?>
          </div>
          <?php endif; ?>

          <?php if ($title): ?>
          <h3 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; font-family: 'Playfair Display', serif;">
            <?php echo $title; ?>
          </h3>
          <?php endif; ?>

          <?php if ($description): ?>
          <p style="color: #666; font-size: 15px; line-height: 1.6; margin: 0;">
            <?php echo $description; ?>
          </p>
          <?php endif; ?>
        </div>
        <?php $stepNum++; endforeach; ?>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. WHY FREE
// ════════════════════════════════════════════════════════════

$freeData = $pageBlocks['why_free']['data'] ?? [];
$freeHeadline = htmlspecialchars($freeData['headline'] ?? 'Pourquoi c\'est gratuit ?');
$freeDesc = htmlspecialchars($freeData['description'] ?? '');
$freeIcon = htmlspecialchars($freeData['icon'] ?? '✓');
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 900px; margin: 0 auto;">
    <div style="background: linear-gradient(135deg, #1a4d7a 0%, #0d2a47 100%); color: #fff; padding: 80px 40px; border-radius: 16px; text-align: center;">
      <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; margin: 0 0 20px;">
        <?php echo $freeHeadline; ?>
      </h2>

      <?php if ($freeDesc): ?>
      <p style="font-size: 18px; line-height: 1.8; margin: 0; opacity: 0.95;">
        <?php echo $freeDesc; ?>
      </p>
      <?php endif; ?>

      <div style="font-size: 80px; margin-top: 40px; opacity: 0.3;">
        <?php echo $freeIcon; ?>
      </div>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 5. SOCIAL PROOF
// ════════════════════════════════════════════════════════════

$proofData = $pageBlocks['social_proof']['data'] ?? [];
$stars = htmlspecialchars($proofData['stars'] ?? '4.8');
$count = htmlspecialchars($proofData['count'] ?? '150');
$proofCtaText = htmlspecialchars($proofData['cta_text'] ?? 'Voir nos avis');
$proofCtaUrl = htmlspecialchars($proofData['cta_url'] ?? '#');
?>

<section style="padding: 80px 24px; background: #f5f2ed; text-align: center;">
  <div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
      <div style="font-size: 48px; letter-spacing: 4px; color: #1a4d7a;">
        ★★★★★
      </div>
    </div>

    <p style="font-family: 'Playfair Display', serif; font-size: 42px; color: #1a1a1a; margin: 0 0 8px;">
      <?php echo $stars; ?>/5
    </p>

    <p style="font-size: 18px; color: #666; margin: 0 0 24px;">
      Basé sur <?php echo $count; ?> avis Google
    </p>

    <?php if ($proofCtaUrl): ?>
    <a href="<?php echo $proofCtaUrl; ?>" style="display: inline-block; background: #1a4d7a; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s;">
      <?php echo $proofCtaText; ?>
    </a>
    <?php endif; ?>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 6. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Estimez votre bien dès aujourd\'hui');
$finalSubtext = htmlspecialchars($finalCtaData['subtext'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'Commencer l\'estimation');
$finalCtaUrl = htmlspecialchars($finalCtaData['cta_url'] ?? '#form_estimation');
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

    <a href="<?php echo $finalCtaUrl; ?>" style="display: inline-block; background: #C9A84C; color: #000; padding: 14px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; margin-bottom: 24px; transition: background 0.2s; font-size: 16px;">
      <?php echo $finalCtaText; ?>
    </a>

    <p style="font-size: 14px; opacity: 0.8; margin: 0;">
      ✓ 100% gratuit et sans engagement
    </p>
  </div>
</section>
