<?php

declare(strict_types=1);

if (!function_exists('get_page_content')) {
    /**
     * Retourne les contenus CMS d'une section de page.
     */
    function get_page_content(string $page, string $section): array
    {
        $prefix = 'cms_' . trim($page) . '_' . trim($section) . '_';

        return [
            'title' => (string) setting($prefix . 'title', ''),
            'subtitle' => (string) setting($prefix . 'subtitle', ''),
        ];
    }
}
