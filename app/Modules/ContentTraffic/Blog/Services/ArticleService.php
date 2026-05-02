<?php

namespace App\Modules\ContentTraffic\Blog\Services;

use App\Modules\ContentTraffic\Blog\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        private MediaUploader $mediaUploader,
    ) {}

    public function create(array $data): Article
    {
        $article = Article::create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'content' => $data['content'],
            'excerpt' => $data['excerpt'] ?? Str::limit(strip_tags($data['content']), config('blog.article.excerpt_length')),
            'featured_image' => $data['featured_image'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'tenant_id' => $data['tenant_id'],
        ]);

        if (isset($data['featured_image']) && $data['featured_image']) {
            $path = $this->mediaUploader->upload($data['featured_image']);
            $article->update(['featured_image' => $path]);
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        if (isset($data['seo']) && is_array($data['seo'])) {
            $article->seoMetadata()->create($data['seo']);
        }

        cache()->forget(config('blog.cache.key_prefix') . 'articles');

        return $article->fresh();
    }

    public function update(Article $article, array $data): Article
    {
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }

        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
            $updateData['excerpt'] = $data['excerpt'] ?? Str::limit(strip_tags($data['content']), config('blog.article.excerpt_length'));
        }

        if (isset($data['featured_image']) && $data['featured_image']) {
            if ($article->featured_image) {
                Storage::disk(config('blog.media.disk'))->delete($article->featured_image);
            }
            $path = $this->mediaUploader->upload($data['featured_image']);
            $updateData['featured_image'] = $path;
        }

        if (isset($data['category_id'])) {
            $updateData['category_id'] = $data['category_id'];
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['published_at'])) {
            $updateData['published_at'] = $data['published_at'];
        }

        $article->update($updateData);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        if (isset($data['seo']) && is_array($data['seo'])) {
            $article->seoMetadata()->updateOrCreate(
                ['article_id' => $article->id],
                $data['seo']
            );
        }

        cache()->forget(config('blog.cache.key_prefix') . 'articles');
        cache()->forget(config('blog.cache.key_prefix') . 'article:' . $article->id);

        return $article->fresh();
    }

    public function delete(Article $article): bool
    {
        if ($article->featured_image) {
            Storage::disk(config('blog.media.disk'))->delete($article->featured_image);
        }

        $article->tags()->detach();
        $article->seoMetadata()->delete();

        cache()->forget(config('blog.cache.key_prefix') . 'articles');
        cache()->forget(config('blog.cache.key_prefix') . 'article:' . $article->id);

        return $article->delete();
    }

    public function publish(Article $article): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $article;
    }

    public function unpublish(Article $article): Article
    {
        $article->update(['status' => 'draft']);

        return $article;
    }
}
