<?php
$advisorDisplayName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
if ($advisorDisplayName === '') {
    $advisorDisplayName = ADVISOR_NAME ?: APP_NAME;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(replacePlaceholders((string)($pageTitle ?? 'IMMO LOCAL+'))) ?> — <?= htmlspecialchars($advisorDisplayName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/css/dashboard.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/dashboard.css') ?>">
    <link rel="stylesheet"
          href="/admin/assets/css/settings.css?v=<?= file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/assets/css/settings.css') ? filemtime($_SERVER['DOCUMENT_ROOT'].'/admin/assets/css/settings.css') : 1 ?>">
</head>
<body data-current-module="<?= htmlspecialchars($module ?? 'construire') ?>">
<div class="dashboard-container" id="dashboard-container">

    <aside class="sidebar" id="sidebar">

        <!-- BRAND / LOGO -->
        <div class="sidebar-brand">
            <div class="brand-logo">
                <i class="fas fa-building"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">IMMO LOCAL<span class="brand-plus">+</span></span>
                <span class="brand-sub"><?= htmlspecialchars($advisorDisplayName) ?></span>
            </div>
        </div>

        <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

        <div class="sidebar-footer">
            <?php $user = Auth::user(); ?>
            <div class="user-profile">
                <div class="user-initials"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                <span class="user-name"><?= htmlspecialchars($user['name'] ?? $advisorDisplayName) ?> Conseiller</span>
            </div>
            <button class="collapse-btn" id="sidebar-toggle" type="button">
                <i class="fas fa-chevron-left" id="toggle-icon"></i>
                <span class="toggle-label">Réduire</span>
            </button>
        </div>

    </aside>

    <div class="layout-body">

        <!-- TOPBAR -->
        <header class="topbar">

            <!-- Gauche : toggle mobile + breadcrumb -->
            <div class="topbar-left">
                <button class="topbar-mobile-toggle" id="mobile-sidebar-toggle" type="button" title="Menu" aria-label="Ouvrir le menu">
                    <i class="fas fa-bars"></i>
                </button>
                <nav class="topbar-breadcrumb" aria-label="Fil d'Ariane">
                    <a href="/admin?module=construire" class="breadcrumb-home" data-module="construire" title="Accueil">
                        <i class="fas fa-house"></i>
                    </a>
                    <i class="fas fa-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current"><?= htmlspecialchars(replacePlaceholders((string)($pageTitle ?? ''))) ?></span>
                </nav>
            </div>

            <!-- Centre : recherche globale -->
            <div class="topbar-center">
                <div class="topbar-search">
                    <i class="fas fa-magnifying-glass topbar-search-icon"></i>
                    <input type="text" class="topbar-search-input" placeholder="Rechercher dans IMMO LOCAL+…" autocomplete="off">
                    <kbd class="topbar-search-kbd">⌘K</kbd>
                </div>
            </div>

            <!-- Droite : actions + user menu -->
            <div class="topbar-right">
                <div class="ia-header-widget" title="Statut IA : <?= get_ia_status() ?>">
                    <span class="ia-dot <?= get_ia_status() ?>"></span>
                    <a href="/admin?module=ia-config" class="ia-gear" title="Config IA" aria-label="Config IA">
                        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.07-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.48 7.48 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.89 2h-3.78a.5.5 0 0 0-.49.42l-.36 2.54c-.58.22-1.13.53-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.72 8.48a.5.5 0 0 0 .12.64l2.03 1.58c-.05.31-.07.63-.07.94s.02.63.07.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.41 1.05.72 1.63.94l.36 2.54a.5.5 0 0 0 .49.42h3.78a.5.5 0 0 0 .49-.42l.36-2.54c.58-.22 1.13-.53 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z"/>
                        </svg>
                    </a>
                </div>
                <a href="/" target="_blank" class="topbar-btn" title="Voir le site public">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                </a>
                <button class="topbar-btn" title="Aide & documentation" aria-label="Aide et documentation">
                    <i class="fas fa-circle-question"></i>
                </button>
                <button class="topbar-btn" title="Notifications" id="notif-btn" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge">2</span>
                </button>

                <div class="topbar-divider"></div>

                <!-- Menu utilisateur -->
                <div class="user-menu" id="user-menu">
                    <button class="user-menu-trigger" id="user-menu-trigger" type="button">
                        <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                        <div class="user-menu-info">
                            <span class="user-menu-name"><?= htmlspecialchars($user['name'] ?? $advisorDisplayName) ?></span>
                            <span class="user-menu-role">Conseiller</span>
                        </div>
                        <i class="fas fa-chevron-down user-menu-arrow"></i>
                    </button>

                    <div class="user-dropdown" id="user-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                            <div>
                                <div class="dropdown-name"><?= htmlspecialchars($user['name'] ?? $advisorDisplayName) ?></div>
                                <div class="dropdown-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/admin?module=profil" class="dropdown-item" data-module="profil">
                            <i class="fas fa-user"></i>
                            Mon profil
                        </a>
                        <a href="/admin?module=parametres" class="dropdown-item" data-module="parametres">
                            <i class="fas fa-gear"></i>
                            Paramètres
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="/admin/logout" class="dropdown-item dropdown-item-danger">
                            <i class="fas fa-right-from-bracket"></i>
                            Se déconnecter
                        </a>
                    </div>
                </div>
            </div>

        </header>

        <!-- CONTENU -->
        <main class="main-content">
            <div id="main-content">
                <?php ob_start(); renderContent(); $adminContent = ob_get_clean(); echo replacePlaceholders($adminContent); ?>
            </div>
        </main>

        <!-- FOOTER -->
        <?php require_once __DIR__ . '/partials/footer.php'; ?>

    </div><!-- /.layout-body -->

</div>

<script src="/admin/assets/js/dashboard.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/dashboard.js') ?>"></script>
</body>
</html>
