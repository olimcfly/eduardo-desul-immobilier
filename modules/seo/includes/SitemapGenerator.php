<?php
declare(strict_types=1);

class SitemapGenerator
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function listUrls(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_sitemap_urls WHERE user_id = :user_id ORDER BY included DESC, id DESC');
        $stmt->execute(['user_id' => $this->userId]);

        return $stmt->fetchAll() ?: [];
    }

    public function generateXml(string $baseUrl): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->listUrls() as $row) {
            if ((int)$row['included'] !== 1) {
                continue;
            }

            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($this->normalizeUrl($baseUrl, (string)$row['url']), ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            if (!empty($row['lastmod'])) {
                $url->addChild('lastmod', (string)$row['lastmod']);
            }
            $url->addChild('changefreq', (string)$row['changefreq']);
            $url->addChild('priority', number_format((float)$row['priority'], 1));
        }

        return $xml->asXML() ?: '';
    }

    private function normalizeUrl(string $baseUrl, string $pathOrUrl): string
    {
        if (preg_match('#^https?://#i', $pathOrUrl)) {
            return $pathOrUrl;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($pathOrUrl, '/');
    }
}
