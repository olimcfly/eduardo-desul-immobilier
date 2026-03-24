<?php
/**
 * ModuleDiagnostic.php — v4.1
 * Diagnostic automatisé de tous les modules IMMO LOCAL+
 *
 * v4.1 (corrections) :
 *   - diagnostic.files : 'diagnostic.php' → 'index.php'
 *   - seo.api_endpoints : suppression de 'seo.php' inexistant
 *   - $ignoredSlugs : exclut catégories, sous-dossiers techniques et orphelins non-modules
 *   - scanModuleDirectories() : guards appliqués sur L1/L2/L3
 */

class ModuleDiagnostic
{
    private $db;
    private $basePath;    // /admin/modules/
    private $apiBasePath; // /admin/api/
    private $results  = [];
    private $summary  = ['total' => 0, 'ok' => 0, 'warning' => 0, 'error' => 0];

    /** slug => chemin absolu résolu */
    private $resolvedPaths = [];

    /**
     * Dossiers à ignorer lors du scan (catégories parentes, sous-dossiers techniques).
     * Ces slugs ne sont pas des modules autonomes.
     */
    private $ignoredSlugs = [
        // catégories parentes
        'content', 'marketing', 'social', 'immobilier', 'seo',
        'ai', 'network', 'builder', 'strategy', 'system',
        // sous-dossiers techniques
        'tabs', 'assets', 'css', 'js', 'sql', 'cron', 'templates',
        'api', 'includes', 'helpers', 'layouts', 'renderers',
        // modules vides / en attente
        'transactions', 'biens',
    ];

    /**
     * Map slug => sous-dossier dans /admin/api/
     * Basé sur la structure réelle constatée dans le filesystem.
     */
    private $apiCategoryMap = [
        // marketing
        'leads'           => 'marketing',
        'scoring'         => 'marketing',
        'emails'          => 'marketing',
        'sequences'       => 'marketing',
        'crm'             => 'marketing',
        'messagerie'      => 'marketing',
        'newsletters'     => 'marketing',
        'sms'             => 'marketing',
        'ads-launch'      => 'marketing',
        // content
        'pages'           => 'content',
        'articles'        => 'content',
        'secteurs'        => 'content',
        'pages-capture'   => 'content',
        'blog'            => 'content',
        'templates'       => 'content',
        'sections'        => 'content',
        'guide-local'     => 'content',
        'guides'          => 'content',
        // builder
        'builder'         => 'builder',
        'design'          => 'builder',
        'menus'           => 'builder',
        // immobilier
        'properties'      => 'immobilier',
        'estimation'      => 'immobilier',
        'rdv'             => 'immobilier',
        'financement'     => 'immobilier',
        // seo
        'seo'             => 'seo',
        'seo-semantic'    => 'seo',
        'local-seo'       => 'seo',
        'analytics'       => 'seo',
        // social
        'gmb'             => 'social',
        'scraper-gmb'     => 'social',
        'facebook'        => 'social',
        'instagram'       => 'social',
        'linkedin'        => 'social',
        'tiktok'          => 'social',
        'reseaux-sociaux' => 'social',
        'kit-publications'=> 'social',
        // strategy
        'strategy'        => 'strategy',
        'launchpad'       => 'strategy',
        // system
        'settings'        => 'system',
        'diagnostic'      => 'system',
        'maintenance'     => 'system',
        'media'           => 'system',
        'license'         => 'system',
        'websites'        => 'system',
        // gmb api centralisée
        'gmb-outreach'    => 'gmb',
        // pas d'api centralisée
        'agents'          => null,
        'ia'              => null,
        'ai-prompts'      => null,
        'neuropersona'    => null,
        'advisor-context' => null,
        'journal'         => null,
        'contact'         => null,
        'partenaires'     => null,
        'courtiers'       => null,
        'email'           => null,
    ];

    // =========================================================================
    // DÉFINITIONS DES MODULES
    // =========================================================================
    private $moduleDefinitions = [

        // ── CRM / Marketing ───────────────────────────────────────────────────
        'leads' => [
            'label' => 'Gestion des Leads', 'category' => 'CRM', 'icon' => 'fas fa-users',
            'files' => ['index.php'],
            'optional_files' => [],
            'tables' => ['leads'],
            'api_endpoints' => ['leads.php'],
            'depends_on' => [],
        ],
        'crm' => [
            'label' => 'CRM Dashboard', 'category' => 'CRM', 'icon' => 'fas fa-address-book',
            'files' => ['index.php'],
            'tables' => ['leads'],
            'api_endpoints' => ['crm.php'],
            'depends_on' => ['leads'],
        ],
        'scoring' => [
            'label' => 'Lead Scoring', 'category' => 'CRM', 'icon' => 'fas fa-star-half-alt',
            'files' => ['index.php'],
            'tables' => ['leads'],
            'api_endpoints' => ['scoring-actions.php'],
            'depends_on' => ['leads'],
        ],
        'messagerie' => [
            'label' => 'Messagerie', 'category' => 'CRM', 'icon' => 'fas fa-comments',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['messagerie.php'],
            'depends_on' => [],
        ],
        'contact' => [
            'label' => 'Formulaire Contact', 'category' => 'CRM', 'icon' => 'fas fa-envelope',
            'files' => ['index.php'],
            'tables' => ['leads'],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'rdv' => [
            'label' => 'Rendez-vous', 'category' => 'CRM', 'icon' => 'fas fa-calendar-check',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['rdv.php'],
            'depends_on' => ['leads'],
        ],

        // ── CMS ───────────────────────────────────────────────────────────────
        'pages' => [
            'label' => 'Gestion des Pages', 'category' => 'CMS', 'icon' => 'fas fa-file-alt',
            'files' => ['index.php', 'PageController.php'],
            'optional_files' => ['action.php', 'create.php', 'edit.php', 'delete.php', 'import.php'],
            'tables' => ['builder_pages'],
            'api_endpoints' => ['pages.php'],
            'depends_on' => [],
        ],
        'pages-capture' => [
            'label' => 'Pages de Capture', 'category' => 'CMS', 'icon' => 'fas fa-magnet',
            'files' => ['index.php'],
            'optional_files' => ['create.php', 'edit.php', 'delete.php', 'form.php', 'save.php', 'stats.php'],
            'tables' => ['capture_pages'],
            'api_endpoints' => ['captures-actions.php'],
            'depends_on' => [],
        ],
        'builder' => [
            'label' => 'Builder Pro', 'category' => 'CMS', 'icon' => 'fas fa-cubes',
            'files' => ['index.php', 'editor.php', 'config.php', 'BuilderController.php'],
            'optional_files' => ['create.php', 'headers.php', 'footers.php', 'templates.php', 'layouts.php', 'edit-header.php', 'edit-footer.php', 'design.php'],
            'tables' => ['builder_pages', 'builder_sections', 'builder_templates'],
            'api_endpoints' => ['builder.php'],
            'depends_on' => [],
        ],
        'articles' => [
            'label' => 'Articles / Blog', 'category' => 'CMS', 'icon' => 'fas fa-newspaper',
            'files' => ['index.php', 'ArticleController.php'],
            'optional_files' => ['edit.php', 'tabs/journal.php'],
            'tables' => ['articles'],
            'api_endpoints' => ['articles.php'],
            'depends_on' => [],
        ],
        'blog' => [
            'label' => 'Blog Frontend', 'category' => 'CMS', 'icon' => 'fas fa-blog',
            'files' => ['index.php'],
            'tables' => ['articles'],
            'api_endpoints' => [],
            'depends_on' => ['articles'],
        ],
        'secteurs' => [
            'label' => 'Secteurs / Quartiers', 'category' => 'CMS', 'icon' => 'fas fa-map-marker-alt',
            'files' => ['index.php', 'edit.php'],
            'optional_files' => ['diagnostic.php', 'secteur-single.php', 'secteurs.php'],
            'tables' => ['secteurs'],
            'api_endpoints' => ['secteurs.php'],
            'depends_on' => [],
        ],
        'sections' => [
            'label' => 'Sections réutilisables', 'category' => 'CMS', 'icon' => 'fas fa-puzzle-piece',
            'files' => ['index.php', 'SectionController.php'],
            'tables' => ['builder_sections'],
            'api_endpoints' => [],
            'depends_on' => ['builder'],
        ],
        'menus' => [
            'label' => 'Gestion des Menus', 'category' => 'CMS', 'icon' => 'fas fa-bars',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['menus.php'],
            'depends_on' => [],
        ],
        'templates' => [
            'label' => 'Templates', 'category' => 'CMS', 'icon' => 'fas fa-palette',
            'files' => ['index.php'],
            'tables' => ['builder_templates'],
            'api_endpoints' => [],
            'depends_on' => ['builder'],
        ],
        'guide-local' => [
            'label' => 'Guide du Quartier', 'category' => 'CMS', 'icon' => 'fas fa-map',
            'files' => ['index.php'],
            'optional_files' => ['edit.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'guides' => [
            'label' => 'Guides', 'category' => 'CMS', 'icon' => 'fas fa-book',
            'files' => ['index.php'],
            'optional_files' => ['create.php', 'edit.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'maintenance' => [
            'label' => 'Page Maintenance', 'category' => 'CMS', 'icon' => 'fas fa-tools',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['maintenance-save.php'],
            'depends_on' => [],
        ],

        // ── Immobilier ────────────────────────────────────────────────────────
        'properties' => [
            'label' => 'Biens Immobiliers', 'category' => 'Immobilier', 'icon' => 'fas fa-home',
            'files' => ['index.php', 'PropertyController.php'],
            'optional_files' => ['edit.php'],
            'tables' => ['properties'],
            'api_endpoints' => ['properties.php'],
            'depends_on' => [],
        ],
        'estimation' => [
            'label' => 'Estimations', 'category' => 'Immobilier', 'icon' => 'fas fa-calculator',
            'files' => ['index.php'],
            'optional_files' => ['EstimationService.php', 'avisdevaleur.php', 'estimation-gratuite.php', 'public.php', 'emails.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'financement' => [
            'label' => 'Financement', 'category' => 'Immobilier', 'icon' => 'fas fa-euro-sign',
            'files' => ['index.php'],
            'optional_files' => ['courtiers.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],

        // ── SEO ───────────────────────────────────────────────────────────────
        'seo' => [
            'label' => 'SEO Tools', 'category' => 'SEO', 'icon' => 'fas fa-search',
            'files' => ['index.php'],
            'optional_files' => ['articles.php', 'pages.php'],
            'tables' => [],
            // CORRIGÉ v4.1 : 'seo.php' supprimé (fichier inexistant), seul 'seo-api.php' existe
            'api_endpoints' => ['seo-api.php'],
            'depends_on' => [],
        ],
        'seo-semantic' => [
            'label' => 'Analyse Sémantique', 'category' => 'SEO', 'icon' => 'fas fa-brain',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => ['articles'],
        ],
        'local-seo' => [
            'label' => 'SEO Local / GMB', 'category' => 'SEO', 'icon' => 'fas fa-map-pin',
            'files' => ['index.php'],
            'optional_files' => [
                'tabs/guide.php', 'tabs/partners.php', 'tabs/reviews.php',
                'tabs/publications.php', 'tabs/questions.php', 'tabs/journal.php',
            ],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'analytics' => [
            'label' => 'Analytics', 'category' => 'SEO', 'icon' => 'fas fa-chart-line',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],

        // ── Social ────────────────────────────────────────────────────────────
        'facebook' => [
            'label' => 'Facebook', 'category' => 'Social', 'icon' => 'fab fa-facebook',
            'files' => ['index.php'],
            'optional_files' => ['tabs/rediger.php', 'tabs/idees.php', 'tabs/journal.php', 'tabs/strategie.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'instagram' => [
            'label' => 'Instagram', 'category' => 'Social', 'icon' => 'fab fa-instagram',
            'files' => ['index.php'],
            'optional_files' => ['tabs/rediger.php', 'tabs/journal.php', 'tabs/strategie.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'linkedin' => [
            'label' => 'LinkedIn', 'category' => 'Social', 'icon' => 'fab fa-linkedin',
            'files' => ['index.php'],
            'optional_files' => ['tabs/rediger.php', 'tabs/journal.php', 'tabs/strategie.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'tiktok' => [
            'label' => 'TikTok', 'category' => 'Social', 'icon' => 'fab fa-tiktok',
            'files' => ['index.php'],
            'optional_files' => ['tabs/scripts.php', 'tabs/idees.php', 'tabs/clonage.php', 'tabs/journal.php'],
            'tables' => [],
            'api_endpoints' => ['tiktok-save-script.php', 'tiktok-update-status.php'],
            'depends_on' => [],
        ],
        'reseaux-sociaux' => [
            'label' => 'Hub Réseaux Sociaux', 'category' => 'Social', 'icon' => 'fas fa-share-alt',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'kit-publications' => [
            'label' => 'Kit Publications', 'category' => 'Social', 'icon' => 'fas fa-images',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],

        // ── Marketing ─────────────────────────────────────────────────────────
        'gmb' => [
            'label' => 'GMB Outreach', 'category' => 'Marketing', 'icon' => 'fas fa-store',
            'files' => ['index.php'],
            'optional_files' => [
                'contacts.php', 'sequences.php', 'ContactController.php',
                'SequenceController.php', 'GmbEmailController.php',
                'EmailValidator.php', 'GmbScraperController.php',
            ],
            'tables' => ['gmb_contacts', 'gmb_sequences'],
            'api_endpoints' => ['gmb.php'],
            'depends_on' => [],
        ],
        'scraper-gmb' => [
            'label' => 'Scraper GMB', 'category' => 'Marketing', 'icon' => 'fas fa-spider',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['gmb.php'],
            'depends_on' => [],
        ],
        'emails' => [
            'label' => 'Email Marketing', 'category' => 'Marketing', 'icon' => 'fas fa-envelope-open-text',
            'files' => ['index.php'],
            'optional_files' => ['tabs/journal.php'],
            'tables' => [],
            'api_endpoints' => ['emails.php'],
            'depends_on' => ['leads'],
        ],
        'sequences' => [
            'label' => 'Séquences Email', 'category' => 'Marketing', 'icon' => 'fas fa-stream',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => ['leads'],
        ],
        'newsletters' => [
            'label' => 'Newsletters', 'category' => 'Marketing', 'icon' => 'fas fa-newspaper',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'sms' => [
            'label' => 'SMS Marketing', 'category' => 'Marketing', 'icon' => 'fas fa-sms',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'ads-launch' => [
            'label' => 'Lanceur de Pubs', 'category' => 'Marketing', 'icon' => 'fas fa-rocket',
            'files' => ['index.php', 'AdsLaunchService.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],

        // ── IA ────────────────────────────────────────────────────────────────
        'ia' => [
            'label' => 'Hub IA', 'category' => 'IA', 'icon' => 'fas fa-robot',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'agents' => [
            'label' => 'Agents IA', 'category' => 'IA', 'icon' => 'fas fa-user-cog',
            'files' => ['index.php'],
            'optional_files' => ['agents.json'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'neuropersona' => [
            'label' => 'NeuroPersona', 'category' => 'IA', 'icon' => 'fas fa-user-astronaut',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'ai-prompts' => [
            'label' => 'Prompts IA', 'category' => 'IA', 'icon' => 'fas fa-terminal',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'advisor-context' => [
            'label' => 'Contexte Conseiller', 'category' => 'IA', 'icon' => 'fas fa-user-tie',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'journal' => [
            'label' => 'Journal Éditorial', 'category' => 'IA', 'icon' => 'fas fa-book-open',
            'files' => ['index.php', 'JournalController.php'],
            'optional_files' => ['generator.php', 'journal-widget.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],

        // ── Stratégie ─────────────────────────────────────────────────────────
        'strategy' => [
            'label' => 'Stratégie Marketing', 'category' => 'Stratégie', 'icon' => 'fas fa-chess',
            'files' => ['index.php', 'StrategyService.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'launchpad' => [
            'label' => 'Launchpad', 'category' => 'Stratégie', 'icon' => 'fas fa-space-shuttle',
            'files' => ['index.php', 'LaunchpadManager.php'],
            'optional_files' => ['LaunchpadAI.php', 'generate-offre.php', 'save-step.php', 'steps.php'],
            'tables' => [],
            'api_endpoints' => ['launchpad.php'],
            'depends_on' => [],
        ],

        // ── Système ───────────────────────────────────────────────────────────
        'settings' => [
            'label' => 'Paramètres', 'category' => 'Système', 'icon' => 'fas fa-cog',
            'files' => ['index.php'],
            'optional_files' => ['site-identity.php', 'ai_settings.php'],
            'tables' => ['settings'],
            'api_endpoints' => ['settings.php'],
            'depends_on' => [],
        ],
        'design' => [
            'label' => 'Design / Thème', 'category' => 'Système', 'icon' => 'fas fa-paint-brush',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'websites' => [
            'label' => 'Multi-sites', 'category' => 'Système', 'icon' => 'fas fa-globe',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'media' => [
            'label' => 'Médiathèque', 'category' => 'Système', 'icon' => 'fas fa-photo-video',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => ['upload.php'],
            'depends_on' => [],
        ],
        'license' => [
            'label' => 'Licence', 'category' => 'Système', 'icon' => 'fas fa-id-card',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'diagnostic' => [
            'label' => 'Diagnostic Système', 'category' => 'Système', 'icon' => 'fas fa-stethoscope',
            // CORRIGÉ v4.1 : 'diagnostic.php' → 'index.php' (fichier réel du module)
            'files' => ['index.php', 'ModuleDiagnostic.php'],
            'optional_files' => ['api.php', 'debug.php', 'test.php'],
            'tables' => [],
            'api_endpoints' => ['modules-ajax.php'],
            'depends_on' => [],
        ],

        // ── Network ───────────────────────────────────────────────────────────
        'partenaires' => [
            'label' => 'Partenaires', 'category' => 'Network', 'icon' => 'fas fa-handshake',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'courtiers' => [
            'label' => 'Courtiers', 'category' => 'Network', 'icon' => 'fas fa-briefcase',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
        'email' => [
            'label' => 'Email (legacy)', 'category' => 'Network', 'icon' => 'fas fa-at',
            'files' => ['index.php'],
            'tables' => [],
            'api_endpoints' => [],
            'depends_on' => [],
        ],
    ];

    // =========================================================================
    // CONSTRUCTEUR
    // =========================================================================
    public function __construct(PDO $db, string $modulesBasePath)
    {
        $this->db          = $db;
        $this->basePath    = rtrim($modulesBasePath, '/');
        $this->apiBasePath = rtrim(dirname($modulesBasePath), '/') . '/api';
    }

    // =========================================================================
    // DIAGNOSTIC COMPLET
    // =========================================================================
    public function runFullDiagnostic(): array
    {
        $this->results       = [];
        $this->summary       = ['total' => 0, 'ok' => 0, 'warning' => 0, 'error' => 0];
        $this->resolvedPaths = [];

        $this->scanModuleDirectories();

        foreach ($this->moduleDefinitions as $slug => $def) {
            $this->results[$slug] = $this->diagnoseModule($slug, $def);
            $this->summary['total']++;
            $status = $this->results[$slug]['status'];
            if ($status === 'ok')          $this->summary['ok']++;
            elseif ($status === 'warning') $this->summary['warning']++;
            else                           $this->summary['error']++;
        }

        // Modules orphelins (présents sur disque mais non référencés et non ignorés)
        foreach ($this->resolvedPaths as $slug => $path) {
            if (isset($this->moduleDefinitions[$slug])) continue;
            if (in_array($slug, $this->ignoredSlugs)) continue;

            $fileCount = $this->countFiles($path);
            $hasIndex  = file_exists($path . '/index.php');
            $this->results[$slug] = [
                'label'      => ucfirst(str_replace('-', ' ', $slug)),
                'category'   => 'Non référencé',
                'icon'       => 'fas fa-question-circle',
                'status'     => 'warning',
                'file_count' => $fileCount,
                'checks'     => [
                    ['type' => 'orphan',        'message' => "Dossier {$slug}/ présent mais non référencé", 'status' => 'warning'],
                    ['type' => 'file_required', 'message' => $hasIndex ? 'index.php présent' : 'index.php ABSENT', 'status' => $hasIndex ? 'ok' : 'warning'],
                    ['type' => 'size',          'message' => "{$fileCount} fichier(s)",                           'status' => $fileCount > 0 ? 'ok' : 'warning'],
                ],
            ];
            $this->summary['total']++;
            $this->summary['warning']++;
        }

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary'   => $this->summary,
            'db_health' => $this->checkDatabaseHealth(),
            'modules'   => $this->results,
        ];
    }

    // =========================================================================
    // SCAN HIÉRARCHIQUE 3 NIVEAUX
    //
    //  L0 : basePath/
    //  L1 : basePath/{item}/            → catégorie OU module racine
    //  L2 : basePath/{cat}/{slug}/      → module dans catégorie (priorité sur L1)
    //  L3 : basePath/{cat}/{sub}/{slug}/→ module profond (priorité absolue)
    //
    //  CORRIGÉ v4.1 : $ignoredSlugs appliqué sur chaque niveau
    //  pour ne pas enregistrer les dossiers catégories et sous-dossiers techniques
    // =========================================================================
    private function scanModuleDirectories(): void
    {
        if (!is_dir($this->basePath)) return;

        foreach ($this->listDirs($this->basePath) as $lvl1) {
            $slug1 = basename($lvl1);

            // L1 : ignorer les dossiers catégories et techniques
            if (in_array($slug1, $this->ignoredSlugs)) {
                // On continue quand même pour scanner L2/L3 à l'intérieur
            } elseif ($this->looksLikeModule($lvl1)) {
                $this->resolvedPaths[$slug1] = $lvl1;
            }

            foreach ($this->listDirs($lvl1) as $lvl2) {
                $slug2 = basename($lvl2);

                // L2 : ignorer les sous-dossiers techniques
                if (in_array($slug2, $this->ignoredSlugs)) {
                    // On continue pour scanner L3 si nécessaire
                } else {
                    // L2 l'emporte sur L1
                    $this->resolvedPaths[$slug2] = $lvl2;
                }

                foreach ($this->listDirs($lvl2) as $lvl3) {
                    $slug3 = basename($lvl3);
                    if (in_array($slug3, $this->ignoredSlugs)) continue;
                    if ($this->looksLikeModule($lvl3)) {
                        // L3 l'emporte sur tout
                        $this->resolvedPaths[$slug3] = $lvl3;
                    }
                }
            }
        }
    }

    private function listDirs(string $path): array
    {
        $dirs = [];
        if (!is_dir($path)) return $dirs;
        foreach (scandir($path) as $item) {
            if ($item[0] === '.') continue;
            $full = $path . '/' . $item;
            if (is_dir($full)) $dirs[] = $full;
        }
        return $dirs;
    }

    private function looksLikeModule(string $path): bool
    {
        foreach (scandir($path) as $f) {
            if (pathinfo($f, PATHINFO_EXTENSION) === 'php') return true;
        }
        return false;
    }

    // =========================================================================
    // RÉSOLUTION D'UN ENDPOINT API
    //
    // Ordre de recherche :
    //   1. Dans le module lui-même      ex: tiktok/api/save-script.php
    //   2. /admin/api/{category}/       ex: /admin/api/marketing/leads.php
    //   3. /admin/api/{slug}/           fallback
    // =========================================================================
    private function resolveApiEndpoint(string $slug, string $endpoint, ?string $modulePath): array
    {
        // 1. Dans le module
        if ($modulePath && file_exists($modulePath . '/' . $endpoint)) {
            return ['found' => true, 'location' => 'module/' . $endpoint];
        }

        // 2. /admin/api/{category}/
        $cat = $this->apiCategoryMap[$slug] ?? null;
        if ($cat) {
            $p = $this->apiBasePath . '/' . $cat . '/' . $endpoint;
            if (file_exists($p)) {
                return ['found' => true, 'location' => '/admin/api/' . $cat . '/' . $endpoint];
            }
        }

        // 3. /admin/api/{slug}/
        $p = $this->apiBasePath . '/' . $slug . '/' . $endpoint;
        if (file_exists($p)) {
            return ['found' => true, 'location' => '/admin/api/' . $slug . '/' . $endpoint];
        }

        $tried = [];
        if ($modulePath) $tried[] = 'module/' . $endpoint;
        if ($cat)        $tried[] = '/admin/api/' . $cat . '/' . $endpoint;
        $tried[]                  = '/admin/api/' . $slug . '/' . $endpoint;

        return ['found' => false, 'location' => '', 'tried' => $tried];
    }

    // =========================================================================
    // DIAGNOSTIC D'UN MODULE
    // =========================================================================
    private function diagnoseModule(string $slug, array $def): array
    {
        $checks     = [];
        $hasError   = false;
        $hasWarning = false;

        $modulePath = $this->resolvedPaths[$slug] ?? null;
        $dirExists  = $modulePath !== null && is_dir($modulePath);

        $checks[] = [
            'type'    => 'directory',
            'message' => $dirExists
                ? 'Dossier → ' . str_replace($this->basePath . '/', '', $modulePath) . '/'
                : "Dossier {$slug}/ MANQUANT",
            'status'  => $dirExists ? 'ok' : 'error',
        ];

        if (!$dirExists) {
            return [
                'label'      => $def['label'],
                'category'   => $def['category'],
                'icon'       => $def['icon'] ?? 'fas fa-folder',
                'status'     => 'error',
                'file_count' => 0,
                'checks'     => $checks,
            ];
        }

        // Fichiers obligatoires
        foreach ($def['files'] ?? [] as $file) {
            $exists = file_exists($modulePath . '/' . $file);
            $checks[] = [
                'type'    => 'file_required',
                'message' => $exists ? $file : "{$file} MANQUANT (requis)",
                'status'  => $exists ? 'ok' : 'error',
            ];
            if (!$exists) {
                $hasError = true;
                continue;
            }
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $syntax = $this->checkPhpSyntax($modulePath . '/' . $file);
                if (!$syntax['valid']) {
                    $checks[] = ['type' => 'syntax', 'message' => "Syntaxe {$file} : " . $syntax['error'], 'status' => 'error'];
                    $hasError = true;
                }
            }
        }

        // Fichiers optionnels
        $optFiles   = $def['optional_files'] ?? [];
        $optTotal   = count($optFiles);
        $optPresent = 0;
        foreach ($optFiles as $file) {
            if (file_exists($modulePath . '/' . $file)) {
                $optPresent++;
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $syntax = $this->checkPhpSyntax($modulePath . '/' . $file);
                    if (!$syntax['valid']) {
                        $checks[] = ['type' => 'syntax', 'message' => "Syntaxe {$file} : " . $syntax['error'], 'status' => 'warning'];
                        $hasWarning = true;
                    }
                }
            }
        }
        if ($optTotal > 0) {
            $pct = round(($optPresent / $optTotal) * 100);
            $st  = $pct >= 80 ? 'ok' : 'warning';
            $checks[] = ['type' => 'files_optional', 'message' => "Optionnels : {$optPresent}/{$optTotal} ({$pct}%)", 'status' => $st];
            if ($st === 'warning') $hasWarning = true;
        }

        // Tables DB
        foreach ($def['tables'] ?? [] as $table) {
            $exists   = $this->tableExists($table);
            $rowCount = $exists ? $this->tableRowCount($table) : 0;
            $checks[] = [
                'type'    => 'table',
                'message' => $exists ? "Table `{$table}` ({$rowCount} lignes)" : "Table `{$table}` MANQUANTE",
                'status'  => $exists ? 'ok' : 'error',
            ];
            if (!$exists) $hasError = true;
        }

        // Endpoints API
        foreach ($def['api_endpoints'] ?? [] as $endpoint) {
            $result = $this->resolveApiEndpoint($slug, $endpoint, $modulePath);
            if ($result['found']) {
                $checks[] = ['type' => 'api', 'message' => "API {$endpoint} ✓ ({$result['location']})", 'status' => 'ok'];
            } else {
                $tried    = implode(', ', $result['tried'] ?? []);
                $checks[] = ['type' => 'api', 'message' => "API {$endpoint} MANQUANT — cherché : {$tried}", 'status' => 'warning'];
                $hasWarning = true;
            }
        }

        // Dépendances
        foreach ($def['depends_on'] ?? [] as $dep) {
            $depPath = $this->resolvedPaths[$dep] ?? null;
            $depOk   = $depPath && is_dir($depPath) && file_exists($depPath . '/index.php');
            $checks[] = [
                'type'    => 'dependency',
                'message' => $depOk ? "Dép. `{$dep}` OK" : "Dép. `{$dep}` NON FONCTIONNELLE",
                'status'  => $depOk ? 'ok' : 'warning',
            ];
            if (!$depOk) $hasWarning = true;
        }

        $fileCount = $this->countFiles($modulePath);
        $checks[]  = ['type' => 'size', 'message' => "{$fileCount} fichier(s)", 'status' => $fileCount > 0 ? 'ok' : 'warning'];

        $status = 'ok';
        if ($hasWarning) $status = 'warning';
        if ($hasError)   $status = 'error';

        return [
            'label'      => $def['label'],
            'category'   => $def['category'],
            'icon'       => $def['icon'] ?? 'fas fa-folder',
            'status'     => $status,
            'file_count' => $fileCount,
            'checks'     => $checks,
        ];
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function checkPhpSyntax(string $filepath): array
    {
        $output = [];
        $rc     = 0;
        exec('php -l ' . escapeshellarg($filepath) . ' 2>&1', $output, $rc);
        return [
            'valid' => $rc === 0,
            'error' => $rc !== 0 ? implode(' ', $output) : null,
        ];
    }

    private function tableExists(string $table): bool
    {
        try {
            return $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($table))->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function tableRowCount(string $table): int
    {
        try {
            return (int) $this->db->query(
                'SELECT COUNT(*) FROM `' . str_replace('`', '', $table) . '`'
            )->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function countFiles(string $dir): int
    {
        if (!is_dir($dir)) return 0;
        $count = 0;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $f) {
            if ($f->isFile()) $count++;
        }
        return $count;
    }

    private function checkDatabaseHealth(): array
    {
        $results = [];
        try {
            $this->db->query('SELECT 1');
            $results[] = ['check' => 'Connexion DB', 'status' => 'ok', 'value' => 'Active'];

            $tables    = $this->db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            $results[] = ['check' => 'Nombre de tables', 'status' => 'ok', 'value' => count($tables)];

            $coreTables = [
                'leads', 'builder_pages', 'builder_sections', 'builder_templates',
                'properties', 'capture_pages', 'articles', 'secteurs',
                'settings', 'admins', 'api_keys', 'gmb_contacts', 'gmb_sequences',
            ];
            foreach ($coreTables as $ct) {
                $exists    = in_array($ct, $tables);
                $results[] = [
                    'check'  => "Table `{$ct}`",
                    'status' => $exists ? 'ok' : 'warning',
                    'value'  => $exists ? $this->tableRowCount($ct) . ' lignes' : 'ABSENTE',
                ];
            }

            $dbName = $this->db->query('SELECT DATABASE()')->fetchColumn();
            $size   = $this->db->query(
                "SELECT ROUND(SUM(data_length+index_length)/1024/1024,2)
                 FROM information_schema.tables
                 WHERE table_schema=" . $this->db->quote($dbName)
            )->fetchColumn();
            $results[] = ['check' => 'Taille DB', 'status' => 'ok', 'value' => $size . ' MB'];

        } catch (\Exception $e) {
            $results[] = ['check' => 'Connexion DB', 'status' => 'error', 'value' => $e->getMessage()];
        }
        return $results;
    }
}