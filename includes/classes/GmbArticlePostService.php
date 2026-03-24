<?php
/**
 * GmbArticlePostService — Gestion des posts GMB liés aux articles
 *
 * - Auto-création de la table gmb_article_posts
 * - Génération de contenu via IA (Claude/OpenAI)
 * - Publication sur Google Business Profile API
 * - CRUD complet
 */
declare(strict_types=1);

class GmbArticlePostService
{
    private PDO $pdo;
    private static bool $tableChecked = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    // =========================================================================
    //  TABLE AUTO-CREATE
    // =========================================================================
    private function ensureTable(): void
    {
        if (self::$tableChecked) return;
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `gmb_article_posts` (
                `id`              INT AUTO_INCREMENT PRIMARY KEY,
                `article_id`      INT NOT NULL,
                `post_text`       TEXT NOT NULL,
                `preview_100`     VARCHAR(120) DEFAULT NULL,
                `cta_button`      VARCHAR(50) DEFAULT 'En savoir plus',
                `cta_url`         VARCHAR(500) DEFAULT NULL,
                `image_suggestion`TEXT DEFAULT NULL,
                `status`          ENUM('draft','pending','published','failed') DEFAULT 'draft',
                `gmb_post_id`     VARCHAR(255) DEFAULT NULL,
                `gmb_error`       TEXT DEFAULT NULL,
                `provider`        VARCHAR(20) DEFAULT NULL,
                `published_at`    DATETIME DEFAULT NULL,
                `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_article` (`article_id`),
                INDEX `idx_status`  (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            self::$tableChecked = true;
        } catch (Throwable $e) {
            self::$tableChecked = true; // avoid retries
        }
    }

    // =========================================================================
    //  CRUD
    // =========================================================================

    /**
     * Retourne tous les posts GMB d'un article.
     * @return array<int, array>
     */
    public function getByArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM gmb_article_posts WHERE article_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne un post par ID.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM gmb_article_posts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Compte les posts par article avec détail par statut.
     * @return array{total:int, published:int, draft:int, pending:int, failed:int}
     */
    public function countByArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT status, COUNT(*) as cnt FROM gmb_article_posts WHERE article_id = ? GROUP BY status"
        );
        $stmt->execute([$articleId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['total' => 0, 'published' => 0, 'draft' => 0, 'pending' => 0, 'failed' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']] = (int)$r['cnt'];
            $counts['total'] += (int)$r['cnt'];
        }
        return $counts;
    }

    /**
     * Compte les posts GMB pour plusieurs articles (batch).
     * @param int[] $articleIds
     * @return array<int, array{total:int, published:int}>
     */
    public function countByArticles(array $articleIds): array
    {
        if (empty($articleIds)) return [];
        $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT article_id, status, COUNT(*) as cnt
             FROM gmb_article_posts
             WHERE article_id IN ({$placeholders})
             GROUP BY article_id, status"
        );
        $stmt->execute(array_map('intval', $articleIds));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $aid = (int)$r['article_id'];
            if (!isset($result[$aid])) {
                $result[$aid] = ['total' => 0, 'published' => 0, 'draft' => 0, 'pending' => 0, 'failed' => 0];
            }
            $result[$aid][$r['status']] = (int)$r['cnt'];
            $result[$aid]['total'] += (int)$r['cnt'];
        }
        return $result;
    }

    /**
     * Supprime un post GMB.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM gmb_article_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    //  AI GENERATION
    // =========================================================================

    /**
     * Génère un post GMB via IA à partir d'un article.
     *
     * @param array $article Données de l'article (titre, extrait, contenu)
     * @return array{success:bool, post_id?:int, post?:array, error?:string}
     */
    public function generateFromArticle(array $article): array
    {
        $articleId = (int)($article['id'] ?? 0);
        if (!$articleId) {
            return ['success' => false, 'error' => 'ID article manquant'];
        }

        $title   = $article['titre'] ?? $article['title'] ?? '';
        $excerpt = $article['extrait'] ?? $article['excerpt'] ?? '';
        $content = strip_tags($article['contenu'] ?? $article['content'] ?? '');
        $slug    = $article['slug'] ?? '';

        // Tronquer le contenu pour le prompt
        $contentPreview = mb_substr($content, 0, 800);

        $prompt = $this->buildPrompt($title, $excerpt, $contentPreview);
        $system = $this->buildSystemContext();

        // Appel IA via AiClient si disponible, sinon cURL direct
        $aiResult = $this->callAI($prompt, $system);

        if (!$aiResult['success']) {
            return ['success' => false, 'error' => $aiResult['error'] ?? 'Erreur IA'];
        }

        // Parser le JSON de la réponse
        $parsed = $this->extractJson($aiResult['content']);

        $postText   = $parsed['post_text'] ?? $aiResult['content'];
        $preview    = $parsed['preview_100'] ?? mb_substr(strip_tags($postText), 0, 100);
        $ctaButton  = $parsed['cta_button'] ?? 'En savoir plus';
        $imageSugg  = $parsed['image_description'] ?? null;

        // Construire l'URL CTA
        $siteUrl = $this->getSiteUrl();
        $ctaUrl  = $siteUrl . '/blog/' . $slug;

        // Sauvegarder en base
        $stmt = $this->pdo->prepare(
            "INSERT INTO gmb_article_posts
                (article_id, post_text, preview_100, cta_button, cta_url, image_suggestion, status, provider, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'draft', ?, NOW())"
        );
        $stmt->execute([
            $articleId,
            $postText,
            $preview,
            $ctaButton,
            $ctaUrl,
            $imageSugg,
            $aiResult['provider'] ?? 'claude',
        ]);
        $postId = (int)$this->pdo->lastInsertId();

        $post = $this->getById($postId);

        // Tenter la publication automatique si GMB API configurée
        $publishResult = $this->tryPublish($postId);

        // Re-fetch pour avoir le statut à jour
        $post = $this->getById($postId);

        return [
            'success'   => true,
            'post_id'   => $postId,
            'post'      => $post,
            'published' => $publishResult['published'] ?? false,
        ];
    }

    /**
     * Construit le prompt pour la génération de post GMB.
     */
    private function buildPrompt(string $title, string $excerpt, string $content): string
    {
        $schema = json_encode([
            'post_text'         => '...(max 1500 car, optimisé GMB)',
            'preview_100'       => '...(100 premiers caractères accrocheurs)',
            'cta_button'        => 'En savoir plus',
            'image_description' => '...(suggestion image pour le post)',
            'keywords_included' => ['...'],
        ], JSON_UNESCAPED_UNICODE);

        return "Génère un post Google My Business à partir de cet article de blog immobilier.

**Titre de l'article** : {$title}
**Extrait** : {$excerpt}
**Contenu (début)** : {$content}

Consignes :
- Maximum 1500 caractères
- Les 100 premiers caractères sont CRUCIAUX (visibles sans clic)
- Ton professionnel mais accessible, adapté à l'immobilier bordelais
- Inclure des mots-clés locaux naturellement (Bordeaux, Gironde, etc.)
- Pas de hashtags (Google My Business ≠ réseaux sociaux)
- Terminer par un appel à l'action invitant à lire l'article
- Le post doit donner envie de cliquer sans répéter l'article

Réponds UNIQUEMENT en JSON valide avec ce schéma :
{$schema}";
    }

    /**
     * Contexte système pour la génération.
     */
    private function buildSystemContext(): string
    {
        return "Tu es l'assistant marketing digital d'Eduardo De Sul, conseiller immobilier eXp France basé à Bordeaux. "
             . "Tu rédiges des posts Google My Business professionnels et engageants pour promouvoir les articles de blog. "
             . "Le ton est expert mais accessible, chaleureux, orienté conseil et confiance. "
             . "Zone géographique : Bordeaux Métropole (Mérignac, Pessac, Talence, Gradignan, Villenave-d'Ornon).";
    }

    /**
     * Appelle l'IA (Claude avec fallback OpenAI).
     */
    private function callAI(string $prompt, string $system): array
    {
        // Méthode 1 : AiClient si disponible
        $aiClientFile = dirname(__DIR__, 2) . '/core/ai/AiClient.php';
        if (file_exists($aiClientFile)) {
            try {
                require_once $aiClientFile;
                $client = AiClient::getInstance();
                return $client->withFallback($prompt, $system, 1200, 0.75);
            } catch (Throwable $e) {
                // Fallback vers appel direct
            }
        }

        // Méthode 2 : Appel direct Claude API
        $apiKey = '';
        if (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY)) {
            $apiKey = ANTHROPIC_API_KEY;
        } elseif (function_exists('get_api_key')) {
            $apiKey = get_api_key('claude');
        }

        if (empty($apiKey)) {
            // Essayer OpenAI
            return $this->callOpenAI($prompt, $system);
        }

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => 'claude-sonnet-4-20250514',
                'max_tokens' => 1200,
                'temperature'=> 0.75,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return $this->callOpenAI($prompt, $system);
        }

        $data = json_decode($response, true);
        $content = $data['content'][0]['text'] ?? '';

        return [
            'success'  => !empty($content),
            'content'  => $content,
            'provider' => 'claude',
            'usage'    => $data['usage'] ?? [],
            'error'    => empty($content) ? 'Réponse IA vide' : null,
        ];
    }

    /**
     * Fallback OpenAI.
     */
    private function callOpenAI(string $prompt, string $system): array
    {
        $apiKey = '';
        if (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY)) {
            $apiKey = OPENAI_API_KEY;
        } elseif (function_exists('get_api_key')) {
            $apiKey = get_api_key('openai');
        }

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Aucune clé API IA configurée', 'content' => '', 'provider' => ''];
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'       => 'gpt-4o',
                'max_tokens'  => 1200,
                'temperature' => 0.75,
                'messages'    => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]),
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return ['success' => false, 'error' => 'Erreur API OpenAI (HTTP ' . $httpCode . ')', 'content' => '', 'provider' => 'openai'];
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';

        return [
            'success'  => !empty($content),
            'content'  => $content,
            'provider' => 'openai',
            'usage'    => $data['usage'] ?? [],
            'error'    => empty($content) ? 'Réponse IA vide' : null,
        ];
    }

    // =========================================================================
    //  GOOGLE BUSINESS PROFILE PUBLISH
    // =========================================================================

    /**
     * Tente de publier un post sur Google Business Profile.
     * Ne publie que si la clé API 'my-business' est configurée.
     */
    public function tryPublish(int $postId): array
    {
        $post = $this->getById($postId);
        if (!$post) return ['published' => false, 'error' => 'Post introuvable'];

        // Vérifier la configuration GMB
        $gmbKey = $this->getGmbApiKey();
        if (empty($gmbKey)) {
            // Pas de clé => reste en brouillon
            return ['published' => false, 'error' => 'Clé API GMB non configurée'];
        }

        $gmbConfig = $this->getGmbConfig();
        if (empty($gmbConfig['account_id']) || empty($gmbConfig['location_id'])) {
            return ['published' => false, 'error' => 'account_id ou location_id GMB manquant'];
        }

        // Marquer comme pending
        $this->updateStatus($postId, 'pending');

        // Appel API Google Business Profile
        $result = $this->publishToGmb($post, $gmbKey, $gmbConfig);

        if ($result['success']) {
            $this->pdo->prepare(
                "UPDATE gmb_article_posts SET status = 'published', gmb_post_id = ?, published_at = NOW(), gmb_error = NULL WHERE id = ?"
            )->execute([$result['gmb_post_id'] ?? '', $postId]);
            return ['published' => true, 'gmb_post_id' => $result['gmb_post_id']];
        } else {
            $this->pdo->prepare(
                "UPDATE gmb_article_posts SET status = 'failed', gmb_error = ? WHERE id = ?"
            )->execute([$result['error'] ?? 'Erreur inconnue', $postId]);
            return ['published' => false, 'error' => $result['error']];
        }
    }

    /**
     * Publie un post via l'API Google Business Profile.
     */
    private function publishToGmb(array $post, string $accessToken, array $config): array
    {
        $accountId  = $config['account_id'];
        $locationId = $config['location_id'];

        $url = "https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}/localPosts";

        $payload = [
            'languageCode' => 'fr-FR',
            'summary'      => $post['post_text'],
            'topicType'    => 'STANDARD',
        ];

        // Ajouter le CTA si présent
        if (!empty($post['cta_url'])) {
            $payload['callToAction'] = [
                'actionType' => 'LEARN_MORE',
                'url'        => $post['cta_url'],
            ];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL: ' . $error];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return [
                'success'     => true,
                'gmb_post_id' => $data['name'] ?? $data['localPostId'] ?? '',
            ];
        }

        $data = json_decode($response, true);
        $errMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
        return ['success' => false, 'error' => $errMsg];
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    private function updateStatus(int $postId, string $status): void
    {
        $this->pdo->prepare(
            "UPDATE gmb_article_posts SET status = ? WHERE id = ?"
        )->execute([$status, $postId]);
    }

    private function getGmbApiKey(): string
    {
        // Essayer via le helper centralisé
        $helperFile = dirname(__DIR__) . '/functions/api-keys.php';
        if (file_exists($helperFile)) {
            require_once $helperFile;
            $key = get_api_key('my-business');
            if (!empty($key)) return $key;
            // Aussi essayer google_my_business
            $key = get_api_key('google_my_business');
            if (!empty($key)) return $key;
        }
        return '';
    }

    private function getGmbConfig(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT setting_key, setting_value FROM gmb_settings WHERE setting_key IN ('account_id','location_id')"
            );
            $config = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $config[$row['setting_key']] = $row['setting_value'];
            }
            return $config;
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getSiteUrl(): string
    {
        if (defined('SITE_URL') && !empty(SITE_URL)) return rtrim(SITE_URL, '/');
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Extrait un objet JSON d'une réponse IA (peut contenir du texte autour).
     */
    private function extractJson(string $text): ?array
    {
        // Essayer le texte brut d'abord
        $decoded = json_decode($text, true);
        if (is_array($decoded)) return $decoded;

        // Chercher un bloc JSON dans le texte
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) return $decoded;
        }

        // Chercher le premier { ... }
        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }
}
