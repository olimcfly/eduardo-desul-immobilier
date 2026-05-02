<?php
declare(strict_types=1);
if (!defined('ABSPATH')) {
    exit;
}
?>
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        <p class="site-footer__brand">
            <?php echo esc_html(get_bloginfo('name')); ?>
        </p>

        <nav aria-label="<?php esc_attr_e('Footer menu', 'beckhamm-immo'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container' => false,
                'menu_class' => 'site-footer__menu',
                'fallback_cb' => false,
            ]);
            ?>
        </nav>

        <p class="site-footer__copy">
            &copy; <?php echo esc_html((string) date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>
        </p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
