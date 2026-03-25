<?php
/**
 * PAGE TEMPLATE
 * Variables disponibles depuis index.php:
 * - $page_title
 * - $meta_description
 * - $page_h1
 * - $page_content
 * - $current_slug
 * - $pdo (connexion DB)
 */

$resolvedPageContent = $page_content ?? '';

$renderSectionsPath = ROOT_PATH . '/front/rende-sections.php';
if (file_exists($renderSectionsPath)) {
    require_once $renderSectionsPath;

    if (!empty($page_content)) {
        $sections = json_decode($page_content, true);
        if (is_array($sections)) {
            ob_start();
            renderSections($sections, $pdo);
            $resolvedPageContent = ob_get_clean();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Page'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? ''); ?>">
    <meta name="theme-color" content="#6366f1">
    <meta name="robots" content="index, follow">

    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            margin: 0;
            background: var(--bg-color, #f8fafc);
            color: var(--text-color, #1e293b);
            font-family: var(--font-family, 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
        }
        .page-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 48px 24px;
            min-height: 60vh;
        }
        .page-main h1 {
            margin: 0 0 24px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.15;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="page-main">
        <?php if (!empty($page_h1)): ?>
            <h1><?php echo htmlspecialchars($page_h1); ?></h1>
        <?php endif; ?>

        <div class="page-content">
            <?php echo $resolvedPageContent; ?>
        </div>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "<?php echo addslashes(_ss('site_name', 'Mon entreprise')); ?>",
      "description": "<?php echo addslashes(_ss('site_description', '')); ?>",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "<?php echo addslashes(_ss('city', '')); ?>",
        "addressRegion": "<?php echo addslashes(_ss('region', '')); ?>",
        "addressCountry": "FR"
      }
    }
    </script>
</body>
</html>
