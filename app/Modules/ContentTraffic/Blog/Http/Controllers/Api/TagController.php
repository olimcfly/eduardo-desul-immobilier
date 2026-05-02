<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Api;

use App\Modules\ContentTraffic\Blog\Models\Tag;
use App\Modules\ContentTraffic\Blog\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()?->tenant_id;

        $tags = Tag::query()
            ->byTenant($tenantId)
            ->withCount('articles')
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($tags);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . config('blog.table_prefix') . 'tags',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id;

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json($tag->load('articles'));
    }

    public function update(Tag $tag, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|unique:' . config('blog.table_prefix') . 'tags,slug,' . $tag->id,
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }

    public function articles(Tag $tag, Request $request): JsonResponse
    {
        $articles = Article::query()
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tag_id', $tag->id);
            })
            ->published()
            ->paginate(config('blog.pagination.per_page'));

        return response()->json($articles);
    }
}
