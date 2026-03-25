<?php

class ZipExportService
{
    public function createZipFromDirectory(string $sourceDir, string $destinationZip): string
    {
        $zipDir = dirname($destinationZip);
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0775, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l’archive ZIP.');
        }

        $sourceDir = rtrim($sourceDir, '/');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $fullPath = $item->getPathname();
            $relativePath = ltrim(str_replace($sourceDir, '', $fullPath), '/');

            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($fullPath, $relativePath);
            }
        }

        $zip->close();

        return $destinationZip;
    }
}
