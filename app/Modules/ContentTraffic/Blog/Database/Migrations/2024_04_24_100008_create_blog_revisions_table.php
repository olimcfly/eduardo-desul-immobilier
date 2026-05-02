<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'revisions', function (Blueprint $table) use ($prefix) {
            $table->id();

            $table->foreignId('post_id')->constrained($prefix . 'posts')->cascadeOnDelete();
            $table->longText('content');

            // Multi-tenant
            $table->uuid('tenant_id')->index();

            // Timestamp de création uniquement
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['post_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'revisions');
    }
};
