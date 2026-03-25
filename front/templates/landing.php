<?php
/**
 * LANDING TEMPLATE
 * Variables disponibles depuis index.php:
 * - $page_title
 * - $meta_description
 * - $page_content (contient tout le HTML inline)
 * - $current_slug
 * - $pdo (connexion DB)
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Landing'); ?></title>
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
        .landing-main {
            min-height: 60vh;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="landing-main">
        <?php echo $page_content ?? ''; ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
