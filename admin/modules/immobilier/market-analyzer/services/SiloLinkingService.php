<?php

class SiloLinkingService {

    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function buildLinkPlan(int $planId): array {
        $items = $this->fetchGeneratedItems($planId);
        $links = [];

        for ($i = 0; $i < count($items); $i++) {
            $current = $items[$i];
            $next = $items[($i + 1) % count($items)] ?? null;
            if (!$next || $current['id'] === $next['id']) {
                continue;
            }

            $links[] = [
                'source_item_id' => (int)$current['id'],
                'target_item_id' => (int)$next['id'],
                'source_article_id' => (int)$current['article_id'],
                'target_article_id' => (int)$next['article_id'],
                'anchor' => 'En savoir plus sur ' . strtolower((string)$next['title']),
            ];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO market_cluster_link_plans (plan_id, link_json, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE link_json = VALUES(link_json), updated_at = NOW()'
        );
        $stmt->execute([$planId, json_encode($links, JSON_UNESCAPED_UNICODE)]);

        return [
            'plan_id' => $planId,
            'links' => $links,
            'generated_count' => count($links),
        ];
    }

    public function getLinkPlan(int $planId): array {
        $stmt = $this->pdo->prepare(
            'SELECT plan_id, link_json, updated_at FROM market_cluster_link_plans WHERE plan_id = ? LIMIT 1'
        );
        $stmt->execute([$planId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'plan_id' => $planId,
                'links' => [],
                'generated_count' => 0,
            ];
        }

        $links = json_decode((string)($row['link_json'] ?? '[]'), true) ?: [];
        return [
            'plan_id' => (int)$row['plan_id'],
            'links' => $links,
            'generated_count' => count($links),
            'updated_at' => $row['updated_at'],
        ];
    }

    private function fetchGeneratedItems(int $planId): array {
        $stmt = $this->pdo->prepare(
            'SELECT i.id, i.title, i.article_id
             FROM market_cluster_items i
             JOIN market_clusters c ON c.id = i.cluster_id
             JOIN market_cluster_plans p ON p.id = c.plan_id
             WHERE p.id = ? AND p.user_id = ? AND i.article_id IS NOT NULL
             ORDER BY c.score DESC, i.id ASC'
        );
        $stmt->execute([$planId, $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
