<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.table_prefix');

        Schema::create($prefix . 'categories', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->uuid('tenant_id');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.table_prefix') . 'categories');
    }
};
