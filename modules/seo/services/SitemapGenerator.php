<?php

declare(strict_types=1);

class SitemapGenerator
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function generate(int $userId): string
    {
        $urls = $this->getUrls($userId);
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($entry['url'], ENT_XML1));
            $url->addChild('lastmod', $entry['lastmod']);
            $url->addChild('changefreq', $entry['changefreq']);
            $url->addChild('priority', number_format((float)$entry['priority'], 1));
        }

        return $xml->asXML() ?: '';
    }

    public function save(string $xml): bool
    {
        $target = dirname(__DIR__, 3) . '/public/sitemap.xml';
        return (bool)file_put_contents($target, $xml);
    }

    public function ping(): bool
    {
        $siteUrl = rtrim((string)setting('site_url', '', $this->userId), '/');
        if ($siteUrl === '') {
            return false;
        }

        $pingUrl = 'https://www.google.com/ping?sitemap=' . rawurlencode($siteUrl . '/sitemap.xml');
        $ch = curl_init($pingUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 400;
    }

    public function getUrls(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT url, priority, changefreq, DATE_FORMAT(lastmod, "%Y-%m-%d") AS lastmod FROM seo_sitemap_urls WHERE user_id = ? AND included = 1 ORDER BY url ASC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function autoDiscoverUrls(int $userId): array
    {
        $siteUrl = rtrim((string)setting('site_url', '', $userId), '/');
        if ($siteUrl === '') {
            return [];
        }

        $urls = [
            ['url' => $siteUrl . '/', 'priority' => 1.0, 'changefreq' => 'daily', 'lastmod' => date('Y-m-d'), 'source_type' => 'fixed'],
            ['url' => $siteUrl . '/biens', 'priority' => 0.9, 'changefreq' => 'daily', 'lastmod' => date('Y-m-d'), 'source_type' => 'fixed'],
            ['url' => $siteUrl . '/estimation', 'priority' => 0.8, 'changefreq' => 'weekly', 'lastmod' => date('Y-m-d'), 'source_type' => 'fixed'],
            ['url' => $siteUrl . '/contact', 'priority' => 0.7, 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d'), 'source_type' => 'fixed'],
        ];

        $cityStmt = $this->pdo->prepare("SELECT slug, DATE_FORMAT(updated_at, '%Y-%m-%d') AS lastmod FROM seo_city_pages WHERE user_id = ? AND status = 'published'");
        $cityStmt->execute([$userId]);
        foreach ($cityStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $city) {
            $urls[] = [
                'url' => $siteUrl . '/' . trim((string)$city['slug'], '/') . '/',
                'priority' => 0.7,
                'changefreq' => 'weekly',
                'lastmod' => $city['lastmod'] ?: date('Y-m-d'),
                'source_type' => 'city',
            ];
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO seo_sitemap_urls (user_id, url, priority, changefreq, lastmod, source_type, included)
             VALUES (?, ?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE priority = VALUES(priority), changefreq = VALUES(changefreq), lastmod = VALUES(lastmod), source_type = VALUES(source_type), included = 1'
        );

        foreach ($urls as $entry) {
            $insert->execute([$userId, $entry['url'], $entry['priority'], $entry['changefreq'], $entry['lastmod'], $entry['source_type']]);
        }

        return $urls;
    }
}
