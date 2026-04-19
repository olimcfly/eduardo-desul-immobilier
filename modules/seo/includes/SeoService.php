<?php
/** @deprecated SEO legacy freeze: no new feature here. Use modules/seo/services/SeoService.php */
declare(strict_types=1);

require_once __DIR__ . '/../_legacy_guard.php';
seoLegacyGuard('modules/seo/includes/SeoService.php', 'modules/seo/services/SeoService.php');

class SeoService
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function getHubStats(): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total, SUM(top10) AS top10 FROM seo_keywords WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $this->userId]);
        $keywords = $stmt->fetch() ?: [];

        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total, SUM(published) AS published FROM seo_fiches_villes WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $this->userId]);
        $fiches = $stmt->fetch() ?: [];

        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM seo_sitemap_urls WHERE user_id = :user_id AND included = 1');
        $stmt->execute(['user_id' => $this->userId]);
        $sitemap = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT ROUND(AVG(score_seo), 0) FROM seo_audits WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $this->userId]);
        $avg = $stmt->fetchColumn();

        return [
            'keywords_total' => (int)($keywords['total'] ?? 0),
            'keywords_top10' => (int)($keywords['top10'] ?? 0),
            'fiches_total' => (int)($fiches['total'] ?? 0),
            'fiches_published' => (int)($fiches['published'] ?? 0),
            'sitemap_included' => $sitemap,
            'audit_avg_seo' => $avg !== null ? (int)$avg : null,
        ];
    }

    public function getAdvisorIdentity(): array
    {
        $fullName = trim((string)setting('advisor_firstname', 'Eduardo', $this->userId) . ' ' . (string)setting('advisor_lastname', 'Desul', $this->userId));

        return [
            'name' => $fullName !== '' ? $fullName : 'Eduardo Desul',
            'zone' => (string)setting('zone_city', 'Bordeaux', $this->userId),
        ];
    }
}