<?php

if (!function_exists('get_page_content')) {
    /**
     * Récupère le contenu d'une section d'une page.
     *
     * @param string $page_slug Slug de la page (ex: "home")
     * @param string $section_name Nom de la section (ex: "hero")
     * @return array<string, mixed> Tableau associatif des champs de la section
     */
    function get_page_content(string $page_slug, string $section_name): array
    {
        $db = Database::getInstance();

        $stmt = $db->prepare(
            'SELECT field_name, field_value, field_type
             FROM page_contents
             WHERE page_slug = ? AND section_name = ?'
        );
        $stmt->execute([$page_slug, $section_name]);
        $contents = $stmt->fetchAll();

        $result = [];
        foreach ($contents as $content) {
            if (($content['field_type'] ?? null) === 'repeater') {
                $decoded = json_decode((string) ($content['field_value'] ?? '[]'), true);
                $result[$content['field_name']] = is_array($decoded) ? $decoded : [];
                continue;
            }

            $result[$content['field_name']] = $content['field_value'];
        }

        return $result;
    }
}

if (!function_exists('get_setting')) {
    /**
     * Récupère un paramètre depuis la table settings.
     *
     * @param string $key Clé du paramètre (ex: "site_meta_title")
     * @return string Valeur du paramètre
     */
    function get_setting(string $key): string
    {
        $db = Database::getInstance();

        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ? AND user_id = 2');
        $stmt->execute([$key]);

        $value = $stmt->fetchColumn();

        return $value === false ? '' : (string) $value;
    }
}
