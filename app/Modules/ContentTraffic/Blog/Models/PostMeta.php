<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PostMeta extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
        'tenant_id',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'post_meta';
    }

    // ==================== Relations ====================

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // ==================== Scopes ====================

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('meta_key', $key);
    }

    public function scopeByPost(Builder $query, int $postId): Builder
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    // ==================== SEO Meta Keys ====================

    // Clés de métadonnées SEO standards
    public const META_KEYS = [
        'seo_title' => 'Titre SEO',
        'seo_description' => 'Description SEO',
        'seo_keywords' => 'Mots-clés',
        'focus_keyword' => 'Mot-clé focus',
        'og_title' => 'Titre Open Graph',
        'og_description' => 'Description Open Graph',
        'og_image' => 'Image Open Graph',
        'canonical_url' => 'URL Canonique',
        'robots' => 'Robots (index, follow, etc)',
        'read_more_text' => 'Texte "Lire plus"',
    ];

    // ==================== Methods ====================

    public function getSeoTitle(): ?string
    {
        return $this->post->meta('seo_title') ?? $this->post->title;
    }

    public function getSeoDescription(): ?string
    {
        return $this->post->meta('seo_description') ?? $this->post->excerpt;
    }

    public function getKeywords(): array
    {
        $keywords = $this->post->meta('seo_keywords');
        return $keywords ? array_map('trim', explode(',', $keywords)) : [];
    }

    public function getFocusKeyword(): ?string
    {
        return $this->post->meta('focus_keyword');
    }

    public static function getMetaKeyLabel(string $key): string
    {
        return self::META_KEYS[$key] ?? $key;
    }

    public static function getAvailableKeys(): array
    {
        return array_keys(self::META_KEYS);
    }
}
