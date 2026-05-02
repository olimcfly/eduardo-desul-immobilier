<?php
$advisorDisplayName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
$helpContext = preg_replace('/[^a-z0-9_-]/', '', (string) ($module ?? 'dashboard'));
$helpLink = '/admin?module=aide&context=' . rawurlencode($helpContext);
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
    <link rel="stylesheet" href="<?= e(asset_url('/admin/assets/css/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/admin/assets/css/settings.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/admin/assets/css/hub-page.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/admin/assets/css/tooltip.css')) ?>">
</head>
<body data-current-module="<?= htmlspecialchars($module ?? 'dashboard') ?>"
      <?php if (isset($adminModulePlugin) && $adminModulePlugin instanceof AdminModulePlugin): ?>
      data-plugin-slug="<?= htmlspecialchars($adminModulePlugin->slug, ENT_QUOTES, 'UTF-8') ?>"
      data-plugin-name="<?= htmlspecialchars($adminModulePlugin->name, ENT_QUOTES, 'UTF-8') ?>"
      data-plugin-in-sidebar="<?= $adminModulePlugin->inSidebar ? '1' : '0' ?>"
      <?php endif; ?>>
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
                    <a href="/admin?module=dashboard" class="breadcrumb-home" data-module="dashboard" title="Accueil">
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
                <a href="<?= htmlspecialchars($helpLink) ?>" class="topbar-btn" title="Comprendre ce module" aria-label="Comprendre ce module">
                    <i class="fas fa-circle-question"></i>
                </a>
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

        <!-- NOTIFICATION DÉVELOPPEMENT -->
        <?php if (setting('site_development_mode', '1') === '1'): ?>
        <div style="background: linear-gradient(90deg, #b8860b, #ffd700); color: #1a1a1a; padding: 12px 20px; font-weight: 600; font-size: 14px; border-bottom: 1px solid #d4a505; text-align: center;">
            ℹ️ <strong>Site en développement</strong> — Le frontend est accessible au public, mais le backend reste en cours de configuration.
        </div>
        <?php endif; ?>

        <!-- CONTENU -->
        <main class="main-content">
            <div id="main-content">
                <?php ob_start(); renderContent(); $adminContent = ob_get_clean(); echo replacePlaceholders($adminContent); ?>
            </div>
        </main>

        <?php
        $aiHelpWidgetPath = ROOT_PATH . '/modules/ai-help-chat/widget.php';
        $aiHelpServicePath = ROOT_PATH . '/modules/ai-help-chat/service.php';
        $aiHelpPermissionsPath = ROOT_PATH . '/modules/ai-help-chat/permissions.php';
        if (is_file($aiHelpWidgetPath) && is_file($aiHelpServicePath) && is_file($aiHelpPermissionsPath)) {
            require_once $aiHelpPermissionsPath;
            require_once $aiHelpServicePath;
            require_once $aiHelpWidgetPath;

            if (function_exists('renderAiHelpChatWidget')) {
                $aiHelpContext = [
                    'module' => (string) ($module ?? 'dashboard'),
                    'page' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
                ];
                $aiHelpService = new AiHelpChatService(db());
                renderAiHelpChatWidget($aiHelpService, $aiHelpContext);
            }
        }
        ?>

        <!-- FOOTER -->
        <?php require_once __DIR__ . '/partials/footer.php'; ?>

    </div><!-- /.layout-body -->

</div>

<script src="<?= e(asset_url('/admin/assets/js/dashboard.js')) ?>"></script>

<?php if (!empty($_SESSION['show_welcome_popup'])): unset($_SESSION['show_welcome_popup']);
$welcomeMessages = [
    ['emoji'=>'🚀','title'=>'Prêt à conquérir le Bordeaux Métropole ?','text'=>'Le marché immobilier n\'a qu\'à bien se tenir. Vous êtes là, et c\'est suffisant.'],
    ['emoji'=>'☀️','title'=>'Bonjour patron !','text'=>'Les biens ne se vendent pas tout seuls. Mais avec vous derrière le clavier, c\'est presque pareil.'],
    ['emoji'=>'🏡','title'=>'Maison. Appartement. Empire.','text'=>'La session est ouverte. Bordeaux attend vos ordres.'],
    ['emoji'=>'💼','title'=>'Connexion réussie. Mission : cartonner.','text'=>'Agenda chargé ou moment calme ? Dans tous les cas, bienvenue dans le QG.'],
    ['emoji'=>'🎯','title'=>'Le chasseur est dans la place.','text'=>'Biens, leads, clients — tout ça ne sait pas encore ce qui l\'attend.'],
    ['emoji'=>'🌟','title'=>'Une nouvelle journée, de nouvelles commissions.','text'=>'On ne va pas se mentir, c\'est pour ça qu\'on est là. Bonne session !'],
    ['emoji'=>'⚡','title'=>'Alerte : expert immobilier connecté.','text'=>'Les autres conseillers du Bordeaux Métropole peuvent commencer à s\'inquiéter.'],
    ['emoji'=>'🦁','title'=>'Le roi est de retour dans son territoire.','text'=>'Bordeaux, Luynes, Puyricard… Le Bordeaux Métropole appartient à ceux qui le connaissent.'],
    ['emoji'=>'🎪','title'=>'Et le show commence !','text'=>'Rideau ouvert, clients briefés, biens prêts. Il ne manquait plus que vous.'],
    ['emoji'=>'🧠','title'=>'Connexion établie. Neurones en route.','text'=>'Statistiques, leads, contenus… votre cerveau immobilier est en ligne.'],
    ['emoji'=>'🏆','title'=>'L\'équipe gagnante est de retour.','text'=>'Spoiler : l\'équipe gagnante, c\'est vous. Et votre ordinateur.'],
    ['emoji'=>'🌅','title'=>'Une belle journée commence.','text'=>'Ou une belle soirée. Ou une belle nuit. L\'immobilier, ça ne dort jamais.'],
];
$msg = $welcomeMessages[array_rand($welcomeMessages)];
?>
<div id="welcome-popup" class="welcome-overlay" role="dialog" aria-modal="true" aria-labelledby="welcome-title">
    <div class="welcome-modal" data-animate-in>
        <div class="welcome-modal__emoji" aria-hidden="true"><?= $msg['emoji'] ?></div>
        <h2 class="welcome-modal__title" id="welcome-title"><?= htmlspecialchars($msg['title']) ?></h2>
        <p class="welcome-modal__text"><?= htmlspecialchars($msg['text']) ?></p>
        <button class="welcome-modal__close" id="welcome-close" autofocus>
            C'est parti 👊
        </button>
    </div>
</div>
<style>
.welcome-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 20, 40, .55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    animation: overlayIn .25s ease;
}
@keyframes overlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
.welcome-modal {
    background: #fff;
    border-radius: 1.25rem;
    padding: 2.5rem 2rem 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    animation: modalIn .35s cubic-bezier(.34,1.56,.64,1);
    position: relative;
}
@keyframes modalIn {
    from { opacity: 0; transform: translateY(32px) scale(.94); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.welcome-modal__emoji {
    font-size: 3.5rem;
    line-height: 1;
    margin-bottom: 1rem;
    display: block;
}
.welcome-modal__title {
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a3c5e;
    margin-bottom: .625rem;
    line-height: 1.3;
}
.welcome-modal__text {
    font-size: .95rem;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1.75rem;
}
.welcome-modal__close {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: #1a3c5e;
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: .75rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .15s;
    font-family: inherit;
}
.welcome-modal__close:hover {
    background: #c9a84c;
    transform: scale(1.03);
}
.welcome-modal__close:active {
    transform: scale(.98);
}
.welcome-overlay.closing {
    animation: overlayOut .2s ease forwards;
}
@keyframes overlayOut {
    to { opacity: 0; }
}
</style>
<script>
(function() {
    var overlay = document.getElementById('welcome-popup');
    var btn     = document.getElementById('welcome-close');
    function closePopup() {
        overlay.classList.add('closing');
        setTimeout(function() { overlay.remove(); }, 200);
    }
    btn.addEventListener('click', closePopup);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePopup();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePopup();
    });
})();
</script>
<?php endif; ?>
</body>
</html>
