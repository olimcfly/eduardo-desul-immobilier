<?php
/**
 * StrategyService.php
 * /admin/modules/strategy/strategy/StrategyService.php
 *
 * Service central de la stratégie marketing IMMO LOCAL+
 *
 * Fonctions :
 *   - Personas (niveaux de conscience Schwartz)
 *   - Méthode MERE (Miroir → Émotion → Réassurance → Exclusivité)
 *   - Sujets de contenu par persona / canal / objectif
 *   - Offres & propositions de valeur
 *   - Cartographie des canaux (GMB, RS, Ads, SEO)
 *   - Structures de communication
 *   - KPIs consolidés depuis les tables de la plateforme
 *   - Migration automatique des tables strategy_*
 */

class StrategyService
{
    private PDO $pdo;

    // Niveaux de conscience Eugene Schwartz — adaptés à l'immobilier
    public const AWARENESS_LEVELS = [
        1 => [
            'slug'     => 'unaware',
            'label'    => 'Inconscient du problème',
            'desc'     => "Le prospect ne sait pas encore qu'il a un besoin immobilier.",
            'exemple'  => "Locataire depuis 10 ans, ne pense pas à acheter.",
            'approche' => "Contenu éducatif, storytelling, inspiration vie locale.",
            'canal'    => ['Facebook','Instagram','TikTok'],
            'color'    => '#94a3b8',
        ],
        2 => [
            'slug'     => 'problem_aware',
            'label'    => 'Conscient du problème',
            'desc'     => "Il sait qu'il a un besoin mais ne cherche pas encore de solution.",
            'exemple'  => "Veut acheter mais ne sait pas par où commencer.",
            'approche' => "Guides pratiques, checklist, FAQ, articles de fond.",
            'canal'    => ['SEO Blog','Google','Facebook'],
            'color'    => '#f59e0b',
        ],
        3 => [
            'slug'     => 'solution_aware',
            'label'    => 'Conscient de la solution',
            'desc'     => "Il cherche une solution mais ne connaît pas encore le conseiller.",
            'exemple'  => "Compare les agences et les mandataires.",
            'approche' => "Comparatifs, preuves sociales, témoignages, études de cas.",
            'canal'    => ['Google','GMB','LinkedIn'],
            'color'    => '#3b82f6',
        ],
        4 => [
            'slug'     => 'product_aware',
            'label'    => 'Conscient du service',
            'desc'     => "Il connaît votre offre mais hésite encore.",
            'exemple'  => "A visité le site, hésite à prendre contact.",
            'approche' => "Garanties, FAQ rassurantes, estimation gratuite.",
            'canal'    => ['Retargeting','Email','Landing page'],
            'color'    => '#8b5cf6',
        ],
        5 => [
            'slug'     => 'most_aware',
            'label'    => 'Prêt à agir',
            'desc'     => "Il est prêt à contacter ou à signer.",
            'exemple'  => "Cherche le meilleur conseiller dans sa zone.",
            'approche' => "CTA direct, urgence, exclusivité géographique, RDV immédiat.",
            'canal'    => ['GMB','Email','SMS','Téléphone'],
            'color'    => '#10b981',
        ],
    ];

    // Méthode MERE
    public const MERE_STRUCTURE = [
        'M' => [
            'label'   => 'Miroir',
            'color'   => '#3b82f6',
            'desc'    => "Refléter exactement la situation, le problème ou le désir du prospect.",
            'conseil' => "Parlez de LUI, pas de vous. Commencez par son ressenti.",
            'exemple' => "Vous êtes propriétaire depuis 3 ans et vous vous demandez si c'est le bon moment pour vendre ?",
        ],
        'E' => [
            'label'   => 'Émotion',
            'color'   => '#ec4899',
            'desc'    => "Amplifier l'émotion dominante : peur de rater, envie de mieux, frustration actuelle.",
            'conseil' => "Reliez le problème à une émotion profonde. Imaginez la vie après la solution.",
            'exemple' => "Imaginez serrer la main de votre acheteur et repartir serein, avec exactement le prix que vous espériez.",
        ],
        'R' => [
            'label'   => 'Réassurance',
            'color'   => '#10b981',
            'desc'    => "Rassurer avec des preuves : stats, témoignages, garanties, expertise locale.",
            'conseil' => "Chiffres précis, témoignages nommés, badge eXp, CPI card visible.",
            'exemple' => "93% de mes mandats vendus au prix demandé. 47 familles accompagnées à Blanquefort et Bordeaux.",
        ],
        'X' => [
            'label'   => 'Exclusivité',
            'color'   => '#f59e0b',
            'desc'    => "Créer l'urgence et l'unicité : offre limitée, zone exclusive, expertise unique.",
            'conseil' => "Pourquoi vous et pas un autre ? Votre exclusivité géographique est votre atout n°1.",
            'exemple' => "Seul conseiller certifié eXp sur Blanquefort et le nord de Bordeaux. Places limitées ce mois-ci.",
        ],
    ];

    // ──────────────────────────────────────────────────────────────────────────
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Factory — résout la connexion depuis le wrapper Database de la plateforme
     * Compatible : Database::getInstance()->getConnection() OU ->getPdo()
     */
    public static function create(): self
    {
        // Chemin depuis /admin/modules/strategy/strategy/
        $rootPath = dirname(__DIR__, 4);
        if (!defined('DB_HOST')) {
            @require_once $rootPath . '/config/config.php';
        }
        if (!class_exists('Database')) {
            require_once $rootPath . '/includes/classes/Database.php';
        }
        $db  = Database::getInstance();
        $pdo = method_exists($db, 'getConnection') ? $db->getConnection()
              : (method_exists($db, 'getPdo')      ? $db->getPdo()
              : $db->pdo);
        return new self($pdo);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PERSONAS
    // ══════════════════════════════════════════════════════════════════════════

    public function getPersonas(): array
    {
        try {
            if ($this->tableExists('strategy_personas')) {
                return $this->pdo->query(
                    "SELECT * FROM strategy_personas ORDER BY niveau_conscience ASC, id ASC"
                )->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {}
        return $this->defaultPersonas();
    }

    public function getPersona(int $id): ?array
    {
        try {
            if ($this->tableExists('strategy_personas')) {
                $stmt = $this->pdo->prepare("SELECT * FROM strategy_personas WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        } catch (\Exception $e) {}
        foreach ($this->defaultPersonas() as $p) {
            if ((int)($p['id'] ?? 0) === $id) return $p;
        }
        return null;
    }

    public function savePersona(array $data): int
    {
        $this->ensureTable('strategy_personas');
        $allowed = [
            'nom','prenom','age_min','age_max','situation','objectif_principal',
            'peur_principale','desir_principal','niveau_conscience','canal_prefere',
            'message_cle','persona_type','avatar_url','notes',
        ];
        [$fields, $params] = $this->buildFieldParams($allowed, $data);
        if (empty($fields)) throw new \InvalidArgumentException('Aucun champ valide');

        $id = (int) ($data['id'] ?? 0);
        if ($id > 0) {
            $set = $this->buildSet($fields, $params);
            $params[':id'] = $id;
            $this->pdo->prepare("UPDATE strategy_personas SET {$set}, updated_at = NOW() WHERE id = :id")->execute($params);
            return $id;
        }
        [$cols, $vals] = $this->colsVals($fields, $params);
        $this->pdo->prepare("INSERT INTO strategy_personas ({$cols}, created_at) VALUES ({$vals}, NOW())")->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function deletePersona(int $id): bool
    {
        if (!$this->tableExists('strategy_personas')) return false;
        return (bool) $this->pdo->prepare("DELETE FROM strategy_personas WHERE id = :id")->execute([':id' => $id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SUJETS DE CONTENU
    // ══════════════════════════════════════════════════════════════════════════

    public function getSujets(array $filters = []): array
    {
        try {
            if ($this->tableExists('strategy_sujets')) {
                $where = ['1=1']; $params = [];
                foreach (['canal' => ':canal', 'niveau_conscience' => ':nc', 'objectif' => ':obj', 'statut' => ':st'] as $col => $p) {
                    $key = str_replace(':', '', $p);
                    if (!empty($filters[$key])) { $where[] = "{$col} = {$p}"; $params[$p] = $filters[$key]; }
                }
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM strategy_sujets WHERE " . implode(' AND ', $where) .
                    " ORDER BY priorite DESC, id DESC LIMIT 200"
                );
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {}
        return $this->defaultSujets();
    }

    public function saveSujet(array $data): int
    {
        $this->ensureTable('strategy_sujets');
        $allowed = ['titre','description','canal','niveau_conscience','objectif','format','structure_mere','priorite','statut','persona_id'];
        [$fields, $params] = $this->buildFieldParams($allowed, $data);
        $id = (int) ($data['id'] ?? 0);
        if ($id > 0) {
            $set = $this->buildSet($fields, $params);
            $params[':id'] = $id;
            $this->pdo->prepare("UPDATE strategy_sujets SET {$set}, updated_at = NOW() WHERE id = :id")->execute($params);
            return $id;
        }
        [$cols, $vals] = $this->colsVals($fields, $params);
        $this->pdo->prepare("INSERT INTO strategy_sujets ({$cols}, created_at) VALUES ({$vals}, NOW())")->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteSujet(int $id): bool
    {
        if (!$this->tableExists('strategy_sujets')) return false;
        return (bool) $this->pdo->prepare("DELETE FROM strategy_sujets WHERE id = :id")->execute([':id' => $id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // OFFRES & PROPOSITIONS DE VALEUR
    // ══════════════════════════════════════════════════════════════════════════

    public function getOffres(): array
    {
        try {
            if ($this->tableExists('strategy_offres')) {
                return $this->pdo->query(
                    "SELECT * FROM strategy_offres ORDER BY ordre ASC, id DESC"
                )->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {}
        return $this->defaultOffres();
    }

    public function saveOffre(array $data): int
    {
        $this->ensureTable('strategy_offres');
        $allowed = ['titre','accroche','description','benefices','preuves','cta','canal','persona_id','niveau_conscience','ordre','statut'];
        [$fields, $params] = $this->buildFieldParams($allowed, $data);
        $id = (int) ($data['id'] ?? 0);
        if ($id > 0) {
            $set = $this->buildSet($fields, $params);
            $params[':id'] = $id;
            $this->pdo->prepare("UPDATE strategy_offres SET {$set}, updated_at = NOW() WHERE id = :id")->execute($params);
            return $id;
        }
        [$cols, $vals] = $this->colsVals($fields, $params);
        $this->pdo->prepare("INSERT INTO strategy_offres ({$cols}, created_at) VALUES ({$vals}, NOW())")->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CANAUX MARKETING
    // ══════════════════════════════════════════════════════════════════════════

    public function getCanaux(): array
    {
        return [
            'gmb' => [
                'label'     => 'Google My Business',
                'icon'      => 'fab fa-google',
                'color'     => '#4285F4',
                'objectif'  => 'Visibilité locale, avis, Google Maps',
                'kpis'      => ['Vues fiche','Clics téléphone','Demandes itinéraire','Avis reçus','Score moyen'],
                'frequence' => '3 publications/semaine + réponse avis < 24h',
                'formats'   => ['Post actualité','Post offre','Post événement','Photo avant/après'],
                'niveaux'   => [3,4,5],
                'conseil'   => "Votre fiche GMB est votre vitrine Google n°1.",
            ],
            'facebook' => [
                'label'     => 'Facebook',
                'icon'      => 'fab fa-facebook',
                'color'     => '#1877F2',
                'objectif'  => 'Notoriété locale, génération de leads, communauté',
                'kpis'      => ['Portée organique','Engagement','Leads formulaire','Partages','Nouveaux abonnés'],
                'frequence' => '5 publications/semaine',
                'formats'   => ['Carrousel biens','Témoignage client','Conseil immo','Coulisses','Stats marché'],
                'niveaux'   => [1,2,3],
                'conseil'   => "Facebook = communauté locale. Humanisez votre image.",
            ],
            'instagram' => [
                'label'     => 'Instagram',
                'icon'      => 'fab fa-instagram',
                'color'     => '#E1306C',
                'objectif'  => 'Inspiration, personal branding, notoriété aspirationnelle',
                'kpis'      => ['Impressions','Reach','Saves','Story views','Followers'],
                'frequence' => '4-5 posts/semaine + stories quotidiennes',
                'formats'   => ['Photo bien','Reel visite','Before/After','Citations','Conseils'],
                'niveaux'   => [1,2],
                'conseil'   => "Instagram = esthétique et inspiration. Charte graphique cohérente.",
            ],
            'linkedin' => [
                'label'     => 'LinkedIn',
                'icon'      => 'fab fa-linkedin',
                'color'     => '#0A66C2',
                'objectif'  => 'Réseau B2B, partenaires, crédibilité professionnelle',
                'kpis'      => ['Vues profil','Connexions','Impressions posts','Partages','Messages entrants'],
                'frequence' => '3 publications/semaine',
                'formats'   => ['Article expert','Stats marché','Étude de cas','Actualité secteur'],
                'niveaux'   => [3,4],
                'conseil'   => "LinkedIn = crédibilité experte. Parlez chiffres et résultats.",
            ],
            'tiktok' => [
                'label'     => 'TikTok',
                'icon'      => 'fab fa-tiktok',
                'color'     => '#010101',
                'objectif'  => 'Viralité, jeune audience, contenus courts dynamiques',
                'kpis'      => ['Vues','Likes','Partages','Followers','Commentaires'],
                'frequence' => '5-7 vidéos/semaine',
                'formats'   => ['Visite rapide','Conseil 60s','Erreur à éviter','Révélation prix'],
                'niveaux'   => [1,2],
                'conseil'   => "TikTok = authenticité. Les 3 premières secondes font tout.",
            ],
            'seo' => [
                'label'     => 'SEO / Blog',
                'icon'      => 'fas fa-search',
                'color'     => '#10B981',
                'objectif'  => 'Trafic organique qualifié, leads entrants long terme',
                'kpis'      => ['Positions Google','Trafic organique','Taux rebond','Leads formulaire','Pages indexées'],
                'frequence' => '2-4 articles/mois',
                'formats'   => ['Guide complet','FAQ quartier','Comparatif quartiers','Étude de cas'],
                'niveaux'   => [2,3],
                'conseil'   => "SEO local : mots-clés géo-localisés. Secteurs, quartiers, villes adjacentes.",
            ],
            'google_ads' => [
                'label'     => 'Google Ads',
                'icon'      => 'fas fa-ad',
                'color'     => '#FBBC04',
                'objectif'  => 'Leads immédiats à forte intention',
                'kpis'      => ['Impressions','CTR','CPC','Conversions','Coût/lead'],
                'frequence' => 'Campagnes continues, révision hebdo',
                'formats'   => ['Search','Display retargeting','Local Service Ads'],
                'niveaux'   => [4,5],
                'conseil'   => "Ciblez : « estimation immobilière Bordeaux », « vendre maison Blanquefort ».",
            ],
            'facebook_ads' => [
                'label'     => 'Facebook Ads',
                'icon'      => 'fas fa-rectangle-ad',
                'color'     => '#1877F2',
                'objectif'  => 'Génération de leads, notoriété ciblée, retargeting',
                'kpis'      => ['CPM','CPC','CPL','ROAS','Taux de conversion'],
                'frequence' => 'Campagnes continues, test A/B hebdo',
                'formats'   => ['Lead Form','Carrousel biens','Vidéo visite','Estimation gratuite'],
                'niveaux'   => [2,3,4],
                'conseil'   => "Ciblage : propriétaires 40-60 ans, revenus +50k, secteur Bordeaux.",
            ],
            'email' => [
                'label'     => 'Email Marketing',
                'icon'      => 'fas fa-envelope-open-text',
                'color'     => '#6366F1',
                'objectif'  => 'Nurturing leads, fidélisation, relances',
                'kpis'      => ['Taux d\'ouverture','Taux de clic','Désinscriptions','Conversions'],
                'frequence' => '1 newsletter/semaine + séquences auto',
                'formats'   => ['Newsletter marché','Séquence bienvenue','Relance estimation'],
                'niveaux'   => [3,4,5],
                'conseil'   => "Segmentez : acheteurs / vendeurs / investisseurs.",
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STRUCTURES COPYWRITING
    // ══════════════════════════════════════════════════════════════════════════

    public function getStructures(): array
    {
        return [
            'MERE' => [
                'label'       => 'Méthode MERE',
                'color'       => '#6366f1',
                'description' => "Framework principal IMMO LOCAL+. Miroir → Émotion → Réassurance → Exclusivité.",
                'etapes'      => self::MERE_STRUCTURE,
                'usage'       => ['Pages de capture','Posts Facebook','Emails','Scripts vidéo','Fiches biens'],
                'niveaux'     => [2,3,4,5],
            ],
            'AIDA' => [
                'label'       => 'AIDA',
                'color'       => '#f59e0b',
                'description' => "Attention → Intérêt → Désir → Action. Framework classique pour les pubs.",
                'etapes'      => [
                    'A' => ['label' => 'Attention', 'color' => '#ef4444', 'desc' => "Accroche qui arrête le scroll.", 'exemple' => ""],
                    'I' => ['label' => 'Intérêt',   'color' => '#f59e0b', 'desc' => "Développer le contexte et le problème.", 'exemple' => ""],
                    'D' => ['label' => 'Désir',      'color' => '#8b5cf6', 'desc' => "Amplifier le bénéfice et la transformation.", 'exemple' => ""],
                    'Ac'=> ['label' => 'Action',     'color' => '#10b981', 'desc' => "CTA clair et unique.", 'exemple' => ""],
                ],
                'usage'       => ['Publicités Facebook/Google','Posts réseaux sociaux','Landing pages'],
                'niveaux'     => [1,2,3],
            ],
            'PAS' => [
                'label'       => 'PAS',
                'color'       => '#ec4899',
                'description' => "Problème → Agitation → Solution. Idéal pour les contenus axés douleur.",
                'etapes'      => [
                    'P' => ['label' => 'Problème',  'color' => '#ef4444', 'desc' => "Identifier le problème exact du prospect.", 'exemple' => ""],
                    'A' => ['label' => 'Agitation', 'color' => '#f59e0b', 'desc' => "Amplifier les conséquences si rien ne change.", 'exemple' => ""],
                    'S' => ['label' => 'Solution',  'color' => '#10b981', 'desc' => "Présenter votre solution comme l'évidence.", 'exemple' => ""],
                ],
                'usage'       => ['Emails nurturing','Articles blog','Scripts appel'],
                'niveaux'     => [2,3,4],
            ],
            'BAB' => [
                'label'       => 'BAB (Before / After / Bridge)',
                'color'       => '#14b8a6',
                'description' => "Avant → Après → Comment. Idéal pour les témoignages.",
                'etapes'      => [
                    'B1' => ['label' => 'Before', 'color' => '#64748b', 'desc' => "Décrire la situation difficile de départ.", 'exemple' => ""],
                    'A'  => ['label' => 'After',  'color' => '#10b981', 'desc' => "Peindre la vie après la transformation.", 'exemple' => ""],
                    'B2' => ['label' => 'Bridge', 'color' => '#6366f1', 'desc' => "Votre service comme pont entre les deux.", 'exemple' => ""],
                ],
                'usage'       => ['Témoignages clients','Posts Instagram','Vidéos success story'],
                'niveaux'     => [3,4,5],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // KPIs CONSOLIDÉS
    // ══════════════════════════════════════════════════════════════════════════

    public function getKpis(): array
    {
        $kpis = [
            'leads_total'    => 0,
            'leads_month'    => 0,
            'leads_hot'      => 0,
            'biens_actifs'   => 0,
            'articles_total' => 0,
            'pages_total'    => 0,
            'personas_total' => 0,
            'sujets_total'   => 0,
        ];

        $checks = [
            'leads'                => fn() => [
                'leads_total' => (int) $this->pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
                'leads_month' => (int) $this->pdo->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn(),
                'leads_hot'   => $this->colExists('leads','score')
                    ? (int) $this->pdo->query("SELECT COUNT(*) FROM leads WHERE score >= 70")->fetchColumn()
                    : 0,
            ],
            'properties'          => fn() => [
                'biens_actifs' => (int) $this->pdo->query(
                    $this->colExists('properties','status')
                        ? "SELECT COUNT(*) FROM properties WHERE status = 'active'"
                        : ($this->colExists('properties','statut')
                            ? "SELECT COUNT(*) FROM properties WHERE statut = 'active'"
                            : "SELECT COUNT(*) FROM properties")
                )->fetchColumn(),
            ],
            'articles'            => fn() => ['articles_total' => (int) $this->pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn()],
            'pages'               => fn() => ['pages_total'    => (int) $this->pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn()],
            'strategy_personas'   => fn() => ['personas_total' => (int) $this->pdo->query("SELECT COUNT(*) FROM strategy_personas")->fetchColumn()],
            'strategy_sujets'     => fn() => ['sujets_total'   => (int) $this->pdo->query("SELECT COUNT(*) FROM strategy_sujets")->fetchColumn()],
        ];

        foreach ($checks as $table => $fn) {
            if (!$this->tableExists($table)) continue;
            try { $kpis = array_merge($kpis, $fn()); } catch (\Exception $e) {}
        }

        return $kpis;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MIGRATION
    // ══════════════════════════════════════════════════════════════════════════

    public function getMigrationSql(): string
    {
        return "
CREATE TABLE IF NOT EXISTS `strategy_personas` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom`               VARCHAR(100) NOT NULL DEFAULT 'Persona',
    `prenom`            VARCHAR(100) DEFAULT NULL,
    `age_min`           TINYINT UNSIGNED DEFAULT 30,
    `age_max`           TINYINT UNSIGNED DEFAULT 55,
    `situation`         TEXT DEFAULT NULL,
    `objectif_principal`TEXT DEFAULT NULL,
    `peur_principale`   TEXT DEFAULT NULL,
    `desir_principal`   TEXT DEFAULT NULL,
    `niveau_conscience` TINYINT UNSIGNED DEFAULT 2,
    `canal_prefere`     VARCHAR(50) DEFAULT 'facebook',
    `message_cle`       TEXT DEFAULT NULL,
    `persona_type`      ENUM('acheteur','vendeur','investisseur','primo-accedant','autre') DEFAULT 'acheteur',
    `avatar_url`        VARCHAR(255) DEFAULT NULL,
    `notes`             TEXT DEFAULT NULL,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_niveau` (`niveau_conscience`),
    KEY `idx_type`   (`persona_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `strategy_sujets` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titre`             VARCHAR(255) NOT NULL,
    `description`       TEXT DEFAULT NULL,
    `canal`             VARCHAR(50) DEFAULT 'facebook',
    `niveau_conscience` TINYINT UNSIGNED DEFAULT 2,
    `objectif`          VARCHAR(50) DEFAULT 'notoriete',
    `format`            VARCHAR(100) DEFAULT NULL,
    `structure_mere`    TINYINT(1) DEFAULT 1,
    `priorite`          TINYINT UNSIGNED DEFAULT 5,
    `statut`            ENUM('idee','planifie','redige','publie','archive') DEFAULT 'idee',
    `persona_id`        INT UNSIGNED DEFAULT NULL,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_canal`    (`canal`),
    KEY `idx_statut`   (`statut`),
    KEY `idx_objectif` (`objectif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `strategy_offres` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titre`             VARCHAR(255) NOT NULL,
    `accroche`          VARCHAR(500) DEFAULT NULL,
    `description`       TEXT DEFAULT NULL,
    `benefices`         TEXT DEFAULT NULL COMMENT 'JSON',
    `preuves`           TEXT DEFAULT NULL COMMENT 'JSON',
    `cta`               VARCHAR(255) DEFAULT NULL,
    `canal`             VARCHAR(50) DEFAULT NULL,
    `persona_id`        INT UNSIGNED DEFAULT NULL,
    `niveau_conscience` TINYINT UNSIGNED DEFAULT 3,
    `ordre`             TINYINT UNSIGNED DEFAULT 10,
    `statut`            ENUM('brouillon','actif','archive') DEFAULT 'brouillon',
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    public function runMigration(): array
    {
        $results = [];
        foreach (array_filter(array_map('trim', explode(';', $this->getMigrationSql()))) as $sql) {
            try {
                $this->pdo->exec($sql);
                preg_match('/TABLE IF NOT EXISTS `([^`]+)`/', $sql, $m);
                $results[] = ['table' => $m[1] ?? '?', 'status' => 'ok'];
            } catch (\Exception $e) {
                preg_match('/TABLE IF NOT EXISTS `([^`]+)`/', $sql, $m);
                $results[] = ['table' => $m[1] ?? '?', 'status' => 'error', 'error' => $e->getMessage()];
            }
        }
        return $results;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════════════════════════

    private function tableExists(string $table): bool
    {
        try {
            return $this->pdo->query("SHOW TABLES LIKE " . $this->pdo->quote($table))->rowCount() > 0;
        } catch (\Exception $e) { return false; }
    }

    private function colExists(string $table, string $col): bool
    {
        try {
            return $this->pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'")->rowCount() > 0;
        } catch (\Exception $e) { return false; }
    }

    private function ensureTable(string $table): void
    {
        if (!$this->tableExists($table)) $this->runMigration();
    }

    private function buildFieldParams(array $allowed, array $data): array
    {
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (!array_key_exists($f, $data)) continue;
            $fields[]        = "`{$f}`";
            $params[":{$f}"] = $data[$f];
        }
        return [$fields, $params];
    }

    private function buildSet(array $fields, array $params): string
    {
        return implode(', ', array_map(
            fn($f, $k) => "{$f} = {$k}",
            $fields,
            array_keys($params)
        ));
    }

    private function colsVals(array $fields, array $params): array
    {
        return [implode(', ', $fields), implode(', ', array_keys($params))];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DONNÉES PAR DÉFAUT
    // ══════════════════════════════════════════════════════════════════════════

    private function defaultPersonas(): array
    {
        return [
            [
                'id' => 1, 'nom' => 'Marie', 'age_min' => 35, 'age_max' => 50,
                'persona_type'      => 'vendeur',
                'situation'         => 'Propriétaire souhaitant vendre pour acquérir plus grand.',
                'objectif_principal'=> 'Vendre rapidement au meilleur prix.',
                'peur_principale'   => 'Vendre en dessous du marché.',
                'desir_principal'   => 'Sérénité et sécurité dans la transaction.',
                'niveau_conscience' => 3, 'canal_prefere' => 'facebook',
                'message_cle'       => "Votre bien mérite le meilleur prix, avec un expert qui connaît vraiment votre quartier.",
            ],
            [
                'id' => 2, 'nom' => 'Thomas', 'age_min' => 28, 'age_max' => 40,
                'persona_type'      => 'primo-accedant',
                'situation'         => 'Locataire avec CDI, premier achat.',
                'objectif_principal'=> 'Acheter son premier appartement à Bordeaux.',
                'peur_principale'   => 'Payer trop cher, refus de prêt.',
                'desir_principal'   => 'Stabilité et fierté propriétaire.',
                'niveau_conscience' => 2, 'canal_prefere' => 'instagram',
                'message_cle'       => "Votre premier achat immobilier, accompagné à chaque étape.",
            ],
            [
                'id' => 3, 'nom' => 'Laurent', 'age_min' => 40, 'age_max' => 60,
                'persona_type'      => 'investisseur',
                'situation'         => 'Investisseur cherchant des biens à fort rendement locatif.',
                'objectif_principal'=> 'Trouver les meilleures opportunités dans le Grand Bordeaux.',
                'peur_principale'   => 'Mauvaise rentabilité, vacance locative.',
                'desir_principal'   => 'Patrimoine solide et revenus passifs.',
                'niveau_conscience' => 4, 'canal_prefere' => 'linkedin',
                'message_cle'       => "Les meilleures opportunités locatives à Bordeaux, avant tout le monde.",
            ],
        ];
    }

    private function defaultSujets(): array
    {
        return [
            ['id'=>1,'titre'=>'Les 5 erreurs à éviter quand on vend à Bordeaux','canal'=>'facebook','niveau_conscience'=>2,'objectif'=>'engagement','format'=>'post','statut'=>'idee','priorite'=>8],
            ['id'=>2,'titre'=>'Prix au m² à Blanquefort : la vérité en 2025','canal'=>'seo','niveau_conscience'=>3,'objectif'=>'leads','format'=>'article','statut'=>'idee','priorite'=>9],
            ['id'=>3,'titre'=>'Estimation gratuite de votre bien en 48h','canal'=>'google_ads','niveau_conscience'=>5,'objectif'=>'conversions','format'=>'landing','statut'=>'idee','priorite'=>10],
            ['id'=>4,'titre'=>'Visite exclusive — Maison 4P jardin Blanquefort','canal'=>'instagram','niveau_conscience'=>1,'objectif'=>'notoriete','format'=>'reel','statut'=>'idee','priorite'=>7],
            ['id'=>5,'titre'=>'Témoignage : vendu en 21 jours au prix demandé','canal'=>'facebook','niveau_conscience'=>3,'objectif'=>'engagement','format'=>'video','statut'=>'idee','priorite'=>9],
        ];
    }

    private function defaultOffres(): array
    {
        return [
            [
                'id'=>1,'titre'=>'Estimation Gratuite','statut'=>'actif','canal'=>'all','niveau_conscience'=>3,'ordre'=>1,
                'accroche'    => 'Découvrez la vraie valeur de votre bien en 48h, sans engagement.',
                'description' => 'Analyse comparative de marché, visite, rapport détaillé offert.',
                'cta'         => 'Demander mon estimation gratuite',
            ],
            [
                'id'=>2,'titre'=>'Accompagnement Vente','statut'=>'actif','canal'=>'all','niveau_conscience'=>4,'ordre'=>2,
                'accroche'    => 'Vendez au meilleur prix, en toute sérénité, avec un expert Bordeaux.',
                'description' => 'Prise en charge complète : estimation, photos pro, diffusion multi-portails, négociation, acte.',
                'cta'         => 'Prendre rendez-vous',
            ],
        ];
    }
}