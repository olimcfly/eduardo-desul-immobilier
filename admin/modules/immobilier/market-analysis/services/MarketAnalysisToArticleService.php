<?php

/**
 * Phase 2 Skeleton: bridge vers module Articles.
 */
class MarketAnalysisToArticleService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function exportClusterDrafts(int $analysisId): array
    {
        return [
            'success' => false,
            'analysis_id' => $analysisId,
            'created_articles' => 0,
            'error' => 'Phase 6 non implémentée: mapping vers module Articles en attente.',
        ];
    }
}
