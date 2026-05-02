<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blog_seo_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained('blog_posts')->onDelete('cascade');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots_meta')->default('index,follow');
            $table->integer('seo_score')->default(0);
            $table->json('suggestions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_seo_metadata');
    }
};
