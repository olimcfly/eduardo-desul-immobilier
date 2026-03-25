<?php

/**
 * Phase 2 Skeleton (pas d'appel HTTP dans cette phase)
 * Phase 3 implémentera la connexion API Perplexity.
 */
class PerplexityMarketResearchService
{
    public function buildPrompt(array $analysis): string
    {
        $city = (string) ($analysis['city'] ?? '');
        $postalCode = (string) ($analysis['postal_code'] ?? '');
        $areaName = (string) ($analysis['area_name'] ?? '');
        $targetType = (string) ($analysis['target_type'] ?? 'mixte');
        $propertyType = (string) ($analysis['property_type'] ?? '');

        return "Analyse le marché immobilier local et réponds EXCLUSIVEMENT en JSON structuré.\n"
            . "Ville: {$city}\n"
            . "Code postal: {$postalCode}\n"
            . "Secteur: {$areaName}\n"
            . "Cible: {$targetType}\n"
            . "Type de bien dominant: {$propertyType}\n";
    }

    public function run(array $analysis): array
    {
        return [
            'success' => false,
            'provider' => 'perplexity',
            'error' => 'Phase 3 non implémentée: appel API Perplexity désactivé.',
            'raw_response' => null,
        ];
    }
}
