<?php
declare(strict_types=1);

/**
 * Découverte minimale des gabarits publics pour la fusion CMS.
 * Version volontairement neutre : aucune valeur conseiller/ville en dur.
 */
final class CmsPageDiscovery
{
    /**
     * Convertit un chemin de template en slug CMS.
     */
    public static function templateToSlug(string $template): string
    {
        $template = trim($template, '/');
        $template = preg_replace('#^pages/#', '', $template) ?? $template;

        if ($template === 'core/home') {
            return 'home';
        }
        if ($template === 'services/services') {
            return 'services';
        }

        return str_replace('/', '-', $template);
    }

    /**
     * Slug CMS pour une page guide local dynamique.
     */
    public static function guideLocalCmsSlug(string $secteurSlug): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', $secteurSlug) ?? '');

        return 'guide-local-ville-' . ($slug !== '' ? $slug : 'default');
    }

    /**
     * Liste les clés éditables détectées dans un template PHP.
     *
     * @return list<string>
     */
    public static function editableKeysForFile(string $absolutePhpPath): array
    {
        if (!is_file($absolutePhpPath)) {
            return [];
        }

        $source = (string) file_get_contents($absolutePhpPath);
        $keys = [];

        if (preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(?:[\'\"]|<<<)/', $source, $matches)) {
            foreach ($matches[1] as $name) {
                if (self::isEditableVar($name)) {
                    $keys[] = $name;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return list<string>
     */
    public static function villeOverlayKeys(): array
    {
        return [
            'nom', 'type', 'prix', 'tendance', 'delai', 'description', 'marche', 'metaDesc', 'image',
        ];
    }

    /**
     * @return list<string>
     */
    public static function guideSecteurOverlayKeys(): array
    {
        return [
            'nom', 'desc', 'marche', 'transports', 'commerces', 'prix', 'tendance', 'delai', 'img', 'img_credit', 'biens',
        ];
    }

    private static function isEditableVar(string $name): bool
    {
        if (preg_match('/(Settings|settings|Error|error|Path|path|Url|url|Href|href|Token|token|Time|time|Count|count|Id|ID|Phone|Email|Image)$/i', $name)) {
            return false;
        }

        return (bool) preg_match('/^(pageTitle|metaDesc|meta_description|metaKeywords|pageContent|intro|subtitle|title|hero|lead|description|heading|label|text|body|cta|section)/i', $name)
            || (bool) preg_match('/^(contact|about|service|legal|faq|merci|territory|speciality|years|approach|icon|value|card|stat|pillar)/i', $name);
    }
}
