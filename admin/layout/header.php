<?php
/**
 * HEADER — IMMO LOCAL+
 * /admin/layout/header.php
 *
 * Inclus par layout.php. Requiert :
 *  - $pdo        (connexion DB)
 *  - $pageTitle  (calculé dans dashboard.php)
 *  - $activeModule
 */

// Infos conseiller
$advisorName = 'Mon espace';
$advisorCity = '';
try {
    $rows = $pdo->query("SELECT field_key, field_value FROM advisor_context
                         WHERE field_key IN ('advisor_name','advisor_city')
                         AND field_value != ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        if ($r['field_key'] === 'advisor_name') $advisorName = $r['field_value'];
        if ($r['field_key'] === 'advisor_city') $advisorCity = $r['field_value'];
    }
} catch (Exception $e) {}

// Liens rapides topbar
$headerLinks = [
    'api-keys'    => ['fa-key',             'Clés API'],
    'ai-settings' => ['fa-robot',           'Paramètres AI'],
    'settings'    => ['fa-sliders',         'Réglages & SMTP'],
    'advisor-context' => ['fa-circle-user', 'Mon profil'],
    'ressources'  => ['fa-circle-question', 'Aide'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($advisorName) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/admin/assets/css/dashboard.css">
<link rel="stylesheet" href="/admin/assets/css/admin-components.css">
<link rel="stylesheet" href="/admin/assets/css/modules.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="/admin/assets/js/admin-components.js" defer></script>
</head>
<body>
<div class="admin-wrapper">

<!-- TOPBAR -->
<header class="hd">
    <button class="hd-btn" id="menuToggle" style="display:none"
            onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <div class="hd-breadcrumb">
        <a href="?page=dashboard"><?= htmlspecialchars($advisorName) ?><?= $advisorCity ? ' · '.htmlspecialchars($advisorCity) : '' ?></a>
        <?php if (!empty($pageTitle)): ?>
            <span class="sep">›</span>
            <strong><?= htmlspecialchars($pageTitle) ?></strong>
        <?php endif; ?>
    </div>

    <div class="hd-spacer"></div>

    <div class="hd-search">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="globalSearch" placeholder="Rechercher…">
    </div>

    <!-- Icônes navigation rapide -->
    <nav class="admin-header-icons">
        <?php foreach ($headerLinks as $page => [$icon, $tip]): ?>
            <a href="/admin/dashboard.php?page=<?= $page ?>"
               class="admin-header-icon <?= ($activeModule ?? '') === $page ? 'active' : '' ?>"
               data-tip="<?= htmlspecialchars($tip) ?>">
                <i class="fas <?= $icon ?>"></i>
            </a>
        <?php endforeach; ?>

        <span class="admin-header-sep"></span>

        <a href="/" target="_blank" class="admin-header-icon" data-tip="Voir le site">
            <i class="fas fa-arrow-up-right-from-square"></i>
        </a>
        <a href="/admin/logout.php" class="admin-header-icon admin-header-icon-logout" data-tip="Déconnexion">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </nav>
</header>

<script>
// Mobile toggle
function checkMobile() {
    const btn = document.getElementById('menuToggle');
    if (btn) btn.style.display = window.innerWidth < 900 ? 'flex' : 'none';
}
checkMobile();
window.addEventListener('resize', checkMobile);
</script>
