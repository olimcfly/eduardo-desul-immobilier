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

if (!function_exists('page_content_registry')) {
    /** @return array<string, mixed> */
    function page_content_registry(): array
    {
        static $reg = null;
        if ($reg !== null) {
            return $reg;
        }
        $path = dirname(__DIR__, 2) . '/config/page_content_registry.php';
        if (!is_file($path)) {
            return $reg = [];
        }
        $loaded = require $path;

        return $reg = is_array($loaded) ? $loaded : [];
    }
}

if (!function_exists('pcms')) {
    /**
     * Texte éditable (table page_contents), avec repli sur la valeur par défaut du template.
     * Nécessite $GLOBALS['__cms_page_text_slug'] (défini par public/index.php page()).
     * Champs enrichis (admin) : afficher sans htmlspecialchars — utiliser pcms() dans une balise PHP d’écho, pas e(pcms()).
     */
    function pcms(string $section, string $field, string $default = ''): string
    {
        $slug = (string) ($GLOBALS['__cms_page_text_slug'] ?? '');
        if ($slug === '') {
            return function_exists('replacePlaceholders') ? replacePlaceholders($default) : $default;
        }
        try {
            $val = PageContentService::getField($slug, $section, $field);
        } catch (Throwable) {
            $val = null;
        }
        if ($val === null || $val === '') {
            return function_exists('replacePlaceholders') ? replacePlaceholders($default) : $default;
        }

        return function_exists('replacePlaceholders') ? replacePlaceholders($val) : $val;
    }
}
