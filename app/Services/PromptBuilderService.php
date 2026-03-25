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

        $lines = [
            'CONTEXTE',
            sprintf('Tu es un expert en contenu immobilier (%s) orienté performance locale.', $type),
            '',
            'OBJECTIF',
            $clean['objectif'] !== '' ? $clean['objectif'] : 'Produire un contenu immobilier utile, crédible et actionnable.',
            '',
            'CIBLE',
            trim(($clean['persona'] ?: 'Audience locale') . ' à ' . ($clean['ville'] ?: 'zone cible')),
            '',
            'CONTRAINTES',
            '- Utiliser un ton humain et professionnel',
            '- Prioriser les informations concrètes et locales',
            '- Respecter la structure demandée sans inventer de chiffres',
            '',
            'REGLES PLATEFORME',
            $this->toList($platformRules['rules'] ?? []),
            '',
            'SEO',
            '- Mot-clé principal : ' . ($clean['mot_cle'] ?: 'non défini'),
            '- Niveau de conscience : ' . ($clean['niveau_conscience'] ?: 'non défini'),
            '- Type de contenu : ' . ($clean['type_contenu'] ?: $type),
            '',
            'INTERDIT',
            $this->toList($platformRules['forbidden'] ?? []),
            '',
            'FORMAT',
            '- Structure lisible avec titres',
            '- Paragraphes courts',
            '- CTA final discret et pertinent',
            '',
            'SORTIE ATTENDUE',
            'Un contenu final prêt à publication, contextualisé, sans placeholders.',
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
