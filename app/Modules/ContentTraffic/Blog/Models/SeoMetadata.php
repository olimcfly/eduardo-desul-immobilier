<?php

namespace App\Modules\ContentTraffic\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $fillable = [
        'article_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'tenant_id',
    ];

    public function getTable(): string
    {
        return config('blog.table_prefix') . 'seo_metadata';
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
