<?php
declare(strict_types=1);

/**
 * Navigation admin canonique.
 *
 * La sidebar, les états actifs et les droits de visibilité doivent venir des
 * métadonnées de modules, pas d'une seconde carte locale dans la vue.
 */
final class AdminNavigation
{
    private const DEFAULT_ICON = 'fas fa-circle';

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function sidebarGroups(string $rootPath, string $currentModule, ?array $user = null): array
    {
        require_once rtrim($rootPath, '/') . '/core/AdminModulePlugin.php';

        $rootPath = rtrim($rootPath, '/');
        $meta = AdminModulePlugin::loadAllMeta($rootPath);
        $role = (string) ($user['role'] ?? '');
        $items = [];

        foreach ($meta as $slug => $row) {
            if (($row['in_sidebar'] ?? false) !== true) {
                continue;
            }

            $requiredRole = isset($row['required_role']) && $row['required_role'] !== ''
                ? (string) $row['required_role']
                : null;
            if ($requiredRole !== null && $role !== $requiredRole) {
                continue;
            }

            if (!is_file($rootPath . '/modules/' . $slug . '/accueil.php')) {
                continue;
            }

            $group = (string) ($row['menu_group'] ?? '');
            if ($group === '') {
                continue;
            }

            $aliases = is_array($row['aliases'] ?? null) ? $row['aliases'] : [];
            $items[] = [
                'module' => (string) $slug,
                'label' => (string) ($row['menu_label'] ?? $row['name'] ?? $slug),
                'title' => (string) ($row['menu_title'] ?? $row['description'] ?? $row['menu_label'] ?? $row['name'] ?? $slug),
                'icon' => (string) ($row['icon'] ?? self::DEFAULT_ICON),
                'group' => $group,
                'order' => (int) ($row['menu_order'] ?? 9990),
                'url' => '/admin?module=' . rawurlencode((string) $slug),
                'aliases' => $aliases,
                'active' => $currentModule === $slug || in_array($currentModule, $aliases, true),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            return ($a['order'] <=> $b['order'])
                ?: strnatcasecmp((string) $a['label'], (string) $b['label']);
        });

        $groups = [];
        foreach ($items as $item) {
            $group = (string) $item['group'];
            unset($item['group']);
            $groups[$group][] = $item;
        }

        return $groups;
    }
}
