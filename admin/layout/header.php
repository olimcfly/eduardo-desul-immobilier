<?php
/**
 * HEADER — IMMO LOCAL+ (Version Minimaliste & Fonctionnelle)
 * /admin/layout/header.php
 *
 * Spécifications:
 * - Logo + titre de la page
 * - Bouton toggle sidebar (mobile)
 * - Zone de notifications (badge dynamique)
 * - Profil utilisateur (avatar + menu déroulant)
 */

// Infos utilisateur (dynamiques)
$advisorName = 'Mon espace';
$advisorCity = '';
try {
    if (!empty($pdo)) {
        $stmt = $pdo->query("SELECT field_key, field_value FROM advisor_context
                             WHERE field_key IN ('advisor_name', 'advisor_city')");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['field_key'] === 'advisor_name') {
                $advisorName = htmlspecialchars($row['field_value']);
            }
            if ($row['field_key'] === 'advisor_city') {
                $advisorCity = htmlspecialchars($row['field_value']);
            }
        }
    }
} catch (Exception $e) {
    error_log('Header: ' . $e->getMessage());
}

// Titre de la page
$pageTitle = $pageTitle ?? 'Tableau de bord';

// Notifications simulées (à remplacer par des vraies données)
$notificationCount = 3;
$notifications = [
    ['icon' => 'fa-bell', 'title' => 'Nouveau lead captée', 'desc' => '123 Rue de Paris, Lyon', 'time' => 'Il y a 5 min'],
    ['icon' => 'fa-calendar-alt', 'title' => 'RDV à confirmer', 'desc' => 'Mardi 14h - M. Martin', 'time' => 'Il y a 2h'],
    ['icon' => 'fa-home', 'title' => 'Bien vendu', 'desc' => 'Appartement 3P - Villeurbanne', 'time' => 'Hier'],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($advisorName) ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="/admin/assets/css/immo-local-plus.css">

    <style>
        /* ============================================
           IMMO LOCAL+ HEADER — Version Minimaliste & Fonctionnelle
           ============================================ */

        :root {
            --header-bg: #ffffff;
            --header-text: #1f2937;
            --header-text-secondary: #6b7280;
            --header-border: #e5e7eb;
            --header-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            --primary: #4f7df3;
            --primary-light: #eef2ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            background: #f9fafb;
            color: var(--header-text);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Header Container */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            background: var(--header-bg);
            border-bottom: 1px solid var(--header-border);
            padding: 1rem 2rem;
            box-shadow: var(--header-shadow);
            flex-wrap: wrap;
            height: 60px;
        }

        /* Header Left */
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
            min-width: 200px;
        }

        /* Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--header-text);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.2s ease;
        }

        .sidebar-toggle:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Page Title */
        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--header-text);
            margin: 0;
            white-space: nowrap;
        }

        /* Header Right */
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
            justify-content: flex-end;
        }

        /* Notifications */
        .notifications {
            position: relative;
        }

        .notification-btn {
            background: none;
            border: none;
            color: var(--header-text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            position: relative;
            transition: color 0.2s ease;
        }

        .notification-btn:hover {
            color: var(--primary);
        }

        /* Badge */
        .badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: -10px;
            width: 320px;
            background: var(--header-bg);
            border: 1px solid var(--header-border);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin-top: 0.75rem;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-dropdown.open {
            display: block;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid var(--header-border);
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .notification-item-content {
            flex: 1;
            min-width: 0;
        }

        .notification-item-title {
            font-weight: 500;
            color: var(--header-text);
            font-size: 13px;
            margin-bottom: 0.25rem;
        }

        .notification-item-desc {
            color: var(--header-text-secondary);
            font-size: 12px;
            margin-bottom: 0.25rem;
        }

        .notification-item-time {
            color: var(--header-text-secondary);
            font-size: 11px;
        }

        /* User Profile */
        .user-profile {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: #f3f4f6;
            border: 1px solid var(--header-border);
            text-decoration: none;
            color: var(--header-text);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .profile-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .profile-chevron {
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .profile-btn.open .profile-chevron {
            transform: rotate(180deg);
        }

        /* Profile Dropdown */
        .profile-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--header-bg);
            border: 1px solid var(--header-border);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin-top: 0.75rem;
            z-index: 1000;
            min-width: 160px;
            overflow: hidden;
        }

        .profile-dropdown.open {
            display: block;
        }

        .profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--header-border);
            text-decoration: none;
            color: var(--header-text);
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .profile-dropdown-item:last-child {
            border-bottom: none;
        }

        .profile-dropdown-item:hover {
            background: #f3f4f6;
            color: var(--primary);
        }

        .profile-dropdown-item.danger:hover {
            background: #fee2e2;
            color: var(--danger);
        }

        .profile-dropdown-item i {
            width: 16px;
            text-align: center;
        }

        /* Layout Wrapper */
        .admin-wrapper {
            display: flex;
            height: 100vh;
            background: var(--header-bg);
        }

        .admin-main {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }

        .admin-content {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            background: #f9fafb;
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .app-header {
                padding: 1rem 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .app-header {
                padding: 1rem;
                gap: 1rem;
            }

            .header-left {
                gap: 1rem;
            }

            .page-title {
                font-size: 16px;
            }

            .header-right {
                gap: 1rem;
            }
        }
    </style>
</head>

<body>

<div class="admin-wrapper">

    <!-- HEADER -->
    <div class="admin-main">
        <header class="app-header" role="banner">

            <!-- Header Left -->
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" title="Basculer la barre latérale">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>

            <!-- Header Right -->
            <div class="header-right">

                <!-- Notifications -->
                <div class="notifications">
                    <button class="notification-btn" id="notificationBtn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="badge"><?= $notificationCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-item-icon">
                                    <i class="fas <?= $notification['icon'] ?>"></i>
                                </div>
                                <div class="notification-item-content">
                                    <div class="notification-item-title"><?= htmlspecialchars($notification['title']) ?></div>
                                    <div class="notification-item-desc"><?= htmlspecialchars($notification['desc']) ?></div>
                                    <div class="notification-item-time"><?= htmlspecialchars($notification['time']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="user-profile">
                    <button class="profile-btn" id="profileBtn" title="Profil utilisateur">
                        <div class="profile-avatar">
                            <?= htmlspecialchars(strtoupper(mb_substr($advisorName, 0, 1))) ?>
                        </div>
                        <span><?= htmlspecialchars($advisorName) ?></span>
                        <i class="fas fa-chevron-down profile-chevron"></i>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="?page=advisor-context" class="profile-dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Mon profil</span>
                        </a>
                        <a href="?page=settings" class="profile-dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Paramètres</span>
                        </a>
                        <a href="?logout=1" class="profile-dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </div>

            </div>

        </header>

        <!-- MAIN CONTENT -->
        <main class="admin-content">

        </main>

    </div><!-- /.admin-main -->

</div><!-- /.admin-wrapper -->

<script>
// Gestion des menus déroulants
document.addEventListener('DOMContentLoaded', function() {
    // Notifications
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    notificationBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown?.classList.toggle('open');
        profileDropdown?.classList.remove('open');
        profileBtn?.classList.remove('open');
    });

    // Profile
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    profileBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown?.classList.toggle('open');
        notificationDropdown?.classList.remove('open');
    });

    // Fermer les dropdowns au clic hors
    document.addEventListener('click', function() {
        notificationDropdown?.classList.remove('open');
        profileDropdown?.classList.remove('open');
        profileBtn?.classList.remove('open');
    });

    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    sidebarToggle?.addEventListener('click', function() {
        const sidebar = document.querySelector('.sb-prestige');
        sidebar?.classList.toggle('collapsed');
    });
});
</script>
