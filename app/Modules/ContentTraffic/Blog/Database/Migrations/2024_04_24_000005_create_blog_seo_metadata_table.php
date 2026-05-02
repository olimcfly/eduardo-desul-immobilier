<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix');

        Schema::create($prefix . 'seo_metadata', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('article_id')->constrained($prefix . 'articles')->cascadeOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->uuid('tenant_id');
            $table->timestamps();

            $table->unique('article_id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix') . 'seo_metadata');
    }
};
