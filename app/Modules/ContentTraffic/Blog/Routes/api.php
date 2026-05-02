<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Api\ArticleController;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Api\CategoryController;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Api\TagController;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Api\SeoController;

Route::group([
    'middleware' => config('blog.route_middleware'),
    'prefix' => config('blog.route_prefix'),
    'name' => 'blog.',
], function () {

    // Articles
    Route::apiResource('articles', ArticleController::class)
        ->parameter('article', 'article')
        ->names('articles');

    Route::get('articles/{article}/related', [ArticleController::class, 'related'])
        ->name('articles.related');

    Route::get('articles/{article}/seo-analysis', [ArticleController::class, 'seoAnalysis'])
        ->name('articles.seo-analysis');

    Route::post('articles/{article}/increment-views', [ArticleController::class, 'incrementViews'])
        ->name('articles.increment-views');

    // Categories
    Route::apiResource('categories', CategoryController::class)
        ->parameter('category', 'category')
        ->names('categories');

    Route::get('categories/{category}/articles', [CategoryController::class, 'articles'])
        ->name('categories.articles');

    // Tags
    Route::apiResource('tags', TagController::class)
        ->parameter('tag', 'tag')
        ->names('tags');

    Route::get('tags/{tag}/articles', [TagController::class, 'articles'])
        ->name('tags.articles');

    // SEO
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::post('analyze', [SeoController::class, 'analyze'])
            ->name('analyze');

        Route::get('sitemap', [SeoController::class, 'sitemap'])
            ->name('sitemap');

        Route::get('robots', [SeoController::class, 'robots'])
            ->name('robots');
    });

    // Search
    Route::get('search', [ArticleController::class, 'search'])->name('search');

    // Published articles (public)
    Route::get('published', [ArticleController::class, 'published'])->name('published');
});
