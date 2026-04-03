<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'IMMO LOCAL+') ?> — Eduardo De Sul</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/css/dashboard.css">
</head>
<body>
<div class="dashboard-container" id="dashboard-container">

    <aside class="sidebar" id="sidebar">

        <?php require_once 'partials/sidebar.php'; ?>

        <div class="sidebar-footer">
            <?php $user = Auth::user(); ?>
            <div class="user-profile">
                <div class="user-initials"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                <span class="user-name"><?= htmlspecialchars($user['name'] ?? 'Eduardo De Sul') ?> Conseiller</span>
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
            <div class="topbar-left">
                <span class="topbar-title">
                    <?= htmlspecialchars($pageTitle ?? '') ?>
                </span>
            </div>
            <div class="topbar-right">
                <!-- Notifications -->
                <button class="topbar-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge">2</span>
                </button>

                <!-- Menu utilisateur -->
                <div class="user-menu" id="user-menu">
                    <button class="user-menu-trigger" id="user-menu-trigger" type="button">
                        <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                        <div class="user-menu-info">
                            <span class="user-menu-name"><?= htmlspecialchars($user['name'] ?? 'Eduardo De Sul') ?></span>
                            <span class="user-menu-role">Conseiller</span>
                        </div>
                        <i class="fas fa-chevron-down user-menu-arrow"></i>
                    </button>

                    <div class="user-dropdown" id="user-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'ED', 0, 2))) ?></div>
                            <div>
                                <div class="dropdown-name"><?= htmlspecialchars($user['name'] ?? 'Eduardo De Sul') ?></div>
                                <div class="dropdown-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" data-module="profil">
                            <i class="fas fa-user"></i>
                            Mon profil
                        </a>
                        <a href="#" class="dropdown-item" data-module="parametres">
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
                <?php renderContent(); ?>
            </div>
        </main>

    </div><!-- /.layout-body -->

</div>

<script src="/admin/assets/js/dashboard.js"></script>
</body>
</html>
