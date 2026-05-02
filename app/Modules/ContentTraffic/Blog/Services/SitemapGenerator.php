<?php

namespace App\Modules\ContentTraffic\Blog\Services;

use App\Modules\ContentTraffic\Blog\Models\Article;
use App\Modules\ContentTraffic\Blog\Models\Category;

class SitemapGenerator
{
    public function generate(string $tenantId): string
    {
        $articles = Article::byTenant($tenantId)
            ->published()
            ->get();

        $categories = Category::byTenant($tenantId)->get();

        return $this->buildSitemap($articles, $categories);
    }

    private function buildSitemap($articles, $categories): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($articles as $article) {
            $xml .= $this->buildUrlEntry(
                route('blog.articles.public', $article->slug),
                $article->updated_at,
                'weekly',
                0.8
            );
        }

        foreach ($categories as $category) {
            $xml .= $this->buildUrlEntry(
                route('blog.categories.public', $category->slug),
                $category->updated_at,
                'weekly',
                0.6
            );
        }

        $xml .= "</urlset>";

        return $xml;
    }

    private function buildUrlEntry(string $url, $lastmod, string $changefreq, float $priority): string
    {
        return sprintf(
            "\t<url>\n\t\t<loc>%s</loc>\n\t\t<lastmod>%s</lastmod>\n\t\t<changefreq>%s</changefreq>\n\t\t<priority>%s</priority>\n\t</url>\n",
            htmlspecialchars($url),
            $lastmod->toAtomString(),
            $changefreq,
            number_format($priority, 1)
        );
    }
}
