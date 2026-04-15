<?php

declare(strict_types=1);

final class HelpCenterService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getCategories(): array
    {
        $articles = $this->loadArticles();
        $categories = [];

        foreach ($articles as $article) {
            $category = trim((string) ($article['category'] ?? 'general'));
            if ($category === '') {
                $category = 'general';
            }
            $categories[$category] = true;
        }

        $list = array_keys($categories);
        sort($list);
        return $list;
    }

    public function searchArticles(string $query = '', string $category = '', string $context = '', int $limit = 24): array
    {
        $rows = $this->loadArticles();
        $query = mb_strtolower(trim($query));
        $category = mb_strtolower(trim($category));
        $context = mb_strtolower(trim($context));

        $filtered = array_values(array_filter($rows, static function (array $article) use ($query, $category): bool {
            if ($category !== '' && mb_strtolower((string) ($article['category'] ?? '')) !== $category) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(
                (string) ($article['title'] ?? '') . ' ' .
                (string) ($article['excerpt'] ?? '') . ' ' .
                (string) ($article['content'] ?? '')
            );

            return mb_strpos($haystack, $query) !== false;
        }));

        usort($filtered, static function (array $a, array $b) use ($context): int {
            $aScore = 0;
            $bScore = 0;

            if ($context !== '') {
                $aScore += mb_strtolower((string) ($a['module_key'] ?? '')) === $context ? 3 : 0;
                $bScore += mb_strtolower((string) ($b['module_key'] ?? '')) === $context ? 3 : 0;

                $aScore += mb_strtolower((string) ($a['category'] ?? '')) === $context ? 2 : 0;
                $bScore += mb_strtolower((string) ($b['category'] ?? '')) === $context ? 2 : 0;
            }

            $aScore += (int) ($a['views_count'] ?? 0) > (int) ($b['views_count'] ?? 0) ? 1 : 0;

            if ($aScore === $bScore) {
                return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
            }

            return $bScore <=> $aScore;
        });

        return array_slice($filtered, 0, max(1, $limit));
    }

    public function getArticle(string $idOrSlug): ?array
    {
        $identifier = trim($idOrSlug);
        if ($identifier === '') {
            return null;
        }

        foreach ($this->loadArticles() as $article) {
            if ((string) ($article['slug'] ?? '') === $identifier || (string) ($article['id'] ?? '') === $identifier) {
                return $article;
            }
        }

        return null;
    }

    public function recordView(int $userId, array $article, string $context = ''): void
    {
        if (!$this->tableExists('help_views')) {
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO help_views (help_article_id, user_id, module_context, viewed_at) VALUES (:help_article_id, :user_id, :module_context, NOW())');
            $stmt->execute([
                'help_article_id' => (int) ($article['id'] ?? 0) ?: null,
                'user_id' => $userId > 0 ? $userId : null,
                'module_context' => mb_substr($context, 0, 50),
            ]);
        } catch (Throwable $e) {
            error_log('HelpCenterService::recordView ' . $e->getMessage());
        }
    }

    private function loadArticles(): array
    {
        if ($this->tableExists('help_articles')) {
            try {
                $stmt = $this->db->query('SELECT id, slug, title, category, module_key, excerpt, content, cta_label, cta_url, is_active FROM help_articles WHERE is_active = 1 ORDER BY id DESC');
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (is_array($rows) && $rows !== []) {
                    return $this->attachViewCounts($rows);
                }
            } catch (Throwable $e) {
                error_log('HelpCenterService::loadArticles ' . $e->getMessage());
            }
        }

        $seed = require __DIR__ . '/data/articles.php';
        return $this->attachViewCounts($seed);
    }

    private function attachViewCounts(array $articles): array
    {
        if (!$this->tableExists('help_views') || $articles === []) {
            return $articles;
        }

        $countsById = [];
        try {
            $rows = $this->db->query('SELECT help_article_id, COUNT(*) AS c FROM help_views WHERE help_article_id IS NOT NULL GROUP BY help_article_id')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $countsById[(int) $row['help_article_id']] = (int) $row['c'];
            }
        } catch (Throwable $e) {
            error_log('HelpCenterService::attachViewCounts ' . $e->getMessage());
        }

        foreach ($articles as &$article) {
            $articleId = (int) ($article['id'] ?? 0);
            $article['views_count'] = $countsById[$articleId] ?? 0;
        }

        return $articles;
    }

    private function tableExists(string $tableName): bool
    {
        static $cache = [];
        if (array_key_exists($tableName, $cache)) {
            return $cache[$tableName];
        }

        try {
            $stmt = $this->db->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name LIMIT 1');
            $stmt->execute(['table_name' => $tableName]);
            $cache[$tableName] = (bool) $stmt->fetchColumn();
            return $cache[$tableName];
        } catch (Throwable $e) {
            error_log('HelpCenterService::tableExists ' . $e->getMessage());
            $cache[$tableName] = false;
            return false;
        }
    }
}
