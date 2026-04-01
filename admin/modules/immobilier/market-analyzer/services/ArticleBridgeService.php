<?php

class ArticleBridgeService {

    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function generateDraftForItem(int $itemId): array {
        $item = $this->getItem($itemId);
        if (!$item) {
            throw new RuntimeException('Cluster item introuvable.');
        }

        if (!empty($item['article_id'])) {
            return [
                'success' => true,
                'item_id' => $itemId,
                'article_id' => (int)$item['article_id'],
                'skipped' => true,
            ];
        }

        $outline = json_decode((string)($item['outline_json'] ?? '{}'), true) ?: [];
        $payload = [
            'module' => 'articles',
            'action' => 'generate',
            'csrf_token' => $_SESSION['auth_csrf_token'] ?? null,
            'subject' => $item['title'],
            'keywords' => $item['keyword'] ?? '',
            'word_count' => 1000,
            'type' => 'guide complet étape par étape',
            'objectif' => 'Générer un brouillon SEO local exploitable par une agence immobilière.',
            'persona' => 'Propriétaire vendeur ou acquéreur local',
        ];

        $generated = $this->callAiArticlesGenerate($payload);
        if (empty($generated['article']['title']) || empty($generated['article']['content'])) {
            throw new RuntimeException('Réponse IA partielle ou invalide.');
        }

        $articleId = $this->insertArticleDraft($generated['article']);

        $update = $this->pdo->prepare(
            'UPDATE market_cluster_items SET article_id = ?, status = ?, error_message = NULL, updated_at = NOW() WHERE id = ?'
        );
        $update->execute([$articleId, 'generated', $itemId]);

        return [
            'success' => true,
            'item_id' => $itemId,
            'article_id' => $articleId,
            'skipped' => false,
        ];
    }

    private function getItem(int $itemId): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.keyword
             FROM market_cluster_items i
             JOIN market_clusters c ON c.id = i.cluster_id
             JOIN market_cluster_plans p ON p.id = c.plan_id
             WHERE i.id = ? AND p.user_id = ? LIMIT 1'
        );
        $stmt->execute([$itemId, $this->userId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    private function callAiArticlesGenerate(array $payload): array {
        $baseUrl = $this->detectBaseUrl();
        $url = rtrim($baseUrl, '/') . '/admin/api/ai/generate.php';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException('Erreur réseau IA: ' . $error);
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode >= 400 || !is_array($decoded) || empty($decoded['success'])) {
            $message = $decoded['error'] ?? ('Erreur IA HTTP ' . $httpCode);
            throw new RuntimeException($message);
        }

        return $decoded;
    }

    private function insertArticleDraft(array $article): int {
        $title = trim((string)($article['title'] ?? ''));
        $slug = trim((string)($article['slug'] ?? ''));
        $content = (string)($article['content'] ?? '');
        $excerpt = (string)($article['excerpt'] ?? '');

        if ($title === '' || $slug === '') {
            throw new RuntimeException('Article IA incomplet (title/slug).');
        }

        $slug = $this->ensureUniqueSlug($slug);

        $meta = [
            'meta_title' => (string)($article['meta_title'] ?? $title),
            'meta_description' => (string)($article['meta_description'] ?? mb_substr(strip_tags($excerpt), 0, 160)),
            'primary_keyword' => (string)($article['primary_keyword'] ?? ''),
            'secondary_keywords' => $article['secondary_keywords'] ?? [],
            'source' => 'market-analyzer-cluster',
        ];

        $stmt = $this->pdo->prepare(
            'INSERT INTO articles (title, slug, content, excerpt, metadata, status, featured, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW())'
        );
        $stmt->execute([$title, $slug, $content, $excerpt, json_encode($meta, JSON_UNESCAPED_UNICODE), 'draft']);

        return (int)$this->pdo->lastInsertId();
    }

    private function ensureUniqueSlug(string $baseSlug): string {
        $slug = trim($baseSlug);
        $suffix = 1;

        $stmt = $this->pdo->prepare('SELECT id FROM articles WHERE slug = ? LIMIT 1');
        while (true) {
            $stmt->execute([$slug]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                return $slug;
            }
            $suffix++;
            $slug = rtrim($baseSlug, '-') . '-' . $suffix;
        }
    }

    private function detectBaseUrl(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
