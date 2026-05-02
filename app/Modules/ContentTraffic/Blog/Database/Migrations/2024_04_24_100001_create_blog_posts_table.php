<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'posts', function (Blueprint $table) use ($prefix) {
            $table->id();

            // Contenu principal
            $table->string('title', 255)->index();
            $table->string('slug', 255)->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();

            // Images
            $table->string('featured_image')->nullable();

            // Relations
            $table->foreignId('category_id')->nullable()->constrained($prefix . 'categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            // Statut et publication
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();

            // Statistiques
            $table->unsignedBigInteger('views_count')->default(0)->index();
            $table->unsignedInteger('comment_count')->default(0);

            // Multi-tenant
            $table->uuid('tenant_id')->index();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes composés
            $table->index(['tenant_id', 'status', 'published_at']);
            $table->index(['tenant_id', 'author_id']);
            $table->index(['tenant_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'posts');
    }
};
