<?php

/**
 * Phase 2 Skeleton: parse défensif d'une réponse JSON IA.
 */
class MarketInsightParserService
{
    public function parse(?string $rawResponse): array
    {
        if (!$rawResponse) {
            return $this->emptyPayload();
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            return $this->emptyPayload();
        }

        return [
            'summary' => (string) ($decoded['summary'] ?? ''),
            'market_trends' => $this->normalizeArray($decoded['market_trends'] ?? []),
            'pricing_data' => $this->normalizeArray($decoded['pricing_data'] ?? []),
            'audience_profiles' => $this->normalizeArray($decoded['audience_profiles'] ?? []),
            'faq_data' => $this->normalizeArray($decoded['faq_data'] ?? []),
            'seo_opportunities' => $this->normalizeArray($decoded['seo_opportunities'] ?? []),
            'business_recommendations' => $this->normalizeArray($decoded['business_recommendations'] ?? []),
        ];
    }

    private function normalizeArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function emptyPayload(): array
    {
        return [
            'summary' => '',
            'market_trends' => [],
            'pricing_data' => [],
            'audience_profiles' => [],
            'faq_data' => [],
            'seo_opportunities' => [],
            'business_recommendations' => [],
        ];
    }
}
