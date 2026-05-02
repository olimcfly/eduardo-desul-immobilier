<?php
declare(strict_types=1);

/**
 * Métadonnées d’un module d’administration (fichier d’entrée : modules/<slug>/accueil.php).
 * Fichier optionnel côté module : modules/<slug>/admin_plugin.php (retourne une instance de cette classe).
 */
final class AdminModulePlugin
{
    public const VERSION = '1.0.0';

    private const META_FILE = 'config/admin_module_plugin_meta.php';

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $metaCache = null;

    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $version = self::VERSION,
        public bool $inSidebar = false,
        public ?string $menuGroup = null,
        public ?string $menuLabel = null,
        public ?string $requiredRole = null,
        public array $aliases = [],
        public ?string $entryFile = 'accueil.php',
    ) {
    }

    public function adminUrl(): string
    {
        return '/admin?module=' . rawurlencode($this->slug);
    }

    /**
     * Résout le plugin à partir d’un chemin de dossier de module (…/modules/<slug>).
     */
    public static function fromModuleDir(string $moduleDir, ?string $rootPath = null): self
    {
        $rootPath = $rootPath ?? self::detectRoot($moduleDir);
        $slug = basename(rtrim($moduleDir, '/'));
        if (!is_file($moduleDir . '/accueil.php')) {
            throw new RuntimeException("Module admin invalide (accueil manquant) : {$slug}");
        }

        $row = self::metaRow($slug, $rootPath);

        return new self(
            $slug,
            (string) ($row['name'] ?? $slug),
            (string) ($row['description'] ?? ''),
            self::VERSION,
            (bool) ($row['in_sidebar'] ?? false),
            isset($row['menu_group']) ? (string) $row['menu_group'] : null,
            isset($row['menu_label']) ? (string) $row['menu_label'] : null,
            isset($row['required_role']) && $row['required_role'] !== ''
                ? (string) $row['required_role']
                : null,
            is_array($row['aliases'] ?? null) ? $row['aliases'] : [],
            'accueil.php',
        );
    }

    public static function tryFromModuleDir(string $moduleDir, ?string $rootPath = null): ?self
    {
        try {
            return self::fromModuleDir($moduleDir, $rootPath);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug'            => $this->slug,
            'name'            => $this->name,
            'description'     => $this->description,
            'version'         => $this->version,
            'in_sidebar'      => $this->inSidebar,
            'menu_group'      => $this->menuGroup,
            'menu_label'      => $this->menuLabel,
            'required_role'   => $this->requiredRole,
            'aliases'         => $this->aliases,
            'entry_file'      => $this->entryFile,
            'admin_url'       => $this->adminUrl(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function loadAllMeta(string $rootPath): array
    {
        if (self::$metaCache !== null) {
            return self::$metaCache;
        }
        $path = rtrim($rootPath, '/') . '/' . self::META_FILE;
        if (!is_file($path)) {
            return self::$metaCache = [];
        }
        /** @var array<string, array<string, mixed>> $data */
        $data = require $path;

        return self::$metaCache = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function metaRow(string $slug, string $rootPath): array
    {
        $all = self::loadAllMeta($rootPath);
        if (isset($all[$slug])) {
            return $all[$slug];
        }

        return [
            'name'         => str_replace(['-', '_'], ' ', $slug),
            'description'  => 'Module d’administration',
            'in_sidebar'   => false,
            'menu_group'   => null,
            'menu_label'   => null,
            'required_role'=> null,
            'aliases'      => [],
        ];
    }

    public static function resetMetaCache(): void
    {
        self::$metaCache = null;
    }

    private static function detectRoot(string $moduleDir): string
    {
        $p = rtrim($moduleDir, '/');
        // …/project/modules/slug
        $maybe = dirname($p, 2);

        return is_file($maybe . '/' . self::META_FILE) ? $maybe : (defined('ROOT_PATH') ? (string) ROOT_PATH : $maybe);
    }
}
