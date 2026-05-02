<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Api;

use App\Modules\ContentTraffic\Blog\Models\Category;
use App\Modules\ContentTraffic\Blog\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()?->tenant_id;

        $categories = Category::query()
            ->byTenant($tenantId)
            ->withCount('articles')
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . config('blog.table_prefix') . 'categories',
            'description' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id;

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('articles'));
    }

    public function update(Category $category, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|unique:' . config('blog.table_prefix') . 'categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function articles(Category $category, Request $request): JsonResponse
    {
        $articles = Article::query()
            ->where('category_id', $category->id)
            ->published()
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($articles);
    }
}
