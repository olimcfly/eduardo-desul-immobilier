<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Revision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'content',
        'created_at',
        'tenant_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'revisions';
    }

    // ==================== Relations ====================

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // ==================== Scopes ====================

    public function scopeByPost(Builder $query, int $postId): Builder
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeRecent(Builder $query, $limit = 10): Builder
    {
        return $query->latest('created_at')->limit($limit);
    }

    public function scopeOlderThan(Builder $query, $days = 30): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    // ==================== Methods ====================

    public function restore(): bool
    {
        return $this->post->restoreRevision($this) !== null;
    }

    public function diff(Revision $other): array
    {
        return [
            'current' => $this->content,
            'previous' => $other->content,
            'changes' => $this->calculateDiff($other->content, $this->content),
        ];
    }

    public function getContentLength(): int
    {
        return strlen($this->content);
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    public function getReadingTime(): int
    {
        return max(1, ceil($this->getWordCount() / 200));
    }

    public function getCharacterCount(): int
    {
        return strlen(strip_tags($this->content));
    }

    public function isOlderThan($days = 30): bool
    {
        return $this->created_at->diffInDays(now()) > $days;
    }

    // ==================== Utilities ====================

    private function calculateDiff(string $original, string $modified): array
    {
        // Calcul simple des différences
        $originalWords = str_word_count($original, 1);
        $modifiedWords = str_word_count($modified, 1);

        $added = array_diff($modifiedWords, $originalWords);
        $removed = array_diff($originalWords, $modifiedWords);

        return [
            'added_words' => count($added),
            'removed_words' => count($removed),
            'added_percentage' => round((count($added) / (count($modifiedWords) ?: 1)) * 100, 2),
        ];
    }

    // ==================== Static Methods ====================

    public static function cleanOldRevisions($daysOld = 90): int
    {
        return self::olderThan($daysOld)->delete();
    }

    public static function getPostRevisionCount(int $postId): int
    {
        return self::byPost($postId)->count();
    }

    public static function getLatestRevision(int $postId): ?self
    {
        return self::byPost($postId)->latest()->first();
    }
}
