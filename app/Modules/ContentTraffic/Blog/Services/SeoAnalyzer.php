<?php

namespace App\Modules\ContentTraffic\Blog\Services;

use App\Modules\ContentTraffic\Blog\Models\Article;

class SeoAnalyzer
{
    public function analyze(Article $article): array
    {
        return [
            'title_score' => $this->analyzeTitleScore($article),
            'description_score' => $this->analyzeDescriptionScore($article),
            'content_score' => $this->analyzeContentScore($article),
            'overall_score' => $this->calculateOverallScore($article),
            'recommendations' => $this->getRecommendations($article),
        ];
    }

    public function analyzeContent(array $data): array
    {
        return [
            'title_analysis' => [
                'length' => strlen($data['title']),
                'min_length' => config('blog.seo.min_title_length'),
                'max_length' => config('blog.seo.max_title_length'),
                'score' => $this->calculateTitleScore($data['title']),
            ],
            'description_analysis' => [
                'length' => strlen($data['description']),
                'min_length' => config('blog.seo.min_description_length'),
                'max_length' => config('blog.seo.max_description_length'),
                'score' => $this->calculateDescriptionScore($data['description']),
            ],
            'content_analysis' => [
                'word_count' => str_word_count(strip_tags($data['content'])),
                'keywords_count' => count($data['keywords'] ?? []),
                'score' => $this->calculateContentScore(
                    $data['content'],
                    $data['keywords'] ?? []
                ),
            ],
            'overall_score' => $this->calculateContentOverallScore($data),
        ];
    }

    private function analyzeTitleScore(Article $article): int
    {
        return $this->calculateTitleScore($article->title);
    }

    private function analyzeDescriptionScore(Article $article): int
    {
        $seo = $article->seoMetadata()->first();
        if (!$seo) {
            return 0;
        }

        return $this->calculateDescriptionScore($seo->meta_description ?? '');
    }

    private function analyzeContentScore(Article $article): int
    {
        return $this->calculateContentScore($article->content, []);
    }

    private function calculateTitleScore(string $title): int
    {
        $length = strlen($title);
        $min = config('blog.seo.min_title_length');
        $max = config('blog.seo.max_title_length');

        if ($length < $min || $length > $max) {
            return 60;
        }

        return 100;
    }

    private function calculateDescriptionScore(string $description): int
    {
        $length = strlen($description);
        $min = config('blog.seo.min_description_length');
        $max = config('blog.seo.max_description_length');

        if ($length < $min || $length > $max) {
            return 60;
        }

        return 100;
    }

    private function calculateContentScore(string $content, array $keywords): int
    {
        $wordCount = str_word_count(strip_tags($content));

        if ($wordCount < 300) {
            return 60;
        }

        if ($wordCount < 500) {
            return 80;
        }

        return 100;
    }

    private function calculateOverallScore(Article $article): int
    {
        $titleScore = $this->analyzeTitleScore($article);
        $descriptionScore = $this->analyzeDescriptionScore($article);
        $contentScore = $this->analyzeContentScore($article);

        return (int) (($titleScore + $descriptionScore + $contentScore) / 3);
    }

    private function calculateContentOverallScore(array $data): int
    {
        $titleScore = $this->calculateTitleScore($data['title']);
        $descriptionScore = $this->calculateDescriptionScore($data['description']);
        $contentScore = $this->calculateContentScore($data['content'], $data['keywords'] ?? []);

        return (int) (($titleScore + $descriptionScore + $contentScore) / 3);
    }

    private function getRecommendations(Article $article): array
    {
        $recommendations = [];

        if (strlen($article->title) < config('blog.seo.min_title_length')) {
            $recommendations[] = 'Title is too short. Increase to at least ' . config('blog.seo.min_title_length') . ' characters.';
        }

        if (strlen($article->title) > config('blog.seo.max_title_length')) {
            $recommendations[] = 'Title is too long. Reduce to maximum ' . config('blog.seo.max_title_length') . ' characters.';
        }

        $seo = $article->seoMetadata()->first();
        if (!$seo || !$seo->meta_description) {
            $recommendations[] = 'Add a meta description to improve click-through rate.';
        } elseif (strlen($seo->meta_description) < config('blog.seo.min_description_length')) {
            $recommendations[] = 'Meta description is too short. Increase to at least ' . config('blog.seo.min_description_length') . ' characters.';
        } elseif (strlen($seo->meta_description) > config('blog.seo.max_description_length')) {
            $recommendations[] = 'Meta description is too long. Reduce to maximum ' . config('blog.seo.max_description_length') . ' characters.';
        }

        $wordCount = str_word_count(strip_tags($article->content));
        if ($wordCount < 300) {
            $recommendations[] = 'Article is too short. Aim for at least 300 words for better SEO.';
        }

        return $recommendations;
    }
}
