<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'tenant_id',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'tags';
    }

    // ==================== Relations ====================

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            config('blog.table_prefix') . 'post_tags',
            'tag_id',
            'post_id'
        )->withTimestamps();
    }

    // ==================== Scopes ====================

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePopular(Builder $query, $limit = 10): Builder
    {
        return $query->withCount('posts')
                     ->orderByDesc('posts_count')
                     ->limit($limit);
    }

    public function scopeSearch(Builder $query, $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    // ==================== Methods ====================

    public function getPostsCount(): int
    {
        return $this->posts()->published()->count();
    }

    public function getPublishedPosts()
    {
        return $this->posts()->published()->latest('published_at');
    }
}
