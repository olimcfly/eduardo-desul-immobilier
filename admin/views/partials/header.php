<?php $current = $_SERVER['REQUEST_URI']; ?>
<header class="topbar">
    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('collapsed')" title="Menu">
        ☰
    </button>

    <div class="topbar-breadcrumb">
        <?= $breadcrumb ?? '' ?>
    </div>

    <div class="topbar-actions">
        <a href="/" target="_blank" class="btn btn-sm btn-outline" title="Voir le site">
            🌐 Site public
        </a>

        <div class="topbar-notif" title="Notifications">
            🔔
            <?php if (!empty($notifCount)): ?>
                <span class="notif-badge"><?= $notifCount ?></span>
            <?php endif; ?>
        </div>

        <div class="topbar-user">
            <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
        </div>
    </div>
</header>
