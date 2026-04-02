<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> — ImmoSite</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-wrapper">
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="main-content">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>

<script src="/admin/assets/js/admin.js"></script>
<?php if (!empty($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
