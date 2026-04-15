<?php

declare(strict_types=1);

$page = is_array($page ?? null) ? $page : [];
$sections = is_array($sections ?? null) ? $sections : [];
$sectionRenderer = $sectionRenderer ?? null;

function lp_thankyou_render_sections(array $sections, mixed $sectionRenderer, array $page): string
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

function lp_thankyou_include_section(string $name, array $context = []): void
{
    $file = __DIR__ . '/sections/' . $name . '.php';

    if (!is_file($file)) {
        return;
    }

    extract($context, EXTR_SKIP);
    require $file;
}

$pageData = json_decode((string) ($page['data_json'] ?? '{}'), true);
$pageData = is_array($pageData) ? $pageData : [];

$sectionsHtml = lp_thankyou_render_sections($sections, $sectionRenderer, $page);
$hasCmsSections = trim($sectionsHtml) !== '';

$fallbackSections = [
    'confirmation',
    'suite',
    'cta',
];

$sectionContext = [
    'page' => $page,
    'pageData' => $pageData,
];
?>

<section class="lp-page lp-page--thankyou">
    <?php if ($hasCmsSections): ?>
        <?= $sectionsHtml ?>
    <?php else: ?>
        <?php foreach ($fallbackSections as $sectionName): ?>
            <?php lp_thankyou_include_section($sectionName, $sectionContext); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</section>