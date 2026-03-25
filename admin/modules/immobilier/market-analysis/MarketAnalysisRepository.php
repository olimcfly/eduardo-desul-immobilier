<?php

class MarketAnalysisRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function paginateByUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM market_analyses WHERE user_id = ?');
        $countStmt->execute([$userId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->pdo->prepare(
            'SELECT id, city, postal_code, area_name, target_type, property_type, status, created_at, updated_at
             FROM market_analyses
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function findByIdAndUser(int $analysisId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM market_analyses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$analysisId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $payload): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO market_analyses (
                user_id, city, postal_code, area_name, target_type, property_type,
                source_provider, source_prompt, raw_response, summary, market_trends,
                pricing_data, audience_profiles, faq_data, seo_opportunities,
                business_recommendations, status, manual_notes, created_at, updated_at
            ) VALUES (
                :user_id, :city, :postal_code, :area_name, :target_type, :property_type,
                :source_provider, :source_prompt, :raw_response, :summary, :market_trends,
                :pricing_data, :audience_profiles, :faq_data, :seo_opportunities,
                :business_recommendations, :status, :manual_notes, NOW(), NOW()
            )'
        );

        $stmt->execute([
            ':user_id' => $payload['user_id'],
            ':city' => $payload['city'],
            ':postal_code' => $payload['postal_code'],
            ':area_name' => $payload['area_name'],
            ':target_type' => $payload['target_type'],
            ':property_type' => $payload['property_type'],
            ':source_provider' => $payload['source_provider'],
            ':source_prompt' => $payload['source_prompt'],
            ':raw_response' => $payload['raw_response'],
            ':summary' => $payload['summary'],
            ':market_trends' => $payload['market_trends'],
            ':pricing_data' => $payload['pricing_data'],
            ':audience_profiles' => $payload['audience_profiles'],
            ':faq_data' => $payload['faq_data'],
            ':seo_opportunities' => $payload['seo_opportunities'],
            ':business_recommendations' => $payload['business_recommendations'],
            ':status' => $payload['status'],
            ':manual_notes' => $payload['manual_notes'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function deleteByIdAndUser(int $analysisId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM market_analyses WHERE id = ? AND user_id = ?');
        return $stmt->execute([$analysisId, $userId]);
    }
}
