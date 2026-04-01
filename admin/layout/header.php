<?php
/**
 * HEADER — IMMO LOCAL+ (Mode Strict Minimaliste)
 * /admin/layout/header.php
 *
 * Spécifications:
 * - Barre d'outils externe (liens WhatsApp, Webmail, etc.)
 * - Breadcrumb avec nom d'utilisateur + ville
 * - Champ de recherche
 * - Icônes d'action (notifications, assistant, etc.)
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

// Titre de la page (défini par le contrôleur)
$pageTitle = $pageTitle ?? 'Tableau de bord';

// Liens d'accès rapide
$quickLinks = [
    ['url' => 'https://wa.me', 'icon' => 'fab fa-whatsapp', 'title' => 'WhatsApp', 'target' => '_blank'],
    ['url' => 'https://webmail.ovh.com', 'icon' => 'fas fa-envelope', 'title' => 'Webmail', 'target' => '_blank'],
    ['url' => 'https://calendar.google.com', 'icon' => 'fas fa-calendar-alt', 'title' => 'Calendrier', 'target' => '_blank'],
    ['url' => 'https://gmail.com', 'icon' => 'fab fa-google', 'title' => 'Gmail', 'target' => '_blank'],
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
           IMMO LOCAL+ HEADER — Mode Strict Minimaliste
           ============================================ */

        :root {
            --color-primary: #6366F1;
            --color-primary-light: #EEF2FF;
            --color-white: #FFFFFF;
            --color-gray-50: #F9FAFB;
            --color-gray-100: #F3F4F6;
            --color-gray-200: #E5E7EB;
            --color-gray-600: #4B5563;
            --color-text-primary: #1F2937;
            --color-text-secondary: #6B7280;
            --color-shadow: rgba(0, 0, 0, 0.1);

            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;

            --radius: 0.5rem;
            --border: 1px solid var(--color-gray-200);
            --shadow: 0 1px 3px var(--color-shadow);
            --font-base: 14px;
            --font-title: 16px;
            --font-note: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            background: var(--color-gray-50);
            color: var(--color-text-primary);
            font-size: var(--font-base);
            line-height: 1.5;
        }

        /* Header Container */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-lg);
            background: var(--color-white);
            border-bottom: var(--border);
            padding: var(--spacing-md) var(--spacing-xl);
            box-shadow: var(--shadow);
            flex-wrap: wrap;
        }

        /* Breadcrumb */
        .header-breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: var(--font-base);
            color: var(--color-text-secondary);
            min-width: 200px;
        }

        .header-breadcrumb-user {
            font-weight: 600;
            color: var(--color-primary);
        }

        .header-breadcrumb-sep {
            color: var(--color-gray-200);
        }

        .header-breadcrumb-page {
            font-weight: 500;
            color: var(--color-text-primary);
        }

        /* Search Bar */
        .header-search {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            background: var(--color-gray-100);
            border: var(--border);
            border-radius: var(--radius);
            padding: var(--spacing-sm) var(--spacing-md);
            min-width: 250px;
            flex: 1;
            max-width: 400px;
        }

        .header-search input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: var(--font-base);
            font-family: inherit;
            color: var(--color-text-primary);
        }

        .header-search input::placeholder {
            color: var(--color-text-secondary);
        }

        .header-search i {
            color: var(--color-text-secondary);
            font-size: 14px;
        }

        /* Quick Links */
        .header-quick-links {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding-left: var(--spacing-lg);
            border-left: var(--border);
        }

        .header-quick-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            background: var(--color-gray-100);
            color: var(--color-text-secondary);
            border: var(--border);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 16px;
        }

        .header-quick-link:hover {
            background: var(--color-primary);
            color: var(--color-white);
            border-color: var(--color-primary);
        }

        /* User Avatar */
        .header-avatar {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius);
            background: var(--color-gray-100);
            border: var(--border);
            text-decoration: none;
            color: var(--color-text-primary);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .header-avatar:hover {
            background: var(--color-primary-light);
            border-color: var(--color-primary);
        }

        .header-avatar-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--color-primary);
            color: var(--color-white);
            font-weight: 600;
            font-size: 14px;
        }

        .header-avatar-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: var(--font-note);
        }

        .header-avatar-name {
            font-weight: 600;
            color: var(--color-text-primary);
        }

        .header-avatar-role {
            color: var(--color-text-secondary);
            font-size: 11px;
        }

        /* Layout Wrapper */
        .admin-wrapper {
            display: flex;
            height: 100vh;
            background: var(--color-white);
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
            padding: var(--spacing-xl);
            background: var(--color-gray-50);
        }

        @media (max-width: 768px) {
            .header {
                padding: var(--spacing-md);
                gap: var(--spacing-md);
            }

            .header-search {
                min-width: auto;
                max-width: 100%;
            }

            .header-quick-links {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            .header {
                padding: var(--spacing-md) var(--spacing-lg);
                gap: var(--spacing-md);
            }

            .header-quick-links {
                display: none;
            }

            .header-search {
                min-width: auto;
                flex: 1;
            }
        }
    </style>
</head>

<body>

<div class="admin-wrapper">

    <!-- HEADER -->
    <div class="admin-main">
        <header class="header" role="banner">

            <!-- Breadcrumb -->
            <div class="header-breadcrumb">
                <span class="header-breadcrumb-user"><?= $advisorName ?></span>
                <?php if ($advisorCity): ?>
                    <span class="header-breadcrumb-sep">·</span>
                    <span class="header-breadcrumb-city"><?= $advisorCity ?></span>
                <?php endif; ?>
                <span class="header-breadcrumb-sep">›</span>
                <span class="header-breadcrumb-page"><?= htmlspecialchars($pageTitle) ?></span>
            </div>

            <!-- Search Bar -->
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Rechercher…" aria-label="Recherche globale">
            </div>

            <!-- Quick Links -->
            <nav class="header-quick-links" aria-label="Accès rapide">
                <?php foreach ($quickLinks as $link): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>"
                       class="header-quick-link"
                       title="<?= htmlspecialchars($link['title']) ?>"
                       target="<?= $link['target'] ?? '_self' ?>"
                       rel="noopener noreferrer">
                        <i class="<?= $link['icon'] ?>"></i>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- User Avatar -->
            <a href="?section=parametres" class="header-avatar" title="Profil utilisateur">
                <div class="header-avatar-icon">
                    <?= strtoupper(mb_substr($advisorName, 0, 1)) ?>
                </div>
                <div class="header-avatar-text">
                    <span class="header-avatar-name"><?= htmlspecialchars($advisorName) ?></span>
                    <span class="header-avatar-role">Administrateur</span>
                </div>
            </a>

        </header>

        <!-- MAIN CONTENT -->
        <main class="admin-content">
