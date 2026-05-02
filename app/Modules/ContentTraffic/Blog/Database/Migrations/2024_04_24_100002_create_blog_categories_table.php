<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix', 'blog_');

        Schema::create($prefix . 'categories', function (Blueprint $table) use ($prefix) {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();

            // Hiérarchie
            $table->foreignId('parent_id')->nullable()->constrained($prefix . 'categories')->nullOnDelete();

            // Affichage
            $table->unsignedInteger('display_order')->default(0);

            // Multi-tenant
            $table->uuid('tenant_id')->index();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'parent_id']);
            $table->index(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix', 'blog_') . 'categories');
    }
};
