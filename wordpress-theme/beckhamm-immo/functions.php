<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function beckhamm_immo_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');
    add_theme_support('customize-selective-refresh-widgets');

    register_nav_menus([
        'primary' => __('Primary Menu', 'beckhamm-immo'),
        'footer' => __('Footer Menu', 'beckhamm-immo'),
    ]);
}
add_action('after_setup_theme', 'beckhamm_immo_setup');

function beckhamm_immo_enqueue_assets(): void
{
    wp_enqueue_style(
        'beckhamm-immo-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'beckhamm-immo-theme',
        get_template_directory_uri() . '/assets/css/theme.css',
        ['beckhamm-immo-style'],
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'beckhamm_immo_enqueue_assets');

function beckhamm_immo_excerpt_length(): int
{
    return 24;
}
add_filter('excerpt_length', 'beckhamm_immo_excerpt_length');

