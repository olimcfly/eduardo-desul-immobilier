<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'post_meta', function (Blueprint $table) use ($prefix) {
            $table->id();

            $table->foreignId('post_id')->constrained($prefix . 'posts')->cascadeOnDelete();
            $table->string('meta_key', 255)->index();
            $table->longText('meta_value')->nullable();

            // Multi-tenant
            $table->uuid('tenant_id')->index();

            // Indexes
            $table->unique(['post_id', 'meta_key']);
            $table->index(['tenant_id', 'meta_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'post_meta');
    }
};
