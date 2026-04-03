<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$advisorName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
if ($advisorName === '') {
    $advisorName = ADVISOR_NAME ?: APP_NAME;
}
function isActive(string $path, string $current): string {
    return ($path !== '/' ? str_starts_with($current, $path) : $current === '/') ? 'active' : '';
}
?>
<header class="site-header" id="site-header">
    <div class="container header__inner">

        <!-- Logo -->
        <a href="<?= e(url('/')) ?>" class="header__logo" aria-label="<?= e($advisorName) ?> — Accueil">
            <span class="logo__icon">🏡</span>
            <span class="logo__text">
                <strong><?= e($advisorName) ?></strong>
                <em>Immobilier</em>
            </span>
        </a>

        <!-- Navigation principale -->
        <?php require __DIR__ . '/nav.php'; ?>

        <!-- CTA header -->
        <div class="header__actions">
            <?php if (APP_PHONE): ?>
            <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--phone">
                📞 <?= e(APP_PHONE) ?>
            </a>
            <?php endif; ?>
            <a href="<?= e(url('/estimation-gratuite')) ?>" class="btn btn--primary">Estimation gratuite</a>
        </div>

        <!-- Burger mobile -->
        <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-mobile">
            <span></span><span></span><span></span>
        </button>

    </div>
</header>
