<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Web;

use App\Modules\ContentTraffic\Blog\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController
{
    public function index(): View
    {
        $tenantId = auth()->user()?->tenant_id;

        $categories = Category::query()
            ->byTenant($tenantId)
            ->withCount('articles')
            ->get();

        return view('blog::categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('blog::categories.create');
    }

    public function store($request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . config('blog.table_prefix') . 'categories',
            'description' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id;

        Category::create($validated);

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit(Category $category): View
    {
        return view('blog::categories.edit', compact('category'));
    }

    public function update(Category $category, $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|unique:' . config('blog.table_prefix') . 'categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category deleted successfully');
    }

    public function showPublic(Category $category): View
    {
        $articles = $category->articles()
            ->published()
            ->latest('published_at')
            ->paginate(config('blog.article.per_page'));

        return view('blog::categories.public', compact('category', 'articles'));
    }
}
