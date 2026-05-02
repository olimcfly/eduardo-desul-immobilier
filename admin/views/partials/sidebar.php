<?php
$currentModule = $module ?? 'dashboard';
require_once ROOT_PATH . '/core/AdminNavigation.php';

$authUser = Auth::user();
$menuGroups = AdminNavigation::sidebarGroups(ROOT_PATH, (string) $currentModule, $authUser);
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <?php foreach ($menuGroups as $sectionLabel => $items): ?>
            <li class="sidebar-section-head">
                <span class="nav-section-label"><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <?php foreach ($items as $item):
                $targetUrl = (string) $item['url'];
                $isActive = (bool) $item['active'];
                $tooltip = (string) ($item['title'] ?? $item['label'] ?? '');
                ?>
                <li>
                    <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>"
                       class="menu-item<?= $isActive ? ' active' : '' ?>"
                       data-module="<?= htmlspecialchars((string) ($item['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-tooltip="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>"
                       title="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>"
                       style="position: relative;">
                        <span class="menu-icon"><i class="<?= htmlspecialchars((string) $item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span class="menu-label"><?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</nav>
