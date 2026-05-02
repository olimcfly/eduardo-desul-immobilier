<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'tenant_id',
        'display_order',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'categories';
    }

    // ==================== Relations ====================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('display_order')
                    ->orderBy('name');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // ==================== Scopes ====================

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    public function scopeWithPostCount(Builder $query): Builder
    {
        return $query->withCount('posts');
    }

    public function scopeSearch(Builder $query, $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('description', 'like', "%{$search}%");
    }

    // ==================== Methods ====================

    public function getHierarchy(): array
    {
        return array_merge(
            $this->parent ? $this->parent->getHierarchy() : [],
            [$this->id => $this->name]
        );
    }

    public function getHierarchyPath(): string
    {
        return implode(' / ', $this->getHierarchy());
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function getDescendants(): array
    {
        $descendants = [];
        foreach ($this->children as $child) {
            $descendants[$child->id] = $child->name;
            $descendants = array_merge($descendants, $child->getDescendants());
        }
        return $descendants;
    }

    public function getPublishedPostsCount(): int
    {
        return $this->posts()
            ->published()
            ->count();
    }
}
