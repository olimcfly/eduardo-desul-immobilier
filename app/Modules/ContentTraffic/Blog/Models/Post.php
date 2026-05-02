<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'category_id',
        'tenant_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'posts';
    }

    // ==================== Relations ====================

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            config('blog.table_prefix') . 'post_tags',
            'post_id',
            'tag_id'
        )->withTimestamps();
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(
            Media::class,
            config('blog.table_prefix') . 'post_media',
            'post_id',
            'media_id'
        )->withTimestamps();
    }

    public function meta(): HasMany
    {
        return $this->hasMany(PostMeta::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    // ==================== Scopes ====================

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
                     ->where('published_at', '>', now());
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByAuthor(Builder $query, $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByTag(Builder $query, $tagId): Builder
    {
        return $query->whereHas('tags', fn($q) => $q->where('tag_id', $tagId));
    }

    public function scopeSearchTitle(Builder $query, $search): Builder
    {
        return $query->where('title', 'like', "%{$search}%");
    }

    public function scopeSearchContent(Builder $query, $search): Builder
    {
        return $query->where('content', 'like', "%{$search}%")
                     ->orWhere('excerpt', 'like', "%{$search}%");
    }

    public function scopeRecent(Builder $query, $limit = 10): Builder
    {
        return $query->latest('published_at')->limit($limit);
    }

    public function scopeMostViewed(Builder $query, $limit = 10): Builder
    {
        return $query->orderByDesc('views_count')->limit($limit);
    }

    // ==================== Accessors ====================

    public function getMetaAttribute($key): ?string
    {
        return $this->meta()
            ->where('meta_key', $key)
            ->value('meta_value');
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->getMeta('seo_title') ?? $this->title;
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getMeta('seo_description');
    }

    public function getSeoKeywordsAttribute(): ?string
    {
        return $this->getMeta('seo_keywords');
    }

    public function getOgImageAttribute(): ?string
    {
        return $this->getMeta('og_image') ?? $this->featured_image;
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200));
    }

    // ==================== Methods ====================

    public function getMeta(string $key): ?string
    {
        return $this->meta()
            ->where('meta_key', $key)
            ->pluck('meta_value')
            ->first();
    }

    public function setMeta(string $key, string $value): self
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value, 'tenant_id' => $this->tenant_id]
        );

        return $this;
    }

    public function publish(): self
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $this;
    }

    public function unpublish(): self
    {
        $this->update(['status' => 'draft']);

        return $this;
    }

    public function archive(): self
    {
        $this->update(['status' => 'archived']);

        return $this;
    }

    public function createRevision(): Revision
    {
        return $this->revisions()->create([
            'content' => $this->content,
            'tenant_id' => $this->tenant_id,
        ]);
    }

    public function restoreRevision(Revision $revision): self
    {
        $this->update(['content' => $revision->content]);

        return $this;
    }

    public function incrementViews(): int
    {
        return $this->increment('views_count');
    }

    public function getViewsCount(): int
    {
        return $this->views_count ?? 0;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at?->isFuture();
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function canPublish(): bool
    {
        return !empty($this->title) && !empty($this->content) && !empty($this->slug);
    }
}
