<?php

declare(strict_types=1);

final class SitemapService
{
    private const STATIC_ROUTES = [
        '/', '/a-propos', '/contact', '/services', '/biens', '/biens/maisons', '/biens/appartements', '/biens/prestige', '/biens/vendus',
        '/estimation-gratuite', '/avis-de-valeur', '/prendre-rendez-vous', '/financement', '/blog', '/actualites', '/guide-local',
        '/ressources', '/avis-clients', '/mentions-legales', '/politique-confidentialite', '/politique-cookies', '/cgv', '/plan-du-site', '/secteurs',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function ensureSchema(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS seo_sitemaps (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                sitemap_url VARCHAR(255) NOT NULL,
                total_urls INT UNSIGNED NOT NULL DEFAULT 0,
                last_generated_at DATETIME NULL,
                status ENUM("idle","ok","warning","error") NOT NULL DEFAULT "idle",
                issues_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_sitemap_user (user_id),
                KEY idx_sitemap_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS seo_sitemap_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sitemap_id BIGINT UNSIGNED NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                action_type ENUM("generate","verify","submit") NOT NULL,
                status ENUM("ok","warning","error") NOT NULL DEFAULT "ok",
                message TEXT NULL,
                urls_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_sitemap_logs_sitemap (sitemap_id, created_at),
                KEY idx_sitemap_logs_user (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->addColumnIfMissing('seo_sitemap_logs', 'user_id', 'BIGINT UNSIGNED NOT NULL DEFAULT 0');
        $this->addColumnIfMissing('seo_sitemap_logs', 'sitemap_id', 'BIGINT UNSIGNED NULL');
        $this->addColumnIfMissing('seo_sitemap_logs', 'action_type', 'VARCHAR(20) NULL');
        $this->addColumnIfMissing('seo_sitemap_logs', 'status', 'VARCHAR(20) NULL');
        $this->addColumnIfMissing('seo_sitemap_logs', 'message', 'TEXT NULL');
        $this->addColumnIfMissing('seo_sitemap_logs', 'created_at', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addColumnIfMissing('seo_sitemap_logs', 'urls_count', 'INT UNSIGNED NOT NULL DEFAULT 0');
    }

    public function generate(int $userId): array
    {
        $collection = $this->collectPublishedUrls($userId);
        $xml = $this->toXml($collection['urls']);
        $path = dirname(__DIR__, 3) . '/public/sitemap.xml';
        file_put_contents($path, $xml);

        $sitemapUrl = rtrim((string) setting('site_url', APP_URL, $userId), '/') . '/sitemap.xml';
        $status = $collection['issues'] === [] ? 'ok' : 'warning';

        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_sitemaps (user_id, sitemap_url, total_urls, last_generated_at, status, issues_count, created_at, updated_at)
             VALUES (:user_id, :sitemap_url, :total_urls, NOW(), :status, :issues_count, NOW(), NOW())
             ON DUPLICATE KEY UPDATE sitemap_url = VALUES(sitemap_url), total_urls = VALUES(total_urls), last_generated_at = VALUES(last_generated_at), status = VALUES(status), issues_count = VALUES(issues_count), updated_at = NOW()'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':sitemap_url' => $sitemapUrl,
            ':total_urls' => count($collection['urls']),
            ':status' => $status,
            ':issues_count' => count($collection['issues']),
        ]);

        $sitemapId = (int) $this->pdo->query('SELECT id FROM seo_sitemaps WHERE user_id = ' . (int) $userId . ' LIMIT 1')->fetchColumn();
        $this->log($userId, $sitemapId, 'generate', $status, sprintf('%d URLs générées', count($collection['urls'])), count($collection['urls']));

        return [
            'xml' => $xml,
            'url' => $sitemapUrl,
            'stats' => $collection['stats'],
            'issues' => $collection['issues'],
            'total_urls' => count($collection['urls']),
            'status' => $status,
        ];
    }

    public function verify(int $userId): array
    {
        $collection = $this->collectPublishedUrls($userId);
        $status = $collection['issues'] === [] ? 'ok' : 'warning';

        $sitemapId = (int) $this->pdo->query('SELECT id FROM seo_sitemaps WHERE user_id = ' . (int) $userId . ' LIMIT 1')->fetchColumn();
        $this->log($userId, $sitemapId, 'verify', $status, sprintf('%d anomalie(s)', count($collection['issues'])), count($collection['urls']));

        return [
            'status' => $status,
            'issues' => $collection['issues'],
            'stats' => $collection['stats'],
            'total_urls' => count($collection['urls']),
        ];
    }

    public function submitPlaceholder(int $userId): array
    {
        $sitemapId = (int) $this->pdo->query('SELECT id FROM seo_sitemaps WHERE user_id = ' . (int) $userId . ' LIMIT 1')->fetchColumn();
        $message = 'Connexion Google Search Console non configurée pour le moment.';
        $this->log($userId, $sitemapId, 'submit', 'warning', $message, 0);

        return ['status' => 'warning', 'message' => $message];
    }

    public function getDashboard(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_sitemaps WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $sitemap = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $verification = $this->verify($userId);
        $logs = $this->getLogs($userId, 20);

        return [
            'sitemap' => $sitemap,
            'coverage' => $verification['stats'],
            'issues' => $verification['issues'],
            'logs' => $logs,
        ];
    }

    public function getLogs(int $userId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->pdo->prepare('SELECT action_type, status, message, urls_count, created_at FROM seo_sitemap_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function collectPublishedUrls(int $userId): array
    {
        $baseUrl = rtrim((string) setting('site_url', APP_URL, $userId), '/');
        $seen = [];
        $urls = [];
        $issues = [];
        $stats = ['pages' => 0, 'villes' => 0, 'blog' => 0, 'secteurs' => 0, 'autres' => 0];

        $add = static function (string $absoluteUrl, string $lastmod) use (&$seen, &$urls, &$issues): void {
            if ($absoluteUrl === '' || !filter_var($absoluteUrl, FILTER_VALIDATE_URL)) {
                $issues[] = 'URL cassée ou vide détectée et ignorée.';
                return;
            }
            if (isset($seen[$absoluteUrl])) {
                $issues[] = 'URL en doublon détectée : ' . $absoluteUrl;
                return;
            }
            $seen[$absoluteUrl] = true;
            $urls[] = ['loc' => $absoluteUrl, 'lastmod' => $lastmod];
        };

        $today = date('Y-m-d');
        foreach (self::STATIC_ROUTES as $route) {
            $add($baseUrl . ($route === '/' ? '' : $route), $today);
            $stats['pages']++;
        }

        if ($this->tableExists('pages')) {
            $stmt = $this->pdo->query("SELECT slug, DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod FROM pages WHERE status = 'published'");
            foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '' || $slug === 'home') {
                    continue;
                }
                $add($baseUrl . '/' . ltrim($slug, '/'), (string) ($row['lastmod'] ?? $today));
                $stats['pages']++;
            }
        }

        if ($this->tableExists('seo_city_pages')) {
            $stmt = $this->pdo->prepare("SELECT slug, status, DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod FROM seo_city_pages WHERE user_id = ?");
            $stmt->execute([$userId]);
            foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                if (($row['status'] ?? '') !== 'published') {
                    continue;
                }
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    $issues[] = 'Fiche ville publiée sans slug valide.';
                    continue;
                }
                $add($baseUrl . '/secteurs/villes/' . rawurlencode($slug), (string) ($row['lastmod'] ?? $today));
                $stats['villes']++;
            }
        }

        if ($this->tableExists('blog_articles')) {
            $stmt = $this->pdo->query("SELECT slug, statut, index_status, DATE_FORMAT(COALESCE(updated_at, date_publication, created_at), '%Y-%m-%d') AS lastmod FROM blog_articles WHERE statut = 'publié'");
            foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                if (($row['index_status'] ?? 'index') === 'noindex') {
                    continue;
                }
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    $issues[] = 'Article publié sans slug valide.';
                    continue;
                }
                $add($baseUrl . '/blog/' . rawurlencode($slug), (string) ($row['lastmod'] ?? $today));
                $stats['blog']++;
            }
        }

        if ($this->tableExists('secteurs')) {
            $stmt = $this->pdo->query("SELECT slug, COALESCE(type, 'villes') AS type, DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod FROM secteurs");
            foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $type = in_array($row['type'], ['villes', 'quartiers', 'regions'], true) ? (string) $row['type'] : 'villes';
                $add($baseUrl . '/secteurs/' . $type . '/' . rawurlencode($slug), (string) ($row['lastmod'] ?? $today));
                $stats['secteurs']++;
            }
        }

        return ['urls' => $urls, 'issues' => array_values(array_unique($issues)), 'stats' => $stats];
    }

    private function toXml(array $urls): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $entry) {
            $node = $xml->addChild('url');
            $node->addChild('loc', htmlspecialchars((string) $entry['loc'], ENT_XML1));
            $node->addChild('lastmod', (string) $entry['lastmod']);
        }

        return (string) $xml->asXML();
    }

    private function log(int $userId, int $sitemapId, string $action, string $status, string $message, int $urlsCount): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO seo_sitemap_logs (sitemap_id, user_id, action_type, status, message, urls_count, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$sitemapId > 0 ? $sitemapId : null, $userId, $action, $status, $message, $urlsCount]);
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if (!$this->tableExists($table)) {
            return;
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1');
        $stmt->execute([':table' => $table, ':column' => $column]);
        if ($stmt->fetchColumn()) {
            return;
        }

        $this->pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
    }
}
