<?php

class PlaceholderReplacementService
{
    /**
     * @param array<string,string> $variables
     */
    public function replaceInDirectory(string $directory, array $variables): void
    {
        $allowedExtensions = ['php', 'env', 'txt', 'json', 'md', 'html', 'js', 'css', 'ini', 'yml', 'yaml'];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                continue;
            }

            $path = $fileInfo->getPathname();
            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $updated = $content;
            foreach ($variables as $key => $value) {
                $tokenA = '{{' . strtoupper($key) . '}}';
                $tokenB = '__' . strtoupper($key) . '__';
                $updated = str_replace([$tokenA, $tokenB], $value, $updated);
            }

            if ($updated !== $content) {
                file_put_contents($path, $updated);
            }
        }
    }
}
