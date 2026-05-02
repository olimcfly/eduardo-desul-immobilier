<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'path',
        'filename',
        'mime_type',
        'alt_text',
        'size',
        'width',
        'height',
        'tenant_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'media';
    }

    // ==================== Relations ====================

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            config('blog.table_prefix') . 'post_media',
            'media_id',
            'post_id'
        )->withTimestamps();
    }

    // ==================== Scopes ====================

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'application/%');
    }

    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('mime_type', 'like', "$type/%");
    }

    public function scopeRecent(Builder $query, $limit = 20): Builder
    {
        return $query->latest('created_at')->limit($limit);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('filename', 'like', "%{$search}%")
                     ->orWhere('alt_text', 'like', "%{$search}%");
    }

    public function scopeUnused(Builder $query): Builder
    {
        return $query->doesntHave('posts');
    }

    // ==================== Accessors ====================

    public function getUrlAttribute(): string
    {
        $disk = config('blog.media.disk', 'public');
        return Storage::disk($disk)->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsDocumentAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'application/');
    }

    public function getFilesizeMbAttribute(): float
    {
        return round($this->size / 1024 / 1024, 2);
    }

    public function getTypeAttribute(): string
    {
        return explode('/', $this->mime_type)[0];
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    // ==================== Methods ====================

    public function delete(): ?bool
    {
        $disk = config('blog.media.disk', 'public');

        if (Storage::disk($disk)->exists($this->path)) {
            Storage::disk($disk)->delete($this->path);
        }

        return parent::delete();
    }

    public function exists(): bool
    {
        $disk = config('blog.media.disk', 'public');
        return Storage::disk($disk)->exists($this->path);
    }

    public function getResponsiveUrl(string $size = 'medium'): string
    {
        $sizes = [
            'thumb' => 'thumb_',
            'small' => 'small_',
            'medium' => 'medium_',
            'large' => 'large_',
        ];

        $prefix = $sizes[$size] ?? '';
        $info = pathinfo($this->path);
        $responsivePath = $info['dirname'] . '/' . $prefix . $info['filename'] . '.' . $info['extension'];

        $disk = config('blog.media.disk', 'public');

        if (Storage::disk($disk)->exists($responsivePath)) {
            return Storage::disk($disk)->url($responsivePath);
        }

        return $this->url;
    }

    public function setAltText(string $altText): self
    {
        $this->update(['alt_text' => $altText]);
        return $this;
    }

    public function getPostsCount(): int
    {
        return $this->posts()->count();
    }

    public function isUsed(): bool
    {
        return $this->getPostsCount() > 0;
    }

    public function isDuplicate(Media $other): bool
    {
        return $this->mime_type === $other->mime_type
            && $this->size === $other->size
            && hash_file('md5', storage_path('app/' . $this->path)) ===
               hash_file('md5', storage_path('app/' . $other->path));
    }
}
