<?php
/**
 * TEMPLATE RENDERER: BLOG PAGE
 * Page d'archive et liste du blog - Eduardo De Sul
 *
 * 4 Blocs :
 * 1. hero → Hero avec titre
 * 2. posts → Liste des articles (dynamique)
 * 3. categories → Catégories de blog
 * 4. cta_final → Appel à action final
 */

if (!isset($pageBlocks)) return;

// ════════════════════════════════════════════════════════════
// 1. HERO
// ════════════════════════════════════════════════════════════

$heroData = $pageBlocks['hero']['data'] ?? [];
$heroTitle = htmlspecialchars($heroData['title'] ?? 'Notre blog immobilier');
$heroSubtitle = htmlspecialchars($heroData['subtitle'] ?? '');
$heroBg = htmlspecialchars($heroData['background_image'] ?? '');

$heroBgStyle = $heroBg
    ? "background: linear-gradient(rgba(0,0,0,.3), rgba(0,0,0,.3)), url('$heroBg') center/cover no-repeat;"
    : "background: linear-gradient(135deg, #722F37 0%, #4a1f26 100%);";
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
// 2. POSTS LIST
// ════════════════════════════════════════════════════════════

$postsData = $pageBlocks['posts']['data'] ?? [];
$postsHeadline = htmlspecialchars($postsData['headline'] ?? 'Derniers articles');
$postsPerPage = $postsData['posts_per_page'] ?? 6;
?>

<section style="padding: 100px 24px; background: #fff;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $postsHeadline; ?>
    </h2>

    <!-- Placeholder pour module Blog dynamique -->
    <div style="background: #f5f2ed; padding: 80px 24px; border-radius: 12px; text-align: center; border: 2px dashed #d4c5b0;">
      <p style="color: #999; font-size: 16px; margin: 0;">
        Les articles du blog s'affichent ici via le module Blog (<?php echo $postsPerPage; ?> articles par page)
      </p>
    </div>

    <!-- Pagination placeholder -->
    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 60px;">
      <button style="padding: 8px 12px; border: 1px solid #d4c5b0; background: #fff; border-radius: 4px; cursor: pointer;">← Précédent</button>
      <button style="padding: 8px 12px; background: #1a4d7a; color: #fff; border: none; border-radius: 4px;">1</button>
      <button style="padding: 8px 12px; border: 1px solid #d4c5b0; background: #fff; border-radius: 4px; cursor: pointer;">2</button>
      <button style="padding: 8px 12px; border: 1px solid #d4c5b0; background: #fff; border-radius: 4px; cursor: pointer;">3</button>
      <button style="padding: 8px 12px; border: 1px solid #d4c5b0; background: #fff; border-radius: 4px; cursor: pointer;">Suivant →</button>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 3. CATEGORIES
// ════════════════════════════════════════════════════════════

$categoriesData = $pageBlocks['categories']['data'] ?? [];
$categoriesHeadline = htmlspecialchars($categoriesData['headline'] ?? 'Catégories');
$showCount = $categoriesData['show_count'] ?? true;
?>

<section style="padding: 100px 24px; background: #f5f2ed;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a1a1a; margin-bottom: 60px;">
      <?php echo $categoriesHeadline; ?>
    </h2>

    <!-- Placeholder pour catégories dynamiques -->
    <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center;">
      <a href="#" style="padding: 12px 24px; background: #fff; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
        Tous les articles <?php if ($showCount): ?>(24)<?php endif; ?>
      </a>
      <a href="#" style="padding: 12px 24px; background: #fff; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
        Conseils d'achat <?php if ($showCount): ?>(8)<?php endif; ?>
      </a>
      <a href="#" style="padding: 12px 24px; background: #fff; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
        Guides de vente <?php if ($showCount): ?>(7)<?php endif; ?>
      </a>
      <a href="#" style="padding: 12px 24px; background: #fff; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
        Financement <?php if ($showCount): ?>(5)<?php endif; ?>
      </a>
      <a href="#" style="padding: 12px 24px; background: #fff; border: 1px solid #d4c5b0; border-radius: 8px; color: #1a1a1a; text-decoration: none; font-weight: 600; transition: all 0.2s;">
        Tendances immobilières <?php if ($showCount): ?>(4)<?php endif; ?>
      </a>
    </div>
  </div>
</section>

<?php
// ════════════════════════════════════════════════════════════
// 4. FINAL CTA
// ════════════════════════════════════════════════════════════

$finalCtaData = $pageBlocks['cta_final']['data'] ?? [];
$finalHeadline = htmlspecialchars($finalCtaData['headline'] ?? 'Restez informé');
$finalDesc = htmlspecialchars($finalCtaData['description'] ?? '');
$finalCtaText = htmlspecialchars($finalCtaData['cta_text'] ?? 'S\'abonner à la newsletter');
$finalCtaUrl = htmlspecialchars($finalCtaData['cta_url'] ?? '/contact');
?>

<section style="padding: 100px 24px; background: #722F37; color: #fff; text-align: center;">
  <div style="max-width: 800px; margin: 0 auto;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; margin: 0 0 20px;">
      <?php echo $finalHeadline; ?>
    </h2>

    <?php if ($finalDesc): ?>
    <p style="font-size: 18px; opacity: 0.95; margin: 0 0 40px; line-height: 1.6;">
      <?php echo $finalDesc; ?>
    </p>
    <?php endif; ?>

    <!-- Newsletter signup form -->
    <form style="display: flex; gap: 12px; margin-bottom: 24px; justify-content: center; flex-wrap: wrap;">
      <input type="email" name="email" placeholder="Votre email" required style="padding: 12px 20px; border: none; border-radius: 8px; font-size: 15px; flex: 1; min-width: 250px;">
      <button type="submit" style="background: #C9A84C; color: #000; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 15px; transition: background 0.2s;">
        S'abonner
      </button>
    </form>

    <p style="font-size: 14px; opacity: 0.8; margin: 0;">
      ✓ Reçevez les derniers articles directement dans votre boîte mail
    </p>
  </div>
</section>
