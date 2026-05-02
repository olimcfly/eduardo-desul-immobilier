<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Web;

use App\Modules\ContentTraffic\Blog\Models\Article;
use App\Modules\ContentTraffic\Blog\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController
{
    public function __construct(private ArticleService $articleService) {}

    public function index(): View
    {
        $tenantId = auth()->user()?->tenant_id;

        $articles = Article::query()
            ->byTenant($tenantId)
            ->with('category', 'tags')
            ->latest('updated_at')
            ->paginate(config('blog.article.per_page'));

        return view('blog::articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('blog::articles.create');
    }

    public function store($request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . config('blog.table_prefix') . 'articles',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image',
            'category_id' => 'nullable|exists:' . config('blog.table_prefix') . 'categories,id',
            'tags' => 'array',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id;

        $article = $this->articleService->create($validated);

        return redirect()->route('blog.articles.show', $article)
            ->with('success', 'Article created successfully');
    }

    public function show(Article $article): View
    {
        return view('blog::articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        $this->authorize('update', $article);

        return view('blog::articles.edit', compact('article'));
    }

    public function update(Article $article, $request): RedirectResponse
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'slug' => 'string|unique:' . config('blog.table_prefix') . 'articles,slug,' . $article->id,
            'content' => 'string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image',
            'category_id' => 'nullable|exists:' . config('blog.table_prefix') . 'categories,id',
            'tags' => 'array',
        ]);

        $this->articleService->update($article, $validated);

        return redirect()->route('blog.articles.show', $article)
            ->with('success', 'Article updated successfully');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        $this->articleService->delete($article);

        return redirect()->route('blog.articles.index')
            ->with('success', 'Article deleted successfully');
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Article published successfully');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $article->update(['status' => 'draft']);

        return back()->with('success', 'Article unpublished successfully');
    }

    public function showPublic(Article $article): View
    {
        $article->increment('views_count');

        $related = $article->category?->articles()
            ->published()
            ->where('id', '!=', $article->id)
            ->limit(3)
            ->get() ?? collect();

        return view('blog::articles.public', compact('article', 'related'));
    }
}
