<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoMetadata extends Model
{
    protected $table = 'blog_seo_metadata';
    protected $fillable = [
        'post_id', 'meta_title', 'meta_description',
        'focus_keyword', 'canonical_url', 'robots_meta',
        'seo_score', 'suggestions'
    ];

    protected $casts = [
        'suggestions' => 'array',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'post_id');
    }
}
