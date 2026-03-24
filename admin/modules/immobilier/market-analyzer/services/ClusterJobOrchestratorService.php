<?php

class ClusterJobOrchestratorService {

    private PDO $pdo;
    private int $userId;
    private ArticleBridgeService $articleBridgeService;

    public function __construct(PDO $pdo, int $userId, ArticleBridgeService $articleBridgeService) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->articleBridgeService = $articleBridgeService;
    }

    public function enqueuePlanGeneration(int $planId): array {
        $itemStmt = $this->pdo->prepare(
            'SELECT i.id
             FROM market_cluster_items i
             JOIN market_clusters c ON c.id = i.cluster_id
             JOIN market_cluster_plans p ON p.id = c.plan_id
             WHERE p.id = ? AND p.user_id = ? AND (i.article_id IS NULL OR i.status IN ("planned", "failed"))'
        );
        $itemStmt->execute([$planId, $this->userId]);
        $itemIds = array_map('intval', array_column($itemStmt->fetchAll(PDO::FETCH_ASSOC), 'id'));

        $enqueued = 0;
        foreach ($itemIds as $itemId) {
            $enqueued += $this->enqueueItemGeneration($itemId, $planId)['enqueued'] ? 1 : 0;
        }

        return ['plan_id' => $planId, 'items' => count($itemIds), 'enqueued' => $enqueued];
    }

    public function enqueueItemGeneration(int $itemId, ?int $planId = null): array {
        if ($planId === null) {
            $planId = $this->resolvePlanIdByItem($itemId);
        }

        $payload = ['item_id' => $itemId, 'user_id' => $this->userId, 'plan_id' => $planId];
        $dedupeKey = 'generate-item:' . $itemId;

        $insert = $this->pdo->prepare(
            'INSERT INTO market_cluster_jobs (type, payload_json, dedupe_key, status, attempts, max_attempts, next_run_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, 0, 3, NOW(), NOW(), NOW())'
        );

        try {
            $insert->execute(['generate_item', json_encode($payload, JSON_UNESCAPED_UNICODE), $dedupeKey, 'pending']);
            return ['enqueued' => true];
        } catch (Throwable $e) {
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return ['enqueued' => false, 'reason' => 'duplicate'];
            }
            throw $e;
        }
    }

    public function retryFailed(?int $planId = null, ?int $itemId = null): array {
        $targets = [];

        if ($itemId) {
            $targets[] = $itemId;
        } elseif ($planId) {
            $stmt = $this->pdo->prepare(
                'SELECT i.id
                 FROM market_cluster_items i
                 JOIN market_clusters c ON c.id = i.cluster_id
                 JOIN market_cluster_plans p ON p.id = c.plan_id
                 WHERE p.id = ? AND p.user_id = ? AND i.status = "failed"'
            );
            $stmt->execute([$planId, $this->userId]);
            $targets = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id'));
        }

        $count = 0;
        foreach ($targets as $targetItemId) {
            $result = $this->enqueueItemGeneration($targetItemId, $planId);
            if (!empty($result['enqueued'])) {
                $count++;
            }
        }

        return ['retried' => $count, 'target_count' => count($targets)];
    }

    public function processQueue(int $limit = 5): array {
        $limit = max(1, min(50, $limit));
        $processed = 0;
        $failed = 0;

        for ($i = 0; $i < $limit; $i++) {
            $job = $this->claimNextJob();
            if (!$job) {
                break;
            }

            try {
                $this->runJob($job);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $this->handleJobFailure($job, $e);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    public function getStatus(?int $planId = null): array {
        $jobsWhere = '';
        $params = [];

        if ($planId) {
            $jobsWhere = 'WHERE payload_json LIKE ?';
            $params[] = '%"plan_id":' . $planId . '%';
        }

        $stmt = $this->pdo->prepare(
            "SELECT status, COUNT(*) as total FROM market_cluster_jobs {$jobsWhere} GROUP BY status"
        );
        $stmt->execute($params);
        $jobStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $itemStats = [];
        if ($planId) {
            $itemStmt = $this->pdo->prepare(
                'SELECT i.status, COUNT(*) as total
                 FROM market_cluster_items i
                 JOIN market_clusters c ON c.id = i.cluster_id
                 WHERE c.plan_id = ?
                 GROUP BY i.status'
            );
            $itemStmt->execute([$planId]);
            $itemStats = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'jobs' => $jobStats,
            'items' => $itemStats,
        ];
    }


    private function resolvePlanIdByItem(int $itemId): ?int {
        $stmt = $this->pdo->prepare(
            "SELECT c.plan_id
             FROM market_cluster_items i
             JOIN market_clusters c ON c.id = i.cluster_id
             JOIN market_cluster_plans p ON p.id = c.plan_id
             WHERE i.id = ? AND p.user_id = ?
             LIMIT 1"
        );
        $stmt->execute([$itemId, $this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['plan_id'] : null;
    }

    private function claimNextJob(): ?array {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM market_cluster_jobs
                 WHERE status = "pending" AND next_run_at <= NOW()
                 ORDER BY id ASC
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute();
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$job) {
                $this->pdo->commit();
                return null;
            }

            $update = $this->pdo->prepare('UPDATE market_cluster_jobs SET status = "running", locked_at = NOW(), updated_at = NOW() WHERE id = ?');
            $update->execute([(int)$job['id']]);
            $this->pdo->commit();

            return $job;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function runJob(array $job): void {
        $payload = json_decode((string)($job['payload_json'] ?? '{}'), true) ?: [];

        if (($job['type'] ?? '') === 'generate_item') {
            $itemId = (int)($payload['item_id'] ?? 0);
            if (!$itemId) {
                throw new RuntimeException('Job sans item_id.');
            }

            $this->markItemGenerating($itemId);
            $this->articleBridgeService->generateDraftForItem($itemId);
            $this->markJobDone((int)$job['id']);
            return;
        }

        throw new RuntimeException('Type de job non supporté: ' . ($job['type'] ?? 'unknown'));
    }

    private function handleJobFailure(array $job, Throwable $e): void {
        $attempts = ((int)$job['attempts']) + 1;
        $maxAttempts = (int)$job['max_attempts'];
        $isFinal = $attempts >= $maxAttempts;

        $payload = json_decode((string)($job['payload_json'] ?? '{}'), true) ?: [];
        $itemId = (int)($payload['item_id'] ?? 0);
        if ($itemId > 0) {
            $itemUpdate = $this->pdo->prepare('UPDATE market_cluster_items SET status = "failed", error_message = ?, updated_at = NOW() WHERE id = ?');
            $itemUpdate->execute([$e->getMessage(), $itemId]);
        }

        $status = $isFinal ? 'failed' : 'pending';
        $nextRunExpr = $isFinal ? 'NULL' : 'DATE_ADD(NOW(), INTERVAL 2 MINUTE)';

        $sql = "UPDATE market_cluster_jobs
                SET status = ?, attempts = ?, last_error = ?, next_run_at = {$nextRunExpr}, updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $attempts, $e->getMessage(), (int)$job['id']]);
    }

    private function markItemGenerating(int $itemId): void {
        $stmt = $this->pdo->prepare('UPDATE market_cluster_items SET status = "generating", updated_at = NOW() WHERE id = ?');
        $stmt->execute([$itemId]);
    }

    private function markJobDone(int $jobId): void {
        $stmt = $this->pdo->prepare('UPDATE market_cluster_jobs SET status = "done", updated_at = NOW() WHERE id = ?');
        $stmt->execute([$jobId]);
    }
}
