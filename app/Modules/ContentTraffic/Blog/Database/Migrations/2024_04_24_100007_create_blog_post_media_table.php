<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'post_media', function (Blueprint $table) use ($prefix) {
            $table->id();

            $table->foreignId('post_id')->constrained($prefix . 'posts')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained($prefix . 'media')->cascadeOnDelete();

            // Ordre d'affichage
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->unique(['post_id', 'media_id']);
            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'post_media');
    }
};
