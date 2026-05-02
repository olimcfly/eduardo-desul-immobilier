<?php
declare(strict_types=1);
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<span class="site-brand__name">' . esc_html(get_bloginfo('name')) . '</span>';
            }
            ?>
        </a>

        <nav class="site-nav" aria-label="<?php esc_attr_e('Main menu', 'beckhamm-immo'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'site-nav__menu',
                'fallback_cb' => false,
            ]);
            ?>
        </nav>
    </div>
</header>

<main class="site-main">
