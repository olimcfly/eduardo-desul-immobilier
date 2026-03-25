<?php

/**
 * Phase 2 Skeleton: génération locale pilier + satellites (sans IA pour l'instant).
 */
class ContentClusterGeneratorService
{
    public function generateFromAnalysis(array $analysis): array
    {
        $city = (string) ($analysis['city'] ?? 'votre ville');
        $target = (string) ($analysis['target_type'] ?? 'mixte');
        $property = (string) ($analysis['property_type'] ?? 'immobilier');
        $mainKeyword = "marché {$property} {$city}";

        return [
            'main_keyword' => $mainKeyword,
            'cluster_title' => "Cluster marché immobilier {$city}",
            'pillar' => [
                'title' => "Marché immobilier à {$city} : guide complet {$target}",
                'keyword' => $mainKeyword,
                'intent_type' => $target,
                'angle' => 'Analyse locale orientée décision',
            ],
            'satellites' => [
                ['title' => "Prix au m² à {$city}", 'keyword' => "prix m2 {$city}", 'intent_type' => 'informationnel', 'angle' => 'chiffres et tendances', 'internal_link_target' => 'pillar'],
                ['title' => "Vendre vite à {$city}", 'keyword' => "vendre {$city}", 'intent_type' => 'vendeur', 'angle' => 'objections vendeurs', 'internal_link_target' => 'pillar'],
                ['title' => "Acheter à {$city} en 2026", 'keyword' => "acheter {$city}", 'intent_type' => 'acheteur', 'angle' => 'checklist décision', 'internal_link_target' => 'pillar'],
                ['title' => "Quartiers à surveiller à {$city}", 'keyword' => "quartiers {$city}", 'intent_type' => 'local', 'angle' => 'zones chaudes', 'internal_link_target' => 'pillar'],
                ['title' => "FAQ marché immobilier {$city}", 'keyword' => "faq immobilier {$city}", 'intent_type' => 'informationnel', 'angle' => 'réponses rapides', 'internal_link_target' => 'pillar'],
            ],
        ];
    }
}
