#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

$targets = [
    [
        'sourceDir' => $rootPath . '/public/assets',
        'sourceUrlPrefix' => '/assets',
        'buildDir' => $rootPath . '/public/assets/build',
        'buildUrlPrefix' => '/assets/build',
    ],
    [
        'sourceDir' => $rootPath . '/public/admin/assets',
        'sourceUrlPrefix' => '/admin/assets',
        'buildDir' => $rootPath . '/public/admin/assets/build',
        'buildUrlPrefix' => '/admin/assets/build',
    ],
    [
        'sourceDir' => $rootPath . '/modules/gmb/assets',
        'sourceUrlPrefix' => '/modules/gmb/assets',
        'buildDir' => $rootPath . '/modules/gmb/assets/build',
        'buildUrlPrefix' => '/modules/gmb/assets/build',
    ],
];

$manifest = [];
$totalBuilt = 0;

foreach ($targets as $target) {
    if (!is_dir($target['sourceDir'])) {
        continue;
    }

    if (!is_dir($target['buildDir'])) {
        mkdir($target['buildDir'], 0775, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target['sourceDir'], FilesystemIterator::SKIP_DOTS)
    );

    $generatedForTarget = [];

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $sourcePath = $fileInfo->getPathname();
        $extension = strtolower($fileInfo->getExtension());

        if (!in_array($extension, ['css', 'js'], true)) {
            continue;
        }

        if (str_contains($sourcePath, DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $basename = $fileInfo->getBasename();
        if (str_contains($basename, '.min.')) {
            continue;
        }

        $relativePath = ltrim(substr($sourcePath, strlen($target['sourceDir'])), DIRECTORY_SEPARATOR);
        if ($relativePath === '') {
            continue;
        }

        $sourceContent = file_get_contents($sourcePath);
        if ($sourceContent === false) {
            fwrite(STDERR, "⚠️ Lecture impossible: {$sourcePath}\n");
            continue;
        }

        $minifiedContent = $extension === 'css'
            ? minifyCss($sourceContent)
            : minifyJs($sourceContent);

        if ($minifiedContent === '') {
            $minifiedContent = $sourceContent;
        }

        $hash = substr(sha1($minifiedContent), 0, 12);
        $relativeDir = dirname($relativePath);
        $relativeDir = $relativeDir === '.' ? '' : $relativeDir;

        $filenameWithoutExt = pathinfo($relativePath, PATHINFO_FILENAME);
        $generatedFilename = sprintf('%s.%s.min.%s', $filenameWithoutExt, $hash, $extension);

        $outputDir = rtrim($target['buildDir'], DIRECTORY_SEPARATOR)
            . ($relativeDir === '' ? '' : DIRECTORY_SEPARATOR . $relativeDir);

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $generatedFilename;
        file_put_contents($outputPath, $minifiedContent);

        $sourceUrl = rtrim($target['sourceUrlPrefix'], '/') . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        $generatedRelativePath = ($relativeDir === '' ? '' : $relativeDir . '/') . $generatedFilename;
        $generatedUrl = rtrim($target['buildUrlPrefix'], '/') . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $generatedRelativePath);

        $manifest[$sourceUrl] = $generatedUrl;
        $generatedForTarget[] = $outputPath;
        $totalBuilt++;
    }

    cleanupOrphanBuildFiles($target['buildDir'], $generatedForTarget);
}

$cacheDir = $rootPath . '/storage/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

$manifestPath = $cacheDir . '/assets-manifest.json';
file_put_contents(
    $manifestPath,
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

echo "✅ Manifest généré: {$manifestPath}\n";
echo "✅ Assets traités: {$totalBuilt}\n";

function minifyCss(string $content): string
{
    $content = preg_replace('!/\*.*?\*/!s', '', $content) ?? $content;
    $content = preg_replace('/\s+/', ' ', $content) ?? $content;
    $content = preg_replace('/\s*([{}:;,>])\s*/', '$1', $content) ?? $content;
    $content = preg_replace('/;}/', '}', $content) ?? $content;
    return trim($content);
}

function minifyJs(string $content): string
{
    $content = preg_replace('#/\*.*?\*/#s', '', $content) ?? $content;
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    $lines = explode("\n", $content);
    $cleaned = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            continue;
        }
        $cleaned[] = preg_replace('/\s+/', ' ', $trimmed) ?? $trimmed;
    }

    return trim(implode("\n", $cleaned));
}

function cleanupOrphanBuildFiles(string $buildDir, array $generatedPaths): void
{
    if (!is_dir($buildDir)) {
        return;
    }

    $existingGenerated = [];
    foreach ($generatedPaths as $path) {
        $realPath = realpath($path);
        if ($realPath !== false) {
            $existingGenerated[$realPath] = true;
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($buildDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $itemPath = $item->getPathname();

        if ($item->isFile()) {
            $realPath = realpath($itemPath);
            if ($realPath === false || !isset($existingGenerated[$realPath])) {
                @unlink($itemPath);
            }
            continue;
        }

        if ($item->isDir()) {
            $entries = scandir($itemPath);
            if ($entries !== false && count($entries) === 2) {
                @rmdir($itemPath);
            }
        }
    }
}
