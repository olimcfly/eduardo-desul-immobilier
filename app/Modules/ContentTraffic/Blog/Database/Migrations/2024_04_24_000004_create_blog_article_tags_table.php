<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix');

        Schema::create($prefix . 'article_tags', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('article_id')->constrained($prefix . 'articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained($prefix . 'tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['article_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix') . 'article_tags');
    }
};
