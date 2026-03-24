<?php

class ClusterPlannerService {

    private PDO $pdo;
    private int $userId;
    private KeywordOpportunityScoringService $scoringService;

    public function __construct(PDO $pdo, int $userId, KeywordOpportunityScoringService $scoringService) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->scoringService = $scoringService;
    }

    public function planForCity(string $city, array $options = []): array {
        $city = trim($city);
        if ($city === '') {
            throw new InvalidArgumentException('Ville requise pour générer un plan.');
        }

        $clusterCount = max(1, min(12, (int)($options['cluster_count'] ?? 3)));
        $itemsPerCluster = max(1, min(10, (int)($options['items_per_cluster'] ?? 2)));
        $inputHash = sha1(strtolower($city) . '|' . $clusterCount . '|' . $itemsPerCluster);

        $existing = $this->findExistingPlan($inputHash);
        if ($existing) {
            return $this->getPlan((int)$existing['id']);
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO market_cluster_plans (user_id, city, status, input_hash, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([$this->userId, $city, 'planned', $inputHash]);
            $planId = (int)$this->pdo->lastInsertId();

            $templates = $this->buildClusterTemplates($city, $clusterCount, $itemsPerCluster);

            foreach ($templates as $template) {
                $scoreData = $this->scoringService->scoreOpportunity($template['scoring']);

                $insertCluster = $this->pdo->prepare(
                    'INSERT INTO market_clusters (plan_id, keyword, intent, score, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
                );
                $insertCluster->execute([
                    $planId,
                    $template['keyword'],
                    $template['intent'],
                    $scoreData['score'],
                    'planned',
                ]);

                $clusterId = (int)$this->pdo->lastInsertId();
                $insertItem = $this->pdo->prepare(
                    'INSERT INTO market_cluster_items (cluster_id, title, outline_json, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())'
                );

                foreach ($template['items'] as $item) {
                    $insertItem->execute([
                        $clusterId,
                        $item['title'],
                        json_encode($item['outline'], JSON_UNESCAPED_UNICODE),
                        'planned',
                    ]);
                }
            }

            $this->pdo->commit();
            return $this->getPlan($planId);
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getPlan(int $planId): array {
        $stmt = $this->pdo->prepare('SELECT * FROM market_cluster_plans WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$planId, $this->userId]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            throw new RuntimeException('Plan introuvable.');
        }

        $clustersStmt = $this->pdo->prepare('SELECT * FROM market_clusters WHERE plan_id = ? ORDER BY score DESC, id ASC');
        $clustersStmt->execute([$planId]);
        $clusters = $clustersStmt->fetchAll(PDO::FETCH_ASSOC);

        $itemsStmt = $this->pdo->prepare('SELECT * FROM market_cluster_items WHERE cluster_id = ? ORDER BY id ASC');

        foreach ($clusters as &$cluster) {
            $itemsStmt->execute([(int)$cluster['id']]);
            $clusterItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($clusterItems as &$item) {
                $item['outline'] = json_decode((string)($item['outline_json'] ?? '{}'), true) ?: [];
            }
            $cluster['items'] = $clusterItems;
        }

        return [
            'plan' => $plan,
            'clusters' => $clusters,
        ];
    }

    private function findExistingPlan(string $inputHash): ?array {
        $stmt = $this->pdo->prepare('SELECT id FROM market_cluster_plans WHERE user_id = ? AND input_hash = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$this->userId, $inputHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function buildClusterTemplates(string $city, int $clusterCount, int $itemsPerCluster): array {
        $base = [
            ['keyword' => "acheter appartement {$city}", 'intent' => 'transactionnelle'],
            ['keyword' => "vendre maison {$city}", 'intent' => 'transactionnelle'],
            ['keyword' => "investir immobilier {$city}", 'intent' => 'informationnelle'],
            ['keyword' => "estimation bien {$city}", 'intent' => 'commerciale'],
            ['keyword' => "quartiers où habiter {$city}", 'intent' => 'informationnelle'],
            ['keyword' => "frais notaire {$city}", 'intent' => 'informationnelle'],
        ];

        $result = [];
        for ($i = 0; $i < $clusterCount; $i++) {
            $seed = $base[$i % count($base)];
            $items = [];
            for ($j = 1; $j <= $itemsPerCluster; $j++) {
                $items[] = [
                    'title' => ucfirst($seed['keyword']) . " : guide {$j}",
                    'outline' => [
                        'h2' => [
                            "Prix et tendances à {$city}",
                            'Étapes pratiques',
                            'Pièges à éviter',
                        ],
                    ],
                ];
            }

            $result[] = [
                'keyword' => $seed['keyword'],
                'intent' => $seed['intent'],
                'items' => $items,
                'scoring' => [
                    'volume_score' => 45 + ($i * 7),
                    'competition_score' => 40 + ($i * 5),
                    'intent_score' => 65 + ($i * 3),
                    'trend_score' => 55 + ($i * 4),
                ],
            ];
        }

        return $result;
    }
}
