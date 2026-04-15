<?php

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStatsData();
        $this->render('admin/views/dashboard/index', ['stats' => $stats]);
    }

    public function getStats()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getStatsData(), JSON_UNESCAPED_UNICODE);
    }

    private function getStatsData(): array
    {
        return [
            'biens' => $this->countBiens(),
            'leads' => $this->countLeadsThisMonth(),
            'blog_articles_publies' => $this->countPublishedArticles(),
            'social_posts_planifies' => $this->countScheduledSocialPosts(),
            'seo_score_global' => $this->getGlobalSeoScore(),
            'derniers_leads' => $this->getLatestLeads(),
        ];
    }

    private function countBiens(): int
    {
        try {
            if (class_exists('BienService')) {
                return (int) BienService::countActiveProperties();
            }

            $stmt = db()->query('SELECT COUNT(*) FROM biens');
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function countLeadsThisMonth(): int
    {
        try {
            $stmt = db()->query("\n                SELECT COUNT(*)\n                FROM leads\n                WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')\n            ");
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function countPublishedArticles(): int
    {
        try {
            $stmt = db()->query("\n                SELECT COUNT(*)\n                FROM articles\n                WHERE statut = 'publie'\n            ");
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function countScheduledSocialPosts(): int
    {
        try {
            $stmt = db()->query("\n                SELECT COUNT(*)\n                FROM social_posts\n                WHERE statut = 'planifie'\n                  AND (planifie_at IS NULL OR planifie_at >= NOW())\n            ");
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function getGlobalSeoScore(): ?int
    {
        try {
            $stmt = db()->query('SELECT ROUND(AVG(seo_score)) FROM seo_performance_audits');
            $value = $stmt->fetchColumn();
            return $value === null ? null : (int) $value;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function getLatestLeads(int $limit = 5): array
    {
        try {
            $stmt = db()->prepare("\n                SELECT id, nom, email, telephone, source, created_at\n                FROM leads\n                ORDER BY created_at DESC\n                LIMIT :leadLimit\n            ");
            $stmt->bindValue(':leadLimit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static function (array $lead): array {
                return [
                    'id' => (int) ($lead['id'] ?? 0),
                    'nom' => (string) ($lead['nom'] ?? ''),
                    'email' => (string) ($lead['email'] ?? ''),
                    'telephone' => (string) ($lead['telephone'] ?? ''),
                    'source' => (string) ($lead['source'] ?? ''),
                    'created_at' => (string) ($lead['created_at'] ?? ''),
                ];
            }, $stmt->fetchAll() ?: []);
        } catch (Throwable $e) {
            return [];
        }
    }
}
