<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Api;

use App\Modules\ContentTraffic\Blog\Models\Article;
use App\Modules\ContentTraffic\Blog\Services\SeoAnalyzer;
use App\Modules\ContentTraffic\Blog\Services\SitemapGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeoController
{
    public function __construct(
        private SeoAnalyzer $seoAnalyzer,
        private SitemapGenerator $sitemapGenerator,
    ) {}

    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',
            'keywords' => 'array',
        ]);

        $analysis = $this->seoAnalyzer->analyzeContent($validated);

        return response()->json($analysis);
    }

    public function sitemap(): Response
    {
        $tenantId = auth()->user()?->tenant_id ?? request()->header('X-Tenant-ID');

        $xml = $this->sitemapGenerator->generate($tenantId);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /api/private\n";
        $content .= "Sitemap: " . route('blog.seo.sitemap') . "\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
