<?php
declare(strict_types=1);

if (!function_exists('get_setting')) {
    /**
     * Alias de compatibilité pour l'API CMS.
     */
    function get_setting(string $key, mixed $default = ''): mixed
    {
        if (function_exists('setting')) {
            return setting($key, $default);
        }

        return $default;
    }
}

if (!function_exists('get_page_content')) {
    /**
     * Récupère un bloc de contenu CMS stocké en JSON dans les settings.
     * Exemple de clé: cms_home_hero.
     */
    function get_page_content(string $page, string $section): array
    {
        $key = sprintf('cms_%s_%s', trim($page), trim($section));
        $raw = get_setting($key, []);

        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
