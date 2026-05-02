<?php
declare(strict_types=1);

/**
 * Génère config/page_content_registry.php à partir des fichiers sous public/pages/ (récursif).
 * Extrait les chaînes par défaut (?? "...") et les titres SEO directs.
 *
 * Usage : php scripts/generate_page_content_registry.php
 */

$root = dirname(__DIR__);
$pagesDir = $root . '/public/pages';
$outFile = $root . '/config/page_content_registry.php';

/** @return array<string, string> template => slug (sans DB, depuis les migrations SQL) */
function loadTemplateSlugMapFromMigrations(string $root): array
{
    $map = [];
    $glob = glob($root . '/database/migrations/*.sql') ?: [];
    foreach ($glob as $sqlFile) {
        $sql = (string) file_get_contents($sqlFile);
        if ($sql === '') {
            continue;
        }
        if (preg_match_all(
            "/UPDATE\\s+`cms_pages`\\s+SET\\s+`template`\\s*=\\s*'([^']+)'[^;]*?WHERE\\s+`slug`\\s*=\\s*'([^']+)'/is",
            $sql,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $row) {
                $map[trim($row[1], '/')] = $row[2];
            }
        }
    }
    // INSERT IGNORE multi-rows : (site_id, 'slug', 'Title', 'pages/...', ...)
    foreach ($glob as $sqlFile) {
        $sql = (string) file_get_contents($sqlFile);
        if (!str_contains($sql, 'cms_pages') || !str_contains($sql, 'INSERT')) {
            continue;
        }
        if (preg_match_all(
            "/\\(\\s*\\d+\\s*,\\s*'([^']+)'\s*,\s*'(?:[^']|'')*'\s*,\s*'([^']+)'\s*,/m",
            $sql,
            $ins,
            PREG_SET_ORDER
        )) {
            foreach ($ins as $row) {
                $slug = $row[1];
                $template = trim($row[2], '/');
                if ($template !== '' && str_starts_with($template, 'pages/')) {
                    $map[$template] = $slug;
                }
            }
        }
    }

    return $map;
}

/** Slug stable par fichier : « pages-core-contact » (évite collisions entre routes CMS et doublons de chemins). */
function slugForTemplate(string $template): string
{
    return str_replace('/', '-', trim($template, '/'));
}

function labelFromField(string $name): string
{
    $n = preg_replace('/_+/', ' ', $name) ?? $name;
    return $n === '' ? $name : (mb_strtoupper(mb_substr($n, 0, 1)) . mb_substr($n, 1));
}

/**
 * @param array<string, array{title: string, fields: array<string, mixed>}> $sections
 */
function registryEnsureSection(array &$sections, string $secKey): void
{
    if (isset($sections[$secKey])) {
        return;
    }
    $title = match ($secKey) {
        'seo' => 'SEO & métadonnées',
        'content' => 'Contenu affiché',
        default => 'Bloc : ' . $secKey,
    };
    $sections[$secKey] = ['title' => $title, 'fields' => []];
}

/**
 * pcms('section', 'field', "default") — aligné sur le rendu front (admin = même texte par défaut).
 *
 * @param array<string, array{title: string, fields: array<string, mixed>}> $sections
 */
function extractPcmsCalls(string $php, array &$sections): void
{
    $patterns = [
        '/\bpcms\s*\(\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*,\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*,\s*"((?:[^"\\\\]|\\\\.)*)"\s*\)/s',
        '/\bpcms\s*\(\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*,\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*,\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*\)/s',
    ];
    foreach ($patterns as $re) {
        if (!preg_match_all($re, $php, $matches, PREG_SET_ORDER)) {
            continue;
        }
        foreach ($matches as $m) {
            $sec = $m[1];
            $field = $m[2];
            $def = stripcslashes($m[3] ?? '');
            registryEnsureSection($sections, $sec);
            $type = (str_contains($def, "\n") || mb_strlen($def) > 160) ? 'textarea' : 'text';
            if ($field === 'meta_description' || str_contains($field, 'description')) {
                $type = 'textarea';
            }
            if (!isset($sections[$sec]['fields'][$field])) {
                $sections[$sec]['fields'][$field] = [
                    'type' => $type,
                    'label' => labelFromField(str_replace('_', ' ', $field)),
                    'default' => $def,
                ];
            }
        }
    }
}

/**
 * $siteSettings['cle'] ?? "texte" — évite les faux positifs ($_POST['x'] ?? '').
 *
 * @param array<string, array{title: string, fields: array<string, mixed>}> $sections
 */
function extractSiteSettingsDefaults(string $php, array &$sections): void
{
    $reD = '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=\s*\$siteSettings\[\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*\]\s*\?\?\s*"((?:[^"\\\\]|\\\\.)*)"\s*;/u';
    $reS = '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=\s*\$siteSettings\[\s*[\'"]([a-zA-Z0-9_-]+)[\'"]\s*\]\s*\?\?\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*;/u';
    foreach ([$reD, $reS] as $re) {
        if (!preg_match_all($re, $php, $matches, PREG_SET_ORDER)) {
            continue;
        }
        foreach ($matches as $m) {
            $field = $m[1];
            $def = stripcslashes($m[2] ?? '');
            if ($def === '') {
                continue;
            }
            registryEnsureSection($sections, 'content');
            if (isset($sections['content']['fields'][$field])) {
                continue;
            }
            $type = (str_contains($def, "\n") || mb_strlen($def) > 160) ? 'textarea' : 'text';
            $sections['content']['fields'][$field] = [
                'type' => $type,
                'label' => labelFromField(str_replace('_', ' ', $field)),
                'default' => $def,
            ];
        }
    }
}

/**
 * @return array<string, array{title: string, fields: array<string, array{type: string, label: string, default: string}>}>
 */
function extractSections(string $php): array
{
    $sections = [
        'seo' => [
            'title' => 'SEO & métadonnées',
            'fields' => [],
        ],
        'content' => [
            'title' => 'Contenu affiché',
            'fields' => [],
        ],
    ];

    extractPcmsCalls($php, $sections);
    extractSiteSettingsDefaults($php, $sections);

    if (!isset($sections['seo']['fields']['page_title'])) {
        if (preg_match('/\$pageTitle\s*=\s*"((?:[^"\\\\]|\\\\.)*)"\s*;/u', $php, $m)) {
            $sections['seo']['fields']['page_title'] = [
                'type' => 'text',
                'label' => 'Titre de la page (balise title)',
                'default' => stripcslashes($m[1]),
            ];
        } elseif (preg_match('/\$pageTitle\s*=\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*;/u', $php, $m)) {
            $sections['seo']['fields']['page_title'] = [
                'type' => 'text',
                'label' => 'Titre de la page (balise title)',
                'default' => stripcslashes($m[1]),
            ];
        }
    }

    if (!isset($sections['seo']['fields']['meta_description'])) {
        if (preg_match('/\$metaDesc\s*=\s*"((?:[^"\\\\]|\\\\.)*)"\s*;/u', $php, $m)) {
            $sections['seo']['fields']['meta_description'] = [
                'type' => 'textarea',
                'label' => 'Meta description',
                'default' => stripcslashes($m[1]),
            ];
        } elseif (preg_match('/\$metaDesc\s*=\s*"([^"]*)"\s*;/u', $php, $m)) {
            $sections['seo']['fields']['meta_description'] = [
                'type' => 'textarea',
                'label' => 'Meta description',
                'default' => $m[1],
            ];
        } elseif (preg_match('/\$metaDesc\s*=\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*;/u', $php, $m)) {
            $sections['seo']['fields']['meta_description'] = [
                'type' => 'textarea',
                'label' => 'Meta description',
                'default' => stripcslashes($m[1]),
            ];
        }
    }

    foreach (array_keys($sections) as $k) {
        if ($sections[$k]['fields'] === []) {
            unset($sections[$k]);
        }
    }

    return $sections;
}

$templateRouteSlugMap = loadTemplateSlugMapFromMigrations($root); // slug URL cms_pages si présent

require_once $root . '/modules/cms/cms_registry_helpers.inc.php';

$registry = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pagesDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }
    $full = $file->getPathname();
    $rel = substr($full, strlen($pagesDir) + 1);
    $rel = str_replace('\\', '/', $rel);
    $template = 'pages/' . preg_replace('/\.php$/i', '', $rel);
    if ($template === 'pages/page' || str_contains($rel, '/config/')) {
        continue;
    }
    $slug = slugForTemplate($template);
    $php = (string) file_get_contents($full);
    $sections = extractSections($php);
    if ($sections === []) {
        // Aucun $pageTitle / $metaDesc / ?? "default" détecté : on enregistre quand même le gabarit
        // avec des champs vides pour pouvoir l’éditer dans le CMS (sinon ~la moitié des fichiers étaient ignorés).
        $sections = [
            'seo' => [
                'title' => 'SEO & métadonnées',
                'fields' => [
                    'page_title' => [
                        'type' => 'text',
                        'label' => 'Titre de la page (balise title)',
                        'default' => '',
                    ],
                    'meta_description' => [
                        'type' => 'textarea',
                        'label' => 'Meta description',
                        'default' => '',
                    ],
                ],
            ],
        ];
    }

    $niceTitle = str_replace(['/', '-'], [' › ', ' — '], preg_replace('#^pages/#', '', $template));
    $row = [
        'label' => $niceTitle,
        'template' => $template,
        'route_slug' => $templateRouteSlugMap[$template] ?? null,
        'sections' => $sections,
    ];
    $row['tier'] = cms_registry_entry_is_secondary($slug, $row) ? 'secondary' : 'primary';
    $registry[$slug] = $row;
}

ksort($registry, SORT_STRING);

$exported = var_export($registry, true);
$header = <<<PHP
<?php
declare(strict_types=1);

/**
 * Registre des champs CMS par page (généré par scripts/generate_page_content_registry.php).
 * Chaque slug correspond à page_contents.page_slug (ou au slug cms_pages quand mappé).
 */

return {$exported};

PHP;

file_put_contents($outFile, $header);
echo "Written {$outFile} (" . count($registry) . " pages)\n";
