<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Api;

use App\Modules\ContentTraffic\Blog\Models\Article;
use App\Modules\ContentTraffic\Blog\Services\ArticleService;
use App\Modules\ContentTraffic\Blog\Services\SeoAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController
{
    public function __construct(
        private ArticleService $articleService,
        private SeoAnalyzer $seoAnalyzer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()?->tenant_id;

        $articles = Article::query()
            ->byTenant($tenantId)
            ->with('category', 'tags', 'seoMetadata')
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($articles);
    }

    public function show(Article $article): JsonResponse
    {
        $this->authorize('view', $article);

        return response()->json($article->load('category', 'tags', 'seoMetadata'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . config('blog.table_prefix') . 'articles',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:' . config('blog.media.max_file_size'),
            'category_id' => 'nullable|exists:' . config('blog.table_prefix') . 'categories,id',
            'status' => 'in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id;

        $article = $this->articleService->create($validated);

        return response()->json($article, 201);
    }

    public function update(Article $article, Request $request): JsonResponse
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'slug' => 'string|unique:' . config('blog.table_prefix') . 'articles,slug,' . $article->id,
            'content' => 'string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:' . config('blog.media.max_file_size'),
            'category_id' => 'nullable|exists:' . config('blog.table_prefix') . 'categories,id',
            'status' => 'in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $article = $this->articleService->update($article, $validated);

        return response()->json($article);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        $this->articleService->delete($article);

        return response()->json(['message' => 'Article deleted successfully']);
    }

    public function related(Article $article): JsonResponse
    {
        $related = Article::query()
            ->byTenant($article->tenant_id)
            ->published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->limit(5)
            ->get();

        return response()->json($related);
    }

    public function seoAnalysis(Article $article): JsonResponse
    {
        $analysis = $this->seoAnalyzer->analyze($article);

        return response()->json($analysis);
    }

    public function incrementViews(Article $article): JsonResponse
    {
        $article->increment('views_count');

        return response()->json(['views_count' => $article->views_count]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        $tenantId = auth()->user()?->tenant_id;

        $results = Article::query()
            ->byTenant($tenantId)
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    public function published(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? request()->header('X-Tenant-ID');

        $articles = Article::query()
            ->where('tenant_id', $tenantId)
            ->published()
            ->with('category', 'tags')
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($articles);
    }
}
