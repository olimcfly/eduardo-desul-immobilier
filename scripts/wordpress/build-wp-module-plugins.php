<?php
declare(strict_types=1);

/**
 * Génère un plugin WordPress par module (dossiers wordpress-plugins/immolocal-module-…/).
 * Usage: php scripts/wordpress/build-wp-module-plugins.php
 */
$root = dirname(__DIR__, 2);
$metaPath = $root . '/config/admin_module_plugin_meta.php';
$outBase = $root . '/wordpress-plugins';
if (!is_file($metaPath)) {
    fwrite(STDERR, "Meta manquant: $metaPath\n");
    exit(1);
}
/** @var array<string, array<string, mixed>> $meta */
$meta = require $metaPath;

if (!is_dir($outBase) && !mkdir($outBase, 0755, true)) {
    fwrite(STDERR, "Impossible de créer $outBase\n");
    exit(1);
}

$shared = <<<'PHP'
if (!\class_exists('IMMO_Local_Plus_Parent', \false)) {
    /**
     * Menu parent partagé — le premier appel l’enregistre, les suivants n’y touchent pas.
     */
    class IMMO_Local_Plus_Parent
    {
        public const PARENT_SLUG         = 'immolocal-modules';
        public const OPTION_BASE_URL     = 'immolocal_base_url';
        private static bool $parentReady = false;

        public static function ensureParentMenu(): void
        {
            if (self::$parentReady) {
                return;
            }
            if (\get_option(self::OPTION_BASE_URL) === \false) {
                \add_option(self::OPTION_BASE_URL, '', '', false);
            }
            \add_menu_page(
                'IMMO Local+',
                'IMMO Local+',
                'manage_options',
                self::PARENT_SLUG,
                [self::class, 'renderMenuHome'],
                'dashicons-admin-multisite',
                59
            );
            self::$parentReady = true;
        }

        public static function renderMenuHome(): void
        {
            if (!\current_user_can('manage_options')) {
                return;
            }
            echo '<div class="wrap"><h1>IMMO Local+</h1><p>Choisissez un module dans le sous-menu.</p></div>';
        }

        public static function baseAppUrl(): string
        {
            $raw = (string) \get_option(self::OPTION_BASE_URL, '');

            return \rtrim($raw, '/');
        }

        public static function moduleAppUrl(string $moduleSlug): string
        {
            $base = self::baseAppUrl();
            if ($base === '') {
                return '#';
            }

            return $base . '/admin?' . \http_build_query(['module' => $moduleSlug], '', '&', \PHP_QUERY_RFC3986);
        }

        public static function registerSubmodule(
            string $moduleSlug,
            string $menuLabel,
            string $description,
            bool $isSettings = false
        ): void {
            \add_action('admin_menu', static function () use ($moduleSlug, $menuLabel, $description, $isSettings): void {
                if (!\current_user_can('manage_options')) {
                    return;
                }
                self::ensureParentMenu();
                $pageSlug = 'immolocal-sub-' . \preg_replace('/[^a-z0-9-]/i', '-', $moduleSlug) . '-p';
                $cb       = static function () use ($moduleSlug, $menuLabel, $description, $isSettings): void {
                    if (!\current_user_can('manage_options')) {
                        return;
                    }
                    $url  = \IMMO_Local_Plus_Parent::moduleAppUrl($moduleSlug);
                    $escU = $url !== '#' ? \esc_url($url) : '#';
                    $escT = \esc_html($menuLabel);
                    $escD = \esc_html($description);
                    if ($isSettings) {
                        if (isset($_POST['immolocal_base_url'], $_POST['immolocal_nonce']) && \wp_verify_nonce(
                            (string) \wp_unslash($_POST['immolocal_nonce'] ?? ''),
                            'immolocal_settings'
                        )) {
                            $v = \untrailingslashit(\esc_url_raw(\wp_unslash((string) $_POST['immolocal_base_url'])));
                            if (!\is_string($v) || $v === '') {
                                $v = '';
                            }
                            \update_option(self::OPTION_BASE_URL, $v, false);
                            echo '<div class="updated notice is-dismissible"><p>' . \esc_html__(
                                'URL enregistrée.',
                                'immolocal-wp'
                            ) . '</p></div>';
                        }
                        $current = (string) \get_option(self::OPTION_BASE_URL, '');

                        echo '<div class="wrap">';
                        echo '<h1>' . $escT . ' — ' . \esc_html__('Réglages', 'immolocal-wp') . '</h1>';
                        echo '<p>' . $escD . '</p>';
                        echo '<h2 class="title">' . \esc_html__(
                            'URL de base de l’application (sans /admin à la fin)',
                            'immolocal-wp'
                        ) . '</h2>';
                        echo '<form method="post" action="">';
                        \wp_nonce_field('immolocal_settings', 'immolocal_nonce', true, true);
                        echo '<table class="form-table" role="presentation"><tbody><tr><th><label for="immolocal_base_url">URL</label></th><td>';
                        echo '<input name="immolocal_base_url" id="immolocal_base_url" type="url" class="large-text code" value="' . \esc_attr(
                            $current
                        ) . '" placeholder="https://votre-app.example.com" />';
                        echo '<p class="description">' . \esc_html__(
                            'Cette URL sert à tous les modules : les liens ouvrent /admin?module=…',
                            'immolocal-wp'
                        ) . '</p>';
                        echo '</td></tr></tbody></table>';
                        echo '<p class="submit"><input type="submit" class="button button-primary" value="' . \esc_attr__(
                            'Enregistrer',
                            'immolocal-wp'
                        ) . '" /></p>';
                        echo '</form></div>';
                    } else {
                        echo '<div class="wrap">';
                        echo '<h1>' . $escT . '</h1>';
                        echo '<p class="description">' . $escD . '</p>';
                        echo '<p><strong>' . \esc_html__('Lien outil (admin PHP)', 'immolocal-wp') . ' :</strong></p>';
                        if ($url === '#') {
                            echo '<p class="notice notice-warning inline"><span>' . \esc_html__(
                                'Définissez l’URL de l’appli via le module « Tableau de bord » (réglages).',
                                'immolocal-wp'
                            ) . '</span></p>';
                        } else {
                            echo '<p><a class="button button-primary" href="' . $escU . '" target="_blank" rel="noopener noreferrer">' . \esc_html__(
                                'Ouvrir dans l’application',
                                'immolocal-wp'
                            ) . '</a> <code>' . \esc_html($url) . '</code></p>';
                        }
                    }
                    echo '<p class="description"><small>module=' . \esc_html($moduleSlug) . '</small></p></div>';
                };
                \add_submenu_page(
                    self::PARENT_SLUG,
                    $menuLabel,
                    $menuLabel,
                    'manage_options',
                    $pageSlug,
                    $cb
                );
            }, 20);
        }
    }
}
PHP;

foreach ($meta as $slug => $row) {
    $name = (string) ($row['name'] ?? $slug);
    $desc = (string) ($row['description'] ?? '');

    $dirSlug  = 'immolocal-module-' . str_replace(['_', ' '], '-', strtolower($slug));
    $fileName = $dirSlug . '.php';
    $dir      = $outBase . '/' . $dirSlug;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        fwrite(STDERR, "mkdir $dir\n");
        continue;
    }

    $isDashboard = $slug === 'dashboard';

    $nameV = \var_export($name, true);
    $descV = \var_export($desc, true);
    $slugV = \var_export($slug, true);
    $setV  = $isDashboard ? 'true' : 'false';

    $php = <<<PHP
<?php
/**
 * Plugin Name: IMMO Local+ - {$slug}
 * Description: Pont WordPress / module admin IMMO ({$name}).
 * Version: 1.0.0
 * Author: Immo local team
 * Text Domain: {$dirSlug}
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;
{$shared}
\IMMO_Local_Plus_Parent::registerSubmodule(
    {$slugV},
    {$nameV},
    {$descV},
    {$setV}
);

PHP;

    file_put_contents($dir . '/' . $fileName, $php);
    echo "Wrote $dir/$fileName\n";
}

echo 'Terminé : ' . count($meta) . " plugins.\n";
