<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'post_tags', function (Blueprint $table) use ($prefix) {
            $table->id();

            $table->foreignId('post_id')->constrained($prefix . 'posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained($prefix . 'tags')->cascadeOnDelete();

            $table->timestamps();

            // Indexes
            $table->unique(['post_id', 'tag_id']);
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'post_tags');
    }
};
