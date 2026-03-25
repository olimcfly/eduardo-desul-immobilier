<?php

require_once __DIR__ . '/../MarketAnalysisRepository.php';
require_once __DIR__ . '/PerplexityMarketResearchService.php';
require_once __DIR__ . '/MarketInsightParserService.php';
require_once __DIR__ . '/KeywordOpportunityService.php';
require_once __DIR__ . '/ContentClusterGeneratorService.php';
require_once __DIR__ . '/MarketAnalysisToArticleService.php';

class MarketAnalysisService
{
    private MarketAnalysisRepository $repository;
    private PerplexityMarketResearchService $researchService;
    private MarketInsightParserService $parserService;
    private KeywordOpportunityService $keywordService;
    private ContentClusterGeneratorService $clusterService;
    private MarketAnalysisToArticleService $articleService;

    public function __construct(PDO $pdo)
    {
        $this->repository = new MarketAnalysisRepository($pdo);
        $this->researchService = new PerplexityMarketResearchService();
        $this->parserService = new MarketInsightParserService();
        $this->keywordService = new KeywordOpportunityService();
        $this->clusterService = new ContentClusterGeneratorService();
        $this->articleService = new MarketAnalysisToArticleService($pdo);
    }

    public function listForUser(int $userId, int $page = 1): array
    {
        return $this->repository->paginateByUser($userId, $page, 20);
    }

    public function createDraft(int $userId, array $input): array
    {
        $city = trim((string) ($input['city'] ?? ''));
        $postalCode = trim((string) ($input['postal_code'] ?? ''));
        $areaName = trim((string) ($input['area_name'] ?? ''));
        $targetType = trim((string) ($input['target_type'] ?? 'mixte'));
        $propertyType = trim((string) ($input['property_type'] ?? ''));
        $manualNotes = trim((string) ($input['manual_notes'] ?? ''));

        $errors = [];
        if ($city === '' || mb_strlen($city) < 2) {
            $errors[] = 'La ville est obligatoire (2 caractères minimum).';
        }
        if ($postalCode !== '' && !preg_match('/^[0-9A-Za-z\-\s]{3,12}$/', $postalCode)) {
            $errors[] = 'Le code postal semble invalide.';
        }
        $allowedTargets = ['vendeur', 'acheteur', 'mixte'];
        if (!in_array($targetType, $allowedTargets, true)) {
            $errors[] = 'La cible sélectionnée est invalide.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $analysisId = $this->repository->create([
            'user_id' => $userId,
            'city' => $city,
            'postal_code' => $postalCode !== '' ? $postalCode : null,
            'area_name' => $areaName !== '' ? $areaName : null,
            'target_type' => $targetType,
            'property_type' => $propertyType !== '' ? $propertyType : null,
            'source_provider' => 'manual_draft',
            'source_prompt' => null,
            'raw_response' => null,
            'summary' => null,
            'market_trends' => json_encode([], JSON_UNESCAPED_UNICODE),
            'pricing_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'audience_profiles' => json_encode([], JSON_UNESCAPED_UNICODE),
            'faq_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'seo_opportunities' => json_encode([], JSON_UNESCAPED_UNICODE),
            'business_recommendations' => json_encode([], JSON_UNESCAPED_UNICODE),
            'status' => 'draft',
            'manual_notes' => $manualNotes !== '' ? $manualNotes : null,
        ]);

        return ['success' => true, 'analysis_id' => $analysisId];
    }

    public function getForUser(int $analysisId, int $userId): ?array
    {
        $analysis = $this->repository->findByIdAndUser($analysisId, $userId);
        if (!$analysis) {
            return null;
        }

        foreach (['market_trends', 'pricing_data', 'audience_profiles', 'faq_data', 'seo_opportunities', 'business_recommendations'] as $jsonField) {
            $analysis[$jsonField . '_decoded'] = $this->decodeJsonField($analysis[$jsonField] ?? null);
        }

        return $analysis;
    }

    public function deleteForUser(int $analysisId, int $userId): bool
    {
        return $this->repository->deleteByIdAndUser($analysisId, $userId);
    }

    public function runAnalysis(int $analysisId, int $userId): array
    {
        $analysis = $this->repository->findByIdAndUser($analysisId, $userId);
        if (!$analysis) {
            return ['success' => false, 'error' => 'Analyse introuvable'];
        }

        // Phase 3 branchera réellement Perplexity + parsing + stockage.
        return $this->researchService->run($analysis);
    }

    public function recalculateKeywords(int $analysisId, int $userId): array
    {
        $analysis = $this->repository->findByIdAndUser($analysisId, $userId);
        if (!$analysis) {
            return ['success' => false, 'error' => 'Analyse introuvable'];
        }

        // Placeholder de phase 4, afin de garder l'interface branchable.
        $sample = $this->keywordService->scoreKeyword([
            'estimated_volume' => 200,
            'competition_level' => 45,
            'intent_type' => $analysis['target_type'] ?? 'informationnel',
            'is_local' => true,
        ]);

        return ['success' => true, 'message' => 'Scoring initial calculé (squelette)', 'sample' => $sample];
    }

    public function generateCluster(int $analysisId, int $userId): array
    {
        $analysis = $this->repository->findByIdAndUser($analysisId, $userId);
        if (!$analysis) {
            return ['success' => false, 'error' => 'Analyse introuvable'];
        }

        $cluster = $this->clusterService->generateFromAnalysis($analysis);
        return ['success' => true, 'message' => 'Cluster généré (squelette)', 'cluster' => $cluster];
    }

    public function sendToArticles(int $analysisId, int $userId): array
    {
        $analysis = $this->repository->findByIdAndUser($analysisId, $userId);
        if (!$analysis) {
            return ['success' => false, 'error' => 'Analyse introuvable'];
        }

        return $this->articleService->exportClusterDrafts($analysisId);
    }

    private function decodeJsonField(?string $value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
