<?php

declare(strict_types=1);

/**
 * Router de rendu CMS.
 *
 * Compatible avec :
 * - /public/templates/pages/*.php
 * - /public/templates/lp/*.php
 *
 * Variables attendues :
 * @var array<string, mixed> $page
 * @var array<int, array<string, mixed>> $sections
 * @var object|null $sectionRenderer
 */

$page = is_array($page ?? null) ? $page : [];
$sections = is_array($sections ?? null) ? $sections : [];
$sectionRenderer = $sectionRenderer ?? new \App\Core\SectionRenderer();

if (!empty($page['id'])) {

    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT *
        FROM cms_sections
        WHERE page_id = :page_id
        AND is_active = 1
        ORDER BY sort_order ASC
    ");

    $stmt->execute([
        'page_id' => $page['id']
    ]);

    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function tpl_value(array $data, string $key, mixed $default = null): mixed
{
    return array_key_exists($key, $data) ? $data[$key] : $default;
}

function tpl_string(array $data, string $key, string $default = ''): string
{
    $value = tpl_value($data, $key, $default);

    return is_scalar($value) ? trim((string) $value) : $default;
}

function tpl_normalize(string $template): string
{
    $template = trim($template);
    $template = str_replace('\\', '/', $template);
    $template = preg_replace('#/+#', '/', $template) ?? $template;

    return trim($template, '/');
}

function tpl_resolve(string $template): ?string
{
    $template = tpl_normalize($template);

    if ($template === '' || str_contains($template, '..')) {
        return null;
    }

    $fullPath = __DIR__ . '/' . $template . '.php';

    return is_file($fullPath) ? $fullPath : null;
}

function tpl_render_sections(array $sections, mixed $sectionRenderer, array $page = []): string
{
    if ($sections === []) {
        return '';
    }

    if (!is_object($sectionRenderer) || !method_exists($sectionRenderer, 'render')) {
        return '';
    }

    $html = '';

    foreach ($sections as $section) {
        if (!is_array($section)) {
            continue;
        }

        $html .= (string) $sectionRenderer->render($section, $page);
    }

    return $html;
}

$template = tpl_string($page, 'template');
$slug = tpl_string($page, 'slug');
$pageType = tpl_string($page, 'page_type');
$pageKind = tpl_string($page, 'kind');

$templateFile = null;

// 1. priorité au template explicite stocké en base
if ($template !== '') {
    $templateFile = tpl_resolve($template);
}

// 2. fallback intelligent si template vide ou introuvable
if ($templateFile === null && $slug !== '') {
    if ($pageType === 'lp' || $pageKind === 'lp') {
        $templateFile = tpl_resolve('lp/' . $slug);
    } else {
        $templateFile = tpl_resolve('pages/' . $slug);
    }
}

// 3. rendu sections CMS
$sectionsHtml = tpl_render_sections($sections, $sectionRenderer, $page);
// 4. rendu final
if ($templateFile !== null) {
    require $templateFile;
    return;
}

// 🔥 fallback direct HOME
$homeTemplate = tpl_resolve('pages/home');
if ($homeTemplate !== null) {
    require $homeTemplate;
    return;
}

// fallback sections
if ($sectionsHtml !== '') {
    echo $sectionsHtml;
    return;
}

// 5. fallback par univers
if ($pageType === 'lp' || $pageKind === 'lp') {
    $lpDefault = tpl_resolve('lp/neuroscript-landing');
    if ($lpDefault !== null) {
        require $lpDefault;
        return;
    }
}

$defaultPage = tpl_resolve('pages/default');
if ($defaultPage !== null) {
    require $defaultPage;
    return;
}

// 6. dernier fallback
http_response_code(404);
$notFound = tpl_resolve('pages/404');
if ($notFound !== null) {
    require $notFound;
    return;
}
?>

<section class="container" style="padding:40px 20px;">
    <h1>404</h1>
    <p>Aucun template trouvé pour cette page.</p>
</section>