<?php
declare(strict_types=1);

if (!function_exists('admin_navigation_home_pages')) {
    function admin_navigation_home_pages(): array
    {
        $modulesFile = __DIR__ . '/../data/modules.php';
        if (!is_file($modulesFile)) {
            return [];
        }

        $modules = require $modulesFile;
        if (!is_array($modules)) {
            return [];
        }

        $pages = [];
        foreach ($modules as $key => $module) {
            if (!is_array($module)) {
                continue;
            }

            $pages[(string) $key] = [
                'title' => (string) ($module['title'] ?? ''),
                'description' => (string) ($module['description'] ?? ''),
                'route' => (string) ($module['route'] ?? ''),
                'section' => (string) ($module['section'] ?? ''),
            ];
        }

        return $pages;
    }
}

if (!function_exists('admin_module_home_page_config')) {
    function admin_module_home_page_config(string $key): array
    {
        $pages = admin_navigation_home_pages();

        return $pages[$key] ?? [];
    }
}

if (!function_exists('render_admin_module_home_page')) {
    function render_admin_module_home_intro(array $config): void
    {
        $badge = (string) ($config['badge'] ?? '');
        $title = (string) ($config['title'] ?? '');
        $description = (string) ($config['description'] ?? '');
        $sectionTitle = (string) ($config['section_title'] ?? 'Les modules de cette section');
        $cards = (array) ($config['cards'] ?? []);
        $cta = (array) ($config['cta'] ?? []);
        ?>
        <div class="admin-home-page">
            <header class="admin-hero">
                <?php if ($badge !== ''): ?>
                    <div class="admin-hero-badge"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($title !== ''): ?>
                    <h1 class="admin-hero-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php endif; ?>
                <?php if ($description !== ''): ?>
                    <p class="admin-hero-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </header>

            <section aria-labelledby="admin-home-section-title">
                <div class="admin-section-title" id="admin-home-section-title"><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-module-list">
                    <?php foreach ($cards as $card): ?>
                        <?php
                        $url = (string) ($card['url'] ?? '#');
                        $cardNumber = (string) ($card['number'] ?? '');
                        $cardIcon = (string) ($card['icon'] ?? 'fas fa-circle');
                        $cardTitle = (string) ($card['title'] ?? '');
                        $cardDescription = (string) ($card['description'] ?? '');
                        $accent = (string) ($card['accent'] ?? '');
                        ?>
                        <a class="admin-module-card" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"<?php if ($accent !== ''): ?> style="--card-accent: <?= htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') ?>;"<?php endif; ?>>
                            <div class="admin-module-card-number"><?= htmlspecialchars($cardNumber, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="admin-module-card-content">
                                <div class="admin-module-card-title">
                                    <span class="admin-module-card-icon" aria-hidden="true"><i class="<?= htmlspecialchars($cardIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                    <span><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <?php if ($cardDescription !== ''): ?>
                                    <div class="admin-module-card-description"><?= htmlspecialchars($cardDescription, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="admin-module-card-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php if (!empty($cta)): ?>
                <section class="admin-cta-card" aria-label="Appel à l’action">
                    <div>
                        <?php if (!empty($cta['title'])): ?>
                            <h2><?= htmlspecialchars((string) $cta['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($cta['description'])): ?>
                            <p><?= htmlspecialchars((string) $cta['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($cta['url']) && !empty($cta['button'])): ?>
                        <a class="admin-cta-button" href="<?= htmlspecialchars((string) $cta['url'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-arrow-right"></i>
                            <span><?= htmlspecialchars((string) $cta['button'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('render_admin_module_home_page')) {
    function render_admin_module_home_page(array $config): void
    {
        render_admin_module_home_intro($config);
    }
}
