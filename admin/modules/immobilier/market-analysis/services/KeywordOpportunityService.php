<?php

/**
 * Phase 2 Skeleton: logique de scoring hybride SEO + Business + Local.
 */
class KeywordOpportunityService
{
    public function scoreKeyword(array $keywordData): array
    {
        $volume = max(0, (int) ($keywordData['estimated_volume'] ?? 0));
        $competition = max(0, min(100, (int) ($keywordData['competition_level'] ?? 0)));
        $intent = (string) ($keywordData['intent_type'] ?? 'informationnel');
        $isLocal = !empty($keywordData['is_local']);

        $seoScore = min(100, (int) round(($volume / 50) + (100 - $competition) * 0.6 + ($isLocal ? 10 : 0)));

        $intentBoost = match ($intent) {
            'vendeur' => 35,
            'transactionnel' => 30,
            'acheteur' => 20,
            'local' => 18,
            default => 10,
        };
        $businessScore = min(100, 40 + $intentBoost + ($isLocal ? 10 : 0));

        $priorityScore = (int) round(($seoScore * 0.45) + ($businessScore * 0.55));

        return [
            'seo_score' => $seoScore,
            'business_score' => $businessScore,
            'priority_score' => min(100, $priorityScore),
        ];
    }
}
