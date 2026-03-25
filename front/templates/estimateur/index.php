<?php
$cfg = $context['config'] ?? [];
$zones = $context['zones'] ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <link rel="stylesheet" href="/front/assets/css/estimateur.css">
</head>
<body class="estimateur-page">
<div class="est-wrap">
  <?php include __DIR__ . '/partials/hero.php'; ?>
  <div class="est-layout">
    <main>
      <?php include __DIR__ . '/partials/steps.php'; ?>
      <?php if ($resultPayload): ?>
        <?php include __DIR__ . '/partials/result.php'; ?>
        <?php include __DIR__ . '/partials/bant.php'; ?>
        <?php include __DIR__ . '/partials/contact.php'; ?>
      <?php endif; ?>
      <?php if (!empty($_GET['success'])): ?>
        <?php include __DIR__ . '/partials/confirmation.php'; ?>
      <?php endif; ?>
    </main>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
  </div>
</div>
<script>
window.estimateurConfig = {
  mode: <?= json_encode($_POST['mode'] ?? 'quick') ?>,
  zones: <?= json_encode($zones, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="/front/assets/js/estimateur.js"></script>
</body>
</html>
