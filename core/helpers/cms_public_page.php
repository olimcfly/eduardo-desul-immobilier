<?php
declare(strict_types=1);

/**
 * Fusionne le contenu CMS publié avec des clés de template.
 *
 * @param array<string, string> $overlay valeurs déjà calculées côté template
 * @return array<string, string>|null
 */
function cms_public_merge(string $slug, array $overlay = []): ?array
{
    if (!function_exists('db')) {
        return null;
    }

    try {
        $st = db()->prepare(
            'SELECT status, meta_title, meta_description, og_image_url, data_json FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1'
        );
        $st->execute([$slug]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return null;
    }

    if (!$row || ($row['status'] ?? '') !== 'published') {
        return null;
    }

    $out = $overlay;
    $data = json_decode((string) ($row['data_json'] ?? ''), true);
    $sections = is_array($data) ? ($data['sections'] ?? []) : [];
    if (!is_array($sections)) {
        $sections = [];
    }

    if (!empty($row['meta_title'])) {
        $out['pageTitle'] = (string) $row['meta_title'];
    }
    if (!empty($row['meta_description'])) {
        $out['metaDesc'] = (string) $row['meta_description'];
    }
    if (!empty($row['og_image_url'])) {
        $out['ogImage'] = (string) $row['og_image_url'];
    }

    foreach ($sections as $key => $value) {
        if (!is_string($key) || $key === '') {
            continue;
        }
        $out[$key] = (string) $value;
    }

    return $out;
}

/**
 * @param list<string> $keys
 */
function cms_public_apply(string $slug, array &$ref, array $keys): void
{
    $pick = [];
    foreach ($keys as $key) {
        if (array_key_exists($key, $ref)) {
            $pick[$key] = (string) $ref[$key];
        }
    }

    $merged = cms_public_merge($slug, $pick);
    if ($merged === null) {
        return;
    }

    foreach ($keys as $key) {
        if (isset($merged[$key])) {
            $ref[$key] = $merged[$key];
        }
    }
}

function cms_public_resolve_slug(string $template, array $templateVars): string
{
    if ($template === 'pages/services/services') {
        return 'services';
    }

    $slug = CmsPageDiscovery::templateToSlug($template);
    if ($template !== 'pages/guide-local/ville') {
        return $slug;
    }

    $sectorSlug = $templateVars['slug'] ?? null;
    if (is_string($sectorSlug) && $sectorSlug !== '') {
        return CmsPageDiscovery::guideLocalCmsSlug($sectorSlug);
    }

    return $slug;
}

/**
 * @param array<string, mixed> $assoc
 * @param list<string> $whitelist
 */
function cms_public_apply_publish_sections_to_assoc(string $slug, array &$assoc, array $whitelist): void
{
    $merged = cms_public_merge($slug, []);
    if ($merged === null) {
        return;
    }

    foreach ($whitelist as $key) {
        if (array_key_exists($key, $merged)) {
            $assoc[$key] = (string) $merged[$key];
        }
    }
}

function cms_public_detect_zone_cms_slug(): ?string
{
    foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16) as $frame) {
        $file = (string) ($frame['file'] ?? '');
        if (str_ends_with($file, '/_ville-secteur.php')) {
            continue;
        }
        if (preg_match('#/public/pages/zones/(villes|quartiers)/([^/]+)\.php$#', $file, $m)) {
            return CmsPageDiscovery::templateToSlug('pages/zones/' . $m[1] . '/' . $m[2]);
        }
    }

    return null;
}
