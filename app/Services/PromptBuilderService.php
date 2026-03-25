<?php

class PromptBuilderService
{
    private array $rules;

    private array $allowedVariables = [
        'ville',
        'persona',
        'objectif',
        'mot_cle',
        'niveau_conscience',
        'type_contenu',
    ];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function buildPrompt(string $type, ?string $plateforme, array $data): string
    {
        $clean = $this->sanitizeData($data);
        $platformRules = $this->rules[$plateforme] ?? $this->rules['default'];

        $typeLabel = $clean['type_contenu'] !== '' ? $clean['type_contenu'] : $type;

        $lines = [
            'CONTEXTE',
            sprintf(
                'Tu es un expert senior en marketing immobilier local (%s), UX/UI, copywriting et conversion.',
                $typeLabel
            ),
            sprintf('Zone ciblée : %s. Audience : %s.', $clean['ville'] ?: 'zone locale à définir', $clean['persona'] ?: 'audience locale'),
            '',
            'OBJECTIF',
            $clean['objectif'] !== ''
                ? $clean['objectif']
                : 'Produire un contenu/action marketing prêt à exécuter, orienté conversion et crédibilité.',
            '',
            'RÈGLES MARKETING À RESPECTER',
            '- Prioriser un message clair : promesse principale + bénéfice concret pour le prospect.',
            '- Suivre une logique conversion : Hook → Valeur → Réassurance → CTA.',
            '- Éviter les promesses irréalistes ou non vérifiables.',
            '',
            'RÈGLES SEO À RESPECTER',
            '- Mot-clé principal : ' . ($clean['mot_cle'] ?: 'non défini'),
            '- Niveau de conscience : ' . ($clean['niveau_conscience'] ?: 'non défini'),
            '- Intégrer des variantes sémantiques naturelles (pas de keyword stuffing).',
            '- Structurer le contenu avec des titres lisibles et une intention de recherche claire.',
            '',
            'STYLE VISUEL ATTENDU',
            '- Ton premium, rassurant, lisible et professionnel.',
            '- Style rédactionnel : phrases courtes, rythme fluide, vocabulaire concret.',
            '- Conserver une cohérence de marque locale immobilière.',
            '',
            'CONTRAINTES DE DESIGN',
            '- Lisibilité mobile prioritaire.',
            '- Hiérarchie visuelle claire (titres, sous-titres, blocs de preuve).',
            '- CTA visible sans être agressif.',
            '',
            'STRUCTURE ATTENDUE',
            '- 1) Hook d\'ouverture centré sur le problème client.',
            '- 2) Proposition de valeur et différenciation locale.',
            '- 3) Preuves / réassurance (méthode, expérience, données locales si disponibles).',
            '- 4) Passage à l\'action (CTA principal + option de contact).',
            '',
            'COPYWRITING',
            '- Utiliser un ton humain, direct et empathique.',
            '- Mettre en avant les bénéfices client avant les caractéristiques.',
            '- Conclure avec une action simple, sans pression commerciale excessive.',
            '',
            'CONTENU À UTILISER',
            '- Plateforme : ' . ($plateforme ?: 'générique'),
            '- Type de contenu : ' . $typeLabel,
            '- Contraintes plateforme :',
            $this->toList($platformRules['rules'] ?? []),
            '- Éléments à éviter :',
            $this->toList($platformRules['forbidden'] ?? []),
            '',
            'IMPORTANT',
            '- Ne pas inventer de statistiques, prix ou garanties sans source fournie.',
            '- Ne pas laisser de placeholders non remplacés.',
            '- Toujours contextualiser avec la ville et le persona quand disponibles.',
            '',
            'LIVRABLE ATTENDU',
            'Une sortie finale propre, directement exploitable, structurée par sections, sans commentaires techniques.',
        ];

        return implode("\n", $lines);
    }

    public function renderTemplate(string $template, array $data): string
    {
        $clean = $this->sanitizeData($data);

        return (string) preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/i', function ($matches) use ($clean) {
            $key = strtolower($matches[1]);
            if (!in_array($key, $this->allowedVariables, true)) {
                return '';
            }

            return $clean[$key] ?? '';
        }, $template);
    }

    public function suggestStrategy(array $data): array
    {
        $clean = $this->sanitizeData($data);
        $city = $clean['ville'] ?: 'votre ville';
        $keyword = $clean['mot_cle'] ?: 'immobilier local';

        return [
            'article_pilier' => sprintf('Guide complet : %s à %s (%s)', $keyword, $city, date('Y')),
            'articles_satellites' => [
                sprintf('Prix au m² : tendances %s à %s', date('Y'), $city),
                sprintf('Vendre rapidement à %s : étapes clés', $city),
                sprintf('Acheter à %s : erreurs à éviter', $city),
                sprintf('Quartiers porteurs à %s : analyse locale', $city),
                sprintf('Checklist vendeur : préparer son bien à %s', $city),
            ],
            'plan_seo_local' => [
                'Créer une page pilier ciblant le mot-clé principal et la ville.',
                'Mailler 5 contenus satellites vers le pilier avec ancres locales.',
                'Publier 1 contenu/semaine et actualiser les données marché mensuellement.',
                'Décliner le pilier en post Google Business Profile et réseaux sociaux.',
            ],
        ];
    }

    private function sanitizeData(array $data): array
    {
        $clean = [];
        foreach ($this->allowedVariables as $key) {
            $value = isset($data[$key]) ? (string) $data[$key] : '';
            $value = strip_tags($value);
            $value = preg_replace('/[\r\n\t]+/', ' ', $value);
            $value = preg_replace('/\s{2,}/', ' ', $value);
            $value = trim($value);
            $clean[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $clean;
    }

    private function toList(array $items): string
    {
        if (empty($items)) {
            return '- Aucune règle spécifique';
        }

        $safeItems = array_map(static function ($item) {
            return '- ' . htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8');
        }, $items);

        return implode("\n", $safeItems);
    }
}
