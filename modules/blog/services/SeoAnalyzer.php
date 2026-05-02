<?php

namespace App\Services;

use App\Models\Article;
use App\Models\SeoMetadata;

class SeoAnalyzer
{
    public function analyze(Article $article)
    {
        $score = 0;
        $suggestions = [];

        // 1. Titre
        if (strlen($article->title) < 50 || strlen($article->title) > 60) {
            $suggestions[] = "Le titre doit contenir entre 50 et 60 caractères.";
        } else {
            $score += 20;
        }

        // 2. Méta description
        if (empty($article->seo->meta_description ?? '')) {
            $suggestions[] = "Ajoutez une méta description.";
        } else {
            $score += 20;
        }

        // 3. Mot-clé principal
        if (empty($article->seo->focus_keyword ?? '')) {
            $suggestions[] = "Définissez un mot-clé principal.";
        } else {
            $score += 20;
        }

        // 4. Densité du mot-clé
        $keywordDensity = $this->calculateKeywordDensity($article);
        if ($keywordDensity < 0.5 || $keywordDensity > 2.5) {
            $suggestions[] = "La densité du mot-clé doit être entre 0.5% et 2.5%.";
        } else {
            $score += 20;
        }

        // 5. Liens
        if (substr_count($article->content, '<a href') < 2) {
            $suggestions[] = "Ajoutez au moins 2 liens (internes ou externes).";
        } else {
            $score += 20;
        }

        // Sauvegarder le score SEO
        $article->seo()->updateOrCreate(
            ['post_id' => $article->id],
            [
                'seo_score' => $score,
                'suggestions' => $suggestions,
            ]
        );

        return [
            'score' => $score,
            'suggestions' => $suggestions,
        ];
    }

    private function calculateKeywordDensity(Article $article)
    {
        $keyword = $article->seo->focus_keyword ?? '';
        if (empty($keyword)) return 0;

        $content = strip_tags($article->content);
        $wordCount = str_word_count($content);
        $keywordCount = substr_count(strtolower($content), strtolower($keyword));

        return ($keywordCount / $wordCount) * 100;
    }
}
