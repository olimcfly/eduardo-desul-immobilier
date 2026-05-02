<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    protected $table = 'blog_media';
    protected $fillable = ['filename', 'path', 'mime_type', 'alt_text', 'size'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'featured_image_id');
    }
}
