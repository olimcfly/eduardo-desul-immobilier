<?php

declare(strict_types=1);

/**
 * Template LP - Étape 1 : Landing / Qualification
 *
 * Compatible avec :
 * - rendu CMS via $sections + $sectionRenderer
 * - fallback sur sections physiques /lp/sections/*.php
 *
 * Variables attendues :
 * @var array<string, mixed> $page
 * @var array<int, array<string, mixed>> $sections
 * @var object|null $sectionRenderer
 */

$page = is_array($page ?? null) ? $page : [];
$sections = is_array($sections ?? null) ? $sections : [];
$sectionRenderer = $sectionRenderer ?? null;

function lp_value(array $data, string $key, mixed $default = null): mixed
{
    return array_key_exists($key, $data) ? $data[$key] : $default;
}

function lp_string(array $data, string $key, string $default = ''): string
{
    $value = lp_value($data, $key, $default);

    return is_scalar($value) ? trim((string) $value) : $default;
}

function lp_array(array $data, string $key): array
{
    $value = lp_value($data, $key, []);

    return is_array($value) ? $value : [];
}

function lp_render_sections(array $sections, mixed $sectionRenderer): string
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

        $html .= (string) $sectionRenderer->render($section);
    }

    return $html;
}

function lp_include_section(string $name, array $context = []): void
{
    $file = __DIR__ . '/sections/' . $name . '.php';

    if (!is_file($file)) {
        return;
    }

    extract($context, EXTR_SKIP);
    require $file;
}

$title = lp_string($page, 'meta_title', lp_string($page, 'title', 'Landing page'));
$metaDescription = lp_string($page, 'meta_description', lp_string($page, 'excerpt', ''));
$slug = lp_string($page, 'slug', 'neuroscript-landing');
$pageData = lp_array($page, 'data_json');
$heroImage = lp_string($pageData, 'hero_image');
$formUrl = lp_string($pageData, 'form_url', '/' . $slug . '/form');
$thankYouUrl = lp_string($pageData, 'thankyou_url', '/' . $slug . '/merci');

$sectionsHtml = lp_render_sections($sections, $sectionRenderer);
$hasCmsSections = trim($sectionsHtml) !== '';

/**
 * Fallback local des sections si rien n'est fourni par le CMS.
 * Ordre recommandé pour la LP étape 1.
 */
$fallbackSections = [
    'hero',
    'accroches',
    'problemes',
    'enjeux',
    'motivations',
    'impact',
    'arguments',
    'transformation',
    'projection',
    'plan',
    'reassurance',
    'guide',
    'cta',
];

$sectionContext = [
    'page' => $page,
    'pageData' => $pageData,
    'title' => $title,
    'metaDescription' => $metaDescription,
    'slug' => $slug,
    'heroImage' => $heroImage,
    'formUrl' => $formUrl,
    'thankYouUrl' => $thankYouUrl,
];
?>

<section class="lp-page lp-page--landing" data-template="lp/neuroscript-landing" data-slug="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($hasCmsSections): ?>
        <?= $sectionsHtml ?>
    <?php else: ?>
        <?php foreach ($fallbackSections as $sectionName): ?>
            <?php lp_include_section($sectionName, $sectionContext); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</section>