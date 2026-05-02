<?php
declare(strict_types=1);

/**
 * Registre : découverte de tous les modules admin ayant un accueil et des métadonnées.
 */
final class AdminModulePluginRegistry
{
    /**
     * @return list<AdminModulePlugin>
     */
    public static function all(string $rootPath): array
    {
        $rootPath = rtrim($rootPath, '/');
        $base = $rootPath . '/modules';
        if (!is_dir($base)) {
            return [];
        }

        $out = [];
        foreach (scandir($base) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $dir = $base . '/' . $entry;
            if (!is_dir($dir) || !is_file($dir . '/accueil.php')) {
                continue;
            }
            $p = AdminModulePlugin::tryFromModuleDir($dir, $rootPath);
            if ($p !== null) {
                $out[] = $p;
            }
        }

        usort($out, static function (AdminModulePlugin $a, AdminModulePlugin $b): int {
            return strnatcasecmp($a->name, $b->name);
        });

        return $out;
    }

    public static function forSlug(string $slug, string $rootPath): ?AdminModulePlugin
    {
        $slug = preg_replace('/[^a-z0-9_-]/i', '', $slug) ?? '';
        if ($slug === '') {
            return null;
        }
        $dir = rtrim($rootPath, '/') . '/modules/' . $slug;

        return AdminModulePlugin::tryFromModuleDir($dir, $rootPath);
    }
}
