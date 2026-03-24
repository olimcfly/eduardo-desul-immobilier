<?php
/**
 * MarketAnalyzer.php
 * Classe d'analyse du marché immobilier par ville
 * Utilise Perplexity/Claude/OpenAI pour générer des analyses data-driven
 */

class MarketAnalyzer {

    private $pdo;
    private $user_id;
    private $cache_hours = 24; // Cache analyses pendant 24h

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->ensureTable();
    }

    /**
     * Auto-création des tables nécessaires
     */
    private function ensureTable() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_analyses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `city` VARCHAR(100) NOT NULL,
            `analysis_data` JSON DEFAULT NULL COMMENT 'Données structurées de l analyse',
            `analysis_html` LONGTEXT DEFAULT NULL COMMENT 'Rendu HTML complet',
            `sources` JSON DEFAULT NULL COMMENT 'Sources et citations',
            `status` ENUM('pending','running','completed','error') DEFAULT 'pending',
            `error_message` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_user_city` (`user_id`, `city`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_cities` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `city` VARCHAR(100) NOT NULL,
            `department` VARCHAR(5) DEFAULT NULL,
            `region` VARCHAR(100) DEFAULT NULL,
            `is_primary` TINYINT(1) DEFAULT 0 COMMENT 'Ville principale (onboarding)',
            `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_user_city` (`user_id`, `city`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_cluster_plans` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `city` VARCHAR(120) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'planned',
            `input_hash` VARCHAR(64) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_user_hash` (`user_id`, `input_hash`),
            INDEX `idx_user_city` (`user_id`, `city`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_clusters` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `plan_id` INT NOT NULL,
            `keyword` VARCHAR(255) NOT NULL,
            `intent` VARCHAR(50) DEFAULT NULL,
            `score` TINYINT UNSIGNED DEFAULT 0,
            `status` VARCHAR(20) NOT NULL DEFAULT 'planned',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_plan` (`plan_id`),
            CONSTRAINT `fk_market_cluster_plan` FOREIGN KEY (`plan_id`) REFERENCES `market_cluster_plans`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_cluster_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `cluster_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `outline_json` JSON DEFAULT NULL,
            `article_id` INT DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'planned',
            `error_message` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_cluster` (`cluster_id`),
            INDEX `idx_article` (`article_id`),
            CONSTRAINT `fk_market_cluster_items_cluster` FOREIGN KEY (`cluster_id`) REFERENCES `market_clusters`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_cluster_jobs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type` VARCHAR(80) NOT NULL,
            `payload_json` JSON DEFAULT NULL,
            `dedupe_key` VARCHAR(190) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `attempts` INT NOT NULL DEFAULT 0,
            `max_attempts` INT NOT NULL DEFAULT 3,
            `next_run_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `locked_at` DATETIME DEFAULT NULL,
            `last_error` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_dedupe` (`dedupe_key`),
            INDEX `idx_status_run` (`status`, `next_run_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `market_cluster_link_plans` (
            `plan_id` INT PRIMARY KEY,
            `link_json` JSON DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_market_link_plan` FOREIGN KEY (`plan_id`) REFERENCES `market_cluster_plans`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * Récupère les villes de l'utilisateur
     */
    public function getUserCities() {
        $cities = [];

        // 1. Ville du profil launchpad
        try {
            $stmt = $this->pdo->prepare("
                SELECT lp.city
                FROM launchpad_profiles lp
                JOIN launchpad l ON l.id = lp.launchpad_id
                WHERE l.user_id = ? AND lp.city IS NOT NULL AND lp.city != ''
                ORDER BY lp.updated_at DESC LIMIT 1
            ");
            $stmt->execute([$this->user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['city'])) {
                $cities[] = [
                    'city' => $row['city'],
                    'source' => 'onboarding',
                    'is_primary' => true
                ];
            }
        } catch (Exception $e) {
            // Table peut ne pas exister
        }

        // 2. Villes ajoutées manuellement
        try {
            $stmt = $this->pdo->prepare("
                SELECT city, is_primary FROM market_cities
                WHERE user_id = ? ORDER BY is_primary DESC, added_at DESC
            ");
            $stmt->execute([$this->user_id]);
            $manual = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($manual as $m) {
                // Éviter doublons
                $exists = false;
                foreach ($cities as $c) {
                    if (strtolower(trim($c['city'])) === strtolower(trim($m['city']))) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $cities[] = [
                        'city' => $m['city'],
                        'source' => 'manual',
                        'is_primary' => (bool)$m['is_primary']
                    ];
                }
            }
        } catch (Exception $e) {}

        return $cities;
    }

    /**
     * Ajoute une ville à la liste
     */
    public function addCity($city, $department = null, $region = null) {
        $city = trim($city);
        if (empty($city)) return false;

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO market_cities (user_id, city, department, region)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE department = COALESCE(VALUES(department), department)
            ");
            $stmt->execute([$this->user_id, $city, $department, $region]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprime une ville
     */
    public function removeCity($city) {
        $stmt = $this->pdo->prepare("DELETE FROM market_cities WHERE user_id = ? AND city = ?");
        return $stmt->execute([$this->user_id, trim($city)]);
    }

    /**
     * Récupère la dernière analyse pour une ville (si pas expirée)
     */
    public function getCachedAnalysis($city) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM market_analyses
            WHERE user_id = ? AND city = ? AND status = 'completed'
            AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$this->user_id, trim($city), $this->cache_hours]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les analyses de l'utilisateur
     */
    public function getAllAnalyses() {
        $stmt = $this->pdo->prepare("
            SELECT id, city, status, created_at,
                   JSON_EXTRACT(analysis_data, '$.prix_m2_maison') as prix_maison,
                   JSON_EXTRACT(analysis_data, '$.prix_m2_appart') as prix_appart,
                   JSON_EXTRACT(analysis_data, '$.nb_transactions') as nb_transactions
            FROM market_analyses
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lance une nouvelle analyse via l'API Claude
     */
    public function runAnalysis($city) {
        $city = trim($city);
        if (empty($city)) {
            return ['success' => false, 'error' => 'Ville manquante'];
        }

        // Vérifier cache
        $cached = $this->getCachedAnalysis($city);
        if ($cached) {
            return [
                'success' => true,
                'cached' => true,
                'analysis' => $cached
            ];
        }

        // Créer l'entrée
        $stmt = $this->pdo->prepare("
            INSERT INTO market_analyses (user_id, city, status) VALUES (?, ?, 'running')
        ");
        $stmt->execute([$this->user_id, $city]);
        $analysisId = $this->pdo->lastInsertId();

        try {
            $result = $this->callAIForAnalysis($city);

            // Sauvegarder
            $stmt = $this->pdo->prepare("
                UPDATE market_analyses
                SET status = 'completed',
                    analysis_data = ?,
                    analysis_html = ?,
                    sources = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($result['data'], JSON_UNESCAPED_UNICODE),
                $result['html'],
                json_encode($result['sources'] ?? [], JSON_UNESCAPED_UNICODE),
                $analysisId
            ]);

            // Ajouter la ville si pas encore dans la liste
            $this->addCity($city);

            return [
                'success' => true,
                'cached' => false,
                'analysis' => [
                    'id' => $analysisId,
                    'city' => $city,
                    'analysis_data' => json_encode($result['data'], JSON_UNESCAPED_UNICODE),
                    'analysis_html' => $result['html'],
                    'sources' => json_encode($result['sources'] ?? [], JSON_UNESCAPED_UNICODE),
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];

        } catch (Exception $e) {
            $stmt = $this->pdo->prepare("
                UPDATE market_analyses SET status = 'error', error_message = ? WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $analysisId]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Appel IA pour l'analyse
     */
    private function callAIForAnalysis($city) {
        $currentDate = date('F Y');
        $currentMonth = date('m/Y');

        $prompt = $this->buildAnalysisPrompt($city, $currentDate, $currentMonth);

        // Priorité Perplexity (données web/citations), fallback Claude puis OpenAI
        if (defined('PERPLEXITY_API_KEY') && !empty(PERPLEXITY_API_KEY)) {
            return $this->callPerplexity($prompt, $city);
        } elseif (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY)) {
            return $this->callClaude($prompt, $city);
        } elseif (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY)) {
            return $this->callOpenAI($prompt, $city);
        }

        throw new Exception('Aucune clé API IA configurée (Perplexity, Claude ou OpenAI requis)');
    }

    /**
     * Appel API Perplexity
     */
    private function callPerplexity($prompt, $city) {
        $endpoint = defined('PERPLEXITY_ENDPOINT') && !empty(PERPLEXITY_ENDPOINT)
            ? PERPLEXITY_ENDPOINT
            : 'https://api.perplexity.ai/chat/completions';

        $model = defined('PERPLEXITY_MODEL') && !empty(PERPLEXITY_MODEL)
            ? PERPLEXITY_MODEL
            : 'sonar-pro';

        $payload = json_encode([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert du marché immobilier français. Réponds uniquement en JSON valide, sans markdown.'
                ],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2,
            'max_tokens' => 4000
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . PERPLEXITY_API_KEY
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erreur réseau Perplexity: $error");
        }
        if ($httpCode !== 200) {
            throw new Exception("Erreur API Perplexity (HTTP $httpCode): " . substr((string)$response, 0, 300));
        }

        $result = json_decode((string)$response, true);
        $text = $result['choices'][0]['message']['content'] ?? '';

        $parsed = $this->parseAIResponse($text, $city);
        $citations = $result['citations'] ?? [];
        if (!empty($citations) && is_array($citations)) {
            $parsed['sources'] = array_values(array_unique(array_merge($parsed['sources'] ?? [], $citations)));
        }

        return $parsed;
    }

    /**
     * Construit le prompt d'analyse
     */
    private function buildAnalysisPrompt($city, $currentDate, $currentMonth) {
        return <<<PROMPT
Tu es un expert en analyse du marché immobilier français. Analyse le marché immobilier de **{$city}** en {$currentDate}.

Tu dois fournir une réponse structurée en JSON avec les données suivantes. Utilise des données réalistes et cohérentes basées sur les tendances connues du marché immobilier français.

Réponds UNIQUEMENT avec un JSON valide (pas de texte avant/après), avec cette structure exacte :

{
  "ville": "{$city}",
  "date_analyse": "{$currentMonth}",
  "resume": "1-2 phrases de résumé du marché",

  "transactions": {
    "nb_ventes_12mois": 0,
    "nb_maisons": 0,
    "nb_appartements": 0,
    "evolution_pct": 0,
    "commentaire": "Texte explicatif 2-3 phrases",
    "source": "DVF / Notaires de France"
  },

  "prix": {
    "maison": {
      "prix_m2_median": 0,
      "evolution_1an_pct": 0,
      "fourchette_basse": 0,
      "fourchette_haute": 0
    },
    "appartement": {
      "prix_m2_median": 0,
      "evolution_1an_pct": 0,
      "fourchette_basse": 0,
      "fourchette_haute": 0
    },
    "commentaire": "Tendances prix",
    "source": "DVF / MeilleursAgents / Notaires"
  },

  "annonces": {
    "total_estimé": 0,
    "maisons": 0,
    "appartements": 0,
    "repartition_sites": {
      "SeLoger": 0,
      "Leboncoin": 0,
      "BienIci": 0,
      "PAP": 0,
      "LogicImmo": 0
    },
    "delai_vente_moyen_jours": 0,
    "commentaire": "Offre actuelle",
    "source": "Estimations portails immobiliers"
  },

  "mots_cles_seo": [
    {"mot_cle": "maison à vendre {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0},
    {"mot_cle": "vente maison {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0},
    {"mot_cle": "vendre maison {$city}", "volume_mensuel": 0, "concurrence": "moyenne", "cpc_eur": 0},
    {"mot_cle": "maison à vendre à {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0},
    {"mot_cle": "appartement à vendre {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0},
    {"mot_cle": "prix immobilier {$city}", "volume_mensuel": 0, "concurrence": "moyenne", "cpc_eur": 0},
    {"mot_cle": "estimation immobilière {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0},
    {"mot_cle": "agent immobilier {$city}", "volume_mensuel": 0, "concurrence": "élevée", "cpc_eur": 0}
  ],

  "conseils_business": [
    "Conseil 1 pour un agent immobilier sur ce marché",
    "Conseil 2",
    "Conseil 3"
  ],

  "opportunites": {
    "score_marche": 0,
    "points_forts": ["point 1", "point 2"],
    "points_vigilance": ["point 1", "point 2"],
    "strategie_recommandee": "Texte stratégie"
  }
}

IMPORTANT :
- Les prix doivent être réalistes pour {$city} en 2025-2026
- Les volumes de mots-clés SEO entre 100 et 8000/mois selon la taille de la ville
- Le score marché entre 1 et 10
- Tous les chiffres doivent être cohérents entre eux
- Remplis TOUS les champs numériques avec des valeurs > 0
PROMPT;
    }

    /**
     * Appel API Claude (Anthropic)
     */
    private function callClaude($prompt, $city) {
        $url = 'https://api.anthropic.com/v1/messages';

        $payload = json_encode([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . ANTHROPIC_API_KEY,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erreur réseau Claude: $error");
        }
        if ($httpCode !== 200) {
            throw new Exception("Erreur API Claude (HTTP $httpCode): " . substr($response, 0, 300));
        }

        $result = json_decode($response, true);
        $text = $result['content'][0]['text'] ?? '';

        return $this->parseAIResponse($text, $city);
    }

    /**
     * Appel API OpenAI (fallback)
     */
    private function callOpenAI($prompt, $city) {
        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = json_encode([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un expert immobilier. Réponds uniquement en JSON valide.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.3
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erreur réseau OpenAI: $error");
        }
        if ($httpCode !== 200) {
            throw new Exception("Erreur API OpenAI (HTTP $httpCode): " . substr($response, 0, 300));
        }

        $result = json_decode($response, true);
        $text = $result['choices'][0]['message']['content'] ?? '';

        return $this->parseAIResponse($text, $city);
    }

    /**
     * Parse la réponse AI et génère le HTML
     */
    private function parseAIResponse($text, $city) {
        // Extraire le JSON de la réponse
        $text = trim($text);

        // Chercher le JSON dans la réponse
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $text;
        }

        $data = json_decode($jsonStr, true);

        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Réponse IA non parsable en JSON: ' . json_last_error_msg());
        }

        // Générer le HTML
        $html = $this->renderAnalysisHTML($data, $city);

        // Extraire les sources
        $sources = [];
        if (isset($data['transactions']['source'])) $sources[] = $data['transactions']['source'];
        if (isset($data['prix']['source'])) $sources[] = $data['prix']['source'];
        if (isset($data['annonces']['source'])) $sources[] = $data['annonces']['source'];

        return [
            'data' => $data,
            'html' => $html,
            'sources' => array_unique($sources)
        ];
    }

    /**
     * Génère le HTML de l'analyse
     */
    private function renderAnalysisHTML($d, $city) {
        $city = htmlspecialchars($city);
        $resume = htmlspecialchars($d['resume'] ?? '');

        // Transactions
        $nbVentes = number_format($d['transactions']['nb_ventes_12mois'] ?? 0, 0, ',', ' ');
        $nbMaisons = number_format($d['transactions']['nb_maisons'] ?? 0, 0, ',', ' ');
        $nbApparts = number_format($d['transactions']['nb_appartements'] ?? 0, 0, ',', ' ');
        $evolTrans = $d['transactions']['evolution_pct'] ?? 0;
        $evolTransSign = $evolTrans >= 0 ? '+' : '';
        $evolTransColor = $evolTrans >= 0 ? '#10b981' : '#ef4444';
        $commentTrans = htmlspecialchars($d['transactions']['commentaire'] ?? '');
        $sourceTrans = htmlspecialchars($d['transactions']['source'] ?? 'DVF');

        // Prix
        $pxMaison = number_format($d['prix']['maison']['prix_m2_median'] ?? 0, 0, ',', ' ');
        $pxAppart = number_format($d['prix']['appartement']['prix_m2_median'] ?? 0, 0, ',', ' ');
        $evolMaison = $d['prix']['maison']['evolution_1an_pct'] ?? 0;
        $evolAppart = $d['prix']['appartement']['evolution_1an_pct'] ?? 0;
        $evolMaisonSign = $evolMaison >= 0 ? '+' : '';
        $evolAppartSign = $evolAppart >= 0 ? '+' : '';
        $evolMaisonColor = $evolMaison >= 0 ? '#10b981' : '#ef4444';
        $evolAppartColor = $evolAppart >= 0 ? '#10b981' : '#ef4444';
        $fBassM = number_format($d['prix']['maison']['fourchette_basse'] ?? 0, 0, ',', ' ');
        $fHautM = number_format($d['prix']['maison']['fourchette_haute'] ?? 0, 0, ',', ' ');
        $fBassA = number_format($d['prix']['appartement']['fourchette_basse'] ?? 0, 0, ',', ' ');
        $fHautA = number_format($d['prix']['appartement']['fourchette_haute'] ?? 0, 0, ',', ' ');
        $commentPrix = htmlspecialchars($d['prix']['commentaire'] ?? '');
        $sourcePrix = htmlspecialchars($d['prix']['source'] ?? 'DVF / Notaires');

        // Annonces
        $totalAnnonces = number_format($d['annonces']['total_estimé'] ?? $d['annonces']['total_estime'] ?? 0, 0, ',', ' ');
        $annoncesMaisons = number_format($d['annonces']['maisons'] ?? 0, 0, ',', ' ');
        $annoncesApparts = number_format($d['annonces']['appartements'] ?? 0, 0, ',', ' ');
        $delaiVente = $d['annonces']['delai_vente_moyen_jours'] ?? 0;
        $repartition = $d['annonces']['repartition_sites'] ?? [];
        $commentAnnonces = htmlspecialchars($d['annonces']['commentaire'] ?? '');

        // Score
        $score = $d['opportunites']['score_marche'] ?? 5;
        $scoreColor = $score >= 7 ? '#10b981' : ($score >= 5 ? '#f59e0b' : '#ef4444');
        $scoreLabel = $score >= 7 ? 'Marché porteur' : ($score >= 5 ? 'Marché stable' : 'Marché tendu');

        // Mots-clés SEO
        $kwRows = '';
        foreach (($d['mots_cles_seo'] ?? []) as $kw) {
            $vol = number_format($kw['volume_mensuel'] ?? 0, 0, ',', ' ');
            $conc = htmlspecialchars($kw['concurrence'] ?? 'moyenne');
            $concColor = $conc === 'élevée' ? '#ef4444' : ($conc === 'moyenne' ? '#f59e0b' : '#10b981');
            $cpc = number_format($kw['cpc_eur'] ?? 0, 2, ',', ' ');
            $motCle = htmlspecialchars($kw['mot_cle'] ?? '');
            $kwRows .= "<tr>
                <td style='font-weight:500'>{$motCle}</td>
                <td style='text-align:center'>{$vol}</td>
                <td style='text-align:center'><span style='color:{$concColor};font-weight:600'>{$conc}</span></td>
                <td style='text-align:center'>{$cpc} &euro;</td>
            </tr>";
        }

        // Répartition annonces
        $repartRows = '';
        foreach ($repartition as $site => $nb) {
            $nbF = number_format($nb, 0, ',', ' ');
            $site = htmlspecialchars($site);
            $repartRows .= "<div class='ma-site-row'><span class='ma-site-name'>{$site}</span><span class='ma-site-count'>{$nbF}</span></div>";
        }

        // Conseils
        $conseilsHtml = '';
        foreach (($d['conseils_business'] ?? []) as $i => $conseil) {
            $num = $i + 1;
            $conseil = htmlspecialchars($conseil);
            $conseilsHtml .= "<div class='ma-conseil'><span class='ma-conseil-num'>{$num}</span><span>{$conseil}</span></div>";
        }

        // Points forts / vigilance
        $pointsForts = '';
        foreach (($d['opportunites']['points_forts'] ?? []) as $p) {
            $p = htmlspecialchars($p);
            $pointsForts .= "<li><i class='fas fa-check' style='color:#10b981;margin-right:6px'></i>{$p}</li>";
        }
        $pointsVig = '';
        foreach (($d['opportunites']['points_vigilance'] ?? []) as $p) {
            $p = htmlspecialchars($p);
            $pointsVig .= "<li><i class='fas fa-exclamation-triangle' style='color:#f59e0b;margin-right:6px'></i>{$p}</li>";
        }

        $strategie = htmlspecialchars($d['opportunites']['strategie_recommandee'] ?? '');

        return <<<HTML
<div class="ma-report">

    <!-- Header -->
    <div class="ma-header">
        <div class="ma-header-left">
            <h2><i class="fas fa-chart-line" style="color:var(--accent,#c9913b);margin-right:8px"></i>Analyse Marché — {$city}</h2>
            <p class="ma-resume">{$resume}</p>
        </div>
        <div class="ma-score-badge" style="border-color:{$scoreColor}">
            <div class="ma-score-val" style="color:{$scoreColor}">{$score}/10</div>
            <div class="ma-score-label">{$scoreLabel}</div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="ma-kpis">
        <div class="ma-kpi">
            <div class="ma-kpi-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="fas fa-handshake"></i></div>
            <div class="ma-kpi-val">{$nbVentes}</div>
            <div class="ma-kpi-label">Ventes (12 mois)</div>
            <div class="ma-kpi-trend" style="color:{$evolTransColor}">{$evolTransSign}{$evolTrans}%</div>
        </div>
        <div class="ma-kpi">
            <div class="ma-kpi-icon" style="background:rgba(201,145,59,.1);color:#c9913b"><i class="fas fa-house"></i></div>
            <div class="ma-kpi-val">{$pxMaison} &euro;/m&sup2;</div>
            <div class="ma-kpi-label">Prix maisons</div>
            <div class="ma-kpi-trend" style="color:{$evolMaisonColor}">{$evolMaisonSign}{$evolMaison}%</div>
        </div>
        <div class="ma-kpi">
            <div class="ma-kpi-icon" style="background:rgba(13,162,113,.1);color:#0da271"><i class="fas fa-building"></i></div>
            <div class="ma-kpi-val">{$pxAppart} &euro;/m&sup2;</div>
            <div class="ma-kpi-label">Prix appartements</div>
            <div class="ma-kpi-trend" style="color:{$evolAppartColor}">{$evolAppartSign}{$evolAppart}%</div>
        </div>
        <div class="ma-kpi">
            <div class="ma-kpi-icon" style="background:rgba(220,38,38,.1);color:#dc2626"><i class="fas fa-tags"></i></div>
            <div class="ma-kpi-val">{$totalAnnonces}</div>
            <div class="ma-kpi-label">Annonces en ligne</div>
            <div class="ma-kpi-trend"><i class="fas fa-clock"></i> {$delaiVente}j délai moyen</div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="ma-section">
        <h3><i class="fas fa-handshake"></i> Volumes de transactions</h3>
        <p>{$commentTrans}</p>
        <div class="ma-grid-2">
            <div class="ma-stat-box">
                <div class="ma-stat-label">Maisons vendues</div>
                <div class="ma-stat-val">{$nbMaisons}</div>
            </div>
            <div class="ma-stat-box">
                <div class="ma-stat-label">Appartements vendus</div>
                <div class="ma-stat-val">{$nbApparts}</div>
            </div>
        </div>
        <div class="ma-source">Source : {$sourceTrans}</div>
    </div>

    <!-- Prix -->
    <div class="ma-section">
        <h3><i class="fas fa-euro-sign"></i> Prix au m&sup2;</h3>
        <p>{$commentPrix}</p>
        <table class="ma-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Prix m&sup2; médian</th>
                    <th>Fourchette</th>
                    <th>Évolution 1 an</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="fas fa-house" style="color:#c9913b;margin-right:6px"></i>Maisons</td>
                    <td style="font-weight:700">{$pxMaison} &euro;</td>
                    <td>{$fBassM} — {$fHautM} &euro;</td>
                    <td><span style="color:{$evolMaisonColor};font-weight:600">{$evolMaisonSign}{$evolMaison}%</span></td>
                </tr>
                <tr>
                    <td><i class="fas fa-building" style="color:#0da271;margin-right:6px"></i>Appartements</td>
                    <td style="font-weight:700">{$pxAppart} &euro;</td>
                    <td>{$fBassA} — {$fHautA} &euro;</td>
                    <td><span style="color:{$evolAppartColor};font-weight:600">{$evolAppartSign}{$evolAppart}%</span></td>
                </tr>
            </tbody>
        </table>
        <div class="ma-source">Source : {$sourcePrix}</div>
    </div>

    <!-- Annonces -->
    <div class="ma-section">
        <h3><i class="fas fa-tags"></i> Annonces actuelles</h3>
        <p>{$commentAnnonces}</p>
        <div class="ma-grid-2">
            <div>
                <div class="ma-stat-box">
                    <div class="ma-stat-label">Maisons à vendre</div>
                    <div class="ma-stat-val">{$annoncesMaisons}</div>
                </div>
                <div class="ma-stat-box" style="margin-top:8px">
                    <div class="ma-stat-label">Appartements à vendre</div>
                    <div class="ma-stat-val">{$annoncesApparts}</div>
                </div>
            </div>
            <div class="ma-sites-list">
                <div class="ma-sites-title">Répartition par portail</div>
                {$repartRows}
            </div>
        </div>
    </div>

    <!-- Mots-clés SEO -->
    <div class="ma-section">
        <h3><i class="fas fa-search"></i> Mots-clés SEO</h3>
        <p>Volumes de recherche mensuels estimés (Google Keyword Planner / SEMrush)</p>
        <table class="ma-table">
            <thead>
                <tr>
                    <th>Mot-clé</th>
                    <th style="text-align:center">Volume/mois</th>
                    <th style="text-align:center">Concurrence</th>
                    <th style="text-align:center">CPC estimé</th>
                </tr>
            </thead>
            <tbody>
                {$kwRows}
            </tbody>
        </table>
        <div class="ma-source">Source : estimations SEMrush / Google Keyword Planner</div>
    </div>

    <!-- Opportunités -->
    <div class="ma-section">
        <h3><i class="fas fa-lightbulb"></i> Opportunités &amp; Stratégie</h3>
        <div class="ma-grid-2">
            <div>
                <h4 style="color:#10b981;margin:0 0 8px"><i class="fas fa-arrow-up"></i> Points forts</h4>
                <ul class="ma-points-list">{$pointsForts}</ul>
            </div>
            <div>
                <h4 style="color:#f59e0b;margin:0 0 8px"><i class="fas fa-exclamation-triangle"></i> Points de vigilance</h4>
                <ul class="ma-points-list">{$pointsVig}</ul>
            </div>
        </div>
        <div class="ma-strategie">
            <strong><i class="fas fa-chess"></i> Stratégie recommandée :</strong>
            <p>{$strategie}</p>
        </div>
    </div>

    <!-- Conseils business -->
    <div class="ma-section">
        <h3><i class="fas fa-rocket"></i> Conseils business</h3>
        {$conseilsHtml}
    </div>

</div>
HTML;
    }
}
