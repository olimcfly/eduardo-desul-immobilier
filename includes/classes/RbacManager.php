<?php
/**
 * RbacManager - Role-Based Access Control Manager
 *
 * Gère les rôles, les permissions et la vérification des droits d'accès.
 *
 * @package Eduardo Desul Immobilier
 */

class RbacManager
{
    /**
     * Available roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_EDITOR = 'editor';
    const ROLE_VIEWER = 'viewer';

    /**
     * Permission levels
     */
    const PERM_VIEW = 'view';
    const PERM_CREATE = 'create';
    const PERM_EDIT = 'edit';
    const PERM_DELETE = 'delete';
    const PERM_MANAGE = 'manage';

    /**
     * Define which modules and actions each role has access to
     *
     * @var array
     */
    private static $permissions = [
        // ========== ADMIN - Full Access ==========
        self::ROLE_ADMIN => [
            // CRM Module
            'crm' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'messagerie' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE
            ],

            // Content Management
            'content_pages' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'content_articles' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'content_guide' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'content_secteurs' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'content_blog' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'content_captures' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],

            // Builder
            'builder' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],

            // Marketing
            'marketing_sequences' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'marketing_leads' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'marketing_ads' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],

            // Social & Network
            'social_linkedin' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'social_instagram' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'gmb' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'network' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],

            // SEO & Analytics
            'seo' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'analytics' => [
                self::PERM_VIEW, self::PERM_MANAGE
            ],

            // System & Admin
            'system_settings' => [
                self::PERM_VIEW, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'system_users' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'system_maintenance' => [
                self::PERM_VIEW, self::PERM_MANAGE
            ],
            'ai' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_DELETE, self::PERM_MANAGE
            ],
            'licenses' => [
                self::PERM_VIEW, self::PERM_MANAGE
            ],
        ],

        // ========== MODERATOR - Content & Marketing ==========
        self::ROLE_MODERATOR => [
            'crm' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'messagerie' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_pages' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'content_articles' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'content_guide' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_secteurs' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_blog' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'content_captures' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'marketing_sequences' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, self::PERM_MANAGE
            ],
            'marketing_leads' => [
                self::PERM_VIEW, self::PERM_EDIT
            ],
            'seo' => [
                self::PERM_VIEW, self::PERM_EDIT
            ],
            'analytics' => [
                self::PERM_VIEW
            ],
        ],

        // ========== EDITOR - Content Only ==========
        self::ROLE_EDITOR => [
            'content_pages' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_articles' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_guide' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_secteurs' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'content_blog' => [
                self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT
            ],
            'seo' => [
                self::PERM_VIEW
            ],
            'analytics' => [
                self::PERM_VIEW
            ],
        ],

        // ========== VIEWER - Read-Only Access ==========
        self::ROLE_VIEWER => [
            'analytics' => [
                self::PERM_VIEW
            ],
            'crm' => [
                self::PERM_VIEW
            ],
            'content_pages' => [
                self::PERM_VIEW
            ],
            'content_articles' => [
                self::PERM_VIEW
            ],
            'marketing_leads' => [
                self::PERM_VIEW
            ],
        ],
    ];

    /**
     * Map modules to their admin page names
     *
     * @var array
     */
    private static $moduleMap = [
        'crm' => ['contact', 'clients'],
        'messagerie' => ['messagerie'],
        'content_pages' => ['pages'],
        'content_articles' => ['articles'],
        'content_guide' => ['guide-local'],
        'content_secteurs' => ['secteurs'],
        'content_blog' => ['blog'],
        'content_captures' => ['pages-capture'],
        'builder' => ['builder'],
        'marketing_sequences' => ['sequences'],
        'marketing_leads' => ['leads'],
        'marketing_ads' => ['ads-launch'],
        'social_linkedin' => ['linkedin'],
        'social_instagram' => ['instagram'],
        'gmb' => ['gmb'],
        'network' => ['network'],
        'seo' => ['seo', 'seo-semantic'],
        'analytics' => ['analytics'],
        'system_settings' => ['settings'],
        'system_users' => ['users'],
        'system_maintenance' => ['maintenance'],
        'ai' => ['ai'],
        'licenses' => ['license'],
    ];

    /**
     * Check if a user has a specific permission for a module
     *
     * @param string $userRole The user's role
     * @param string $module The module identifier (e.g. 'content_pages')
     * @param string $permission The permission to check (view, create, edit, delete, manage)
     *
     * @return bool True if user has permission, false otherwise
     */
    public static function hasPermission(string $userRole, string $module, string $permission = self::PERM_VIEW): bool
    {
        if (!isset(self::$permissions[$userRole])) {
            return false;
        }

        $rolePermissions = self::$permissions[$userRole];

        if (!isset($rolePermissions[$module])) {
            return false;
        }

        return in_array($permission, $rolePermissions[$module], true);
    }

    /**
     * Get module identifier from admin page name
     *
     * @param string $pageName The admin page name (e.g. 'pages', 'articles')
     *
     * @return string|null The module identifier or null if not found
     */
    public static function getModuleFromPageName(string $pageName): ?string
    {
        foreach (self::$moduleMap as $module => $pageNames) {
            if (in_array($pageName, $pageNames, true)) {
                return $module;
            }
        }
        return null;
    }

    /**
     * Get all roles
     *
     * @return array List of available roles
     */
    public static function getAllRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_VIEWER => 'Viewer',
        ];
    }

    /**
     * Get all permissions
     *
     * @return array List of available permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            self::PERM_VIEW => 'View',
            self::PERM_CREATE => 'Create',
            self::PERM_EDIT => 'Edit',
            self::PERM_DELETE => 'Delete',
            self::PERM_MANAGE => 'Manage',
        ];
    }

    /**
     * Get role label
     *
     * @param string $role Role identifier
     *
     * @return string Role label
     */
    public static function getRoleLabel(string $role): string
    {
        $roles = self::getAllRoles();
        return $roles[$role] ?? 'Unknown';
    }
}
