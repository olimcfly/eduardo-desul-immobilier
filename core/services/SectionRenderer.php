<?php

declare(strict_types=1);

namespace App\Core;

final class SectionRenderer
{
    private string $templatesBasePath;

    public function __construct(?string $templatesBasePath = null)
    {
        $this->templatesBasePath = rtrim(
            $templatesBasePath ?? dirname(__DIR__, 2) . '/public/templates',
            '/'
        );
    }

    /**
     * @param array<string, mixed> $section
     * @param array<string, mixed> $page
     */
    public function render(array $section, array $page = []): string
    {
        $sectionKey = $this->string($section['section_key'] ?? '');
        $sectionType = $this->string($section['section_type'] ?? '');
        $pageType = $this->string($page['page_type'] ?? ($section['page_type'] ?? 'page'));

        if ($sectionKey === '' && $sectionType === '') {
            return '';
        }

        $file = $this->resolveSectionFile($pageType, $sectionKey, $sectionType);

        if ($file === null) {
            return '';
        }

        $sectionData = $this->decodeJsonToArray($section['data_json'] ?? null);
        $pageData = $this->decodeJsonToArray($page['data_json'] ?? null);

        ob_start();

        $section = $section;
        $page = $page;
        $data = $sectionData;
        $pageData = $pageData;

        require $file;

        return (string) ob_get_clean();
    }

    /**
     * @param array<int, array<string, mixed>> $sections
     * @param array<string, mixed> $page
     */
    public function renderMany(array $sections, array $page = []): string
    {
        $html = '';

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $html .= $this->render($section, $page);
        }

        return $html;
    }

    private function resolveSectionFile(string $pageType, string $sectionKey, string $sectionType): ?string
    {
        $candidates = [];

        if ($pageType === 'lp') {
            $candidates[] = $this->templatesBasePath . '/lp/sections/' . $sectionKey . '.php';
            $candidates[] = $this->templatesBasePath . '/lp/sections/' . $sectionType . '.php';
        }

        $candidates[] = $this->templatesBasePath . '/pages/sections/' . $sectionKey . '.php';
        $candidates[] = $this->templatesBasePath . '/pages/sections/' . $sectionType . '.php';

        foreach ($candidates as $file) {
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}