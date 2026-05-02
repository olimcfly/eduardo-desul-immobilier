<?php
declare(strict_types=1);

/**
 * Contenus éditables par page (table page_contents).
 * Types : text, textarea, richtext — pas de stockage JSON pour les champs éditoriaux.
 */
final class PageContentService
{
    /** @var array<string, array<string, array<string, string>>> */
    private static array $pageDataCache = [];

    /** Identifiant stable aligné sur config/page_content_registry.php (ex. pages-core-contact). */
    public static function slugFromTemplate(string $template): string
    {
        $template = str_replace('\\', '/', trim($template, '/'));

        return str_replace('/', '-', $template);
    }

    /** @return array<string, array<string, string>> section => field => value */
    public static function getPageContent(string $pageSlug): array
    {
        if (isset(self::$pageDataCache[$pageSlug])) {
            return self::$pageDataCache[$pageSlug];
        }

        $out = [];
        try {
            // Pas de ORDER BY sort_order : les schémas sur serveur peuvent ne pas avoir cette colonne.
            $stmt = db()->prepare(
                'SELECT section_name, field_name, field_value, field_type
                 FROM page_contents
                 WHERE page_slug = ?
                 ORDER BY section_name ASC, field_name ASC'
            );
            $stmt->execute([$pageSlug]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $sec = (string) ($row['section_name'] ?? 'main');
                $field = (string) ($row['field_name'] ?? '');
                if ($field === '') {
                    continue;
                }
                $val = $row['field_value'];
                $out[$sec][$field] = $val === null ? '' : (string) $val;
            }
        } catch (Throwable) {
            $out = [];
        }

        self::$pageDataCache[$pageSlug] = $out;
        return $out;
    }

    public static function getField(string $pageSlug, string $section, string $field): ?string
    {
        $data = self::getPageContent($pageSlug);
        if (!isset($data[$section][$field])) {
            return null;
        }
        $v = $data[$section][$field];
        return $v === '' ? null : $v;
    }

    /**
     * @param array<string, array<string, string>> $sections
     */
    public static function savePageContent(string $pageSlug, array $sections): void
    {
        foreach ($sections as $sectionName => $fields) {
            if (!is_array($fields)) {
                continue;
            }
            $sectionName = (string) $sectionName;
            foreach ($fields as $fieldName => $fieldValue) {
                $fieldName = (string) $fieldName;
                if ($fieldName === '') {
                    continue;
                }
                if (!is_scalar($fieldValue) && $fieldValue !== null) {
                    continue;
                }
                $str = $fieldValue === null ? '' : (string) $fieldValue;
                $type = 'text';
                if (str_contains($str, "\n") || mb_strlen($str) > 190) {
                    $type = 'textarea';
                }
                if (str_contains($str, '<') && preg_match('/<[a-z][\s\S]*>/i', $str)) {
                    $type = 'richtext';
                }

                // Colonnes minimales uniquement (pas de sort_order) pour compatibilité avec les tables existantes.
                $stmt = db()->prepare(
                    'INSERT INTO page_contents (page_slug, section_name, field_name, field_value, field_type)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        field_value = VALUES(field_value),
                        field_type = VALUES(field_type)'
                );
                $stmt->execute([$pageSlug, $sectionName, $fieldName, $str, $type]);
            }
        }

        unset(self::$pageDataCache[$pageSlug]);
    }

    /**
     * Insère les valeurs par défaut du registre si aucune ligne n’existe pour cette page.
     *
     * @param array<string, array{fields?: array<string, array{type?: string, default?: string}>}> $pageDef
     */
    public static function ensureDefaults(string $pageSlug, array $pageDef): void
    {
        try {
            $chk = db()->prepare('SELECT 1 FROM page_contents WHERE page_slug = ? LIMIT 1');
            $chk->execute([$pageSlug]);
            if ($chk->fetchColumn()) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        $sections = [];
        foreach ($pageDef as $sectionKey => $sectionCfg) {
            if (!is_array($sectionCfg)) {
                continue;
            }
            $fields = $sectionCfg['fields'] ?? [];
            if (!is_array($fields)) {
                continue;
            }
            foreach ($fields as $fname => $fcfg) {
                $default = '';
                if (is_array($fcfg)) {
                    $default = (string) ($fcfg['default'] ?? '');
                }
                $sections[(string) $sectionKey][(string) $fname] = $default;
            }
        }

        if ($sections !== []) {
            self::savePageContent($pageSlug, $sections);
        }
    }

    /** @return list<array{slug: string, title: string, template: string}> */
    public static function listManagedPages(): array
    {
        $list = [];
        try {
            $stmt = db()->query(
                'SELECT slug, title, template FROM cms_pages ORDER BY page_type ASC, title ASC, slug ASC'
            );
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $list[] = [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'title' => (string) ($row['title'] ?? $row['slug'] ?? ''),
                    'template' => trim((string) ($row['template'] ?? ''), '/'),
                ];
            }
        } catch (Throwable) {
            return [];
        }

        return $list;
    }
}
