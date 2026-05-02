<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Web\BlogController;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Web\ArticleController as WebArticleController;
use App\Modules\ContentTraffic\Blog\Http\Controllers\Web\CategoryController as WebCategoryController;

Route::group([
    'middleware' => config('blog.web_middleware'),
    'prefix' => config('blog.web_prefix'),
    'name' => 'blog.',
], function () {

    // Dashboard
    Route::get('/', [BlogController::class, 'dashboard'])->name('dashboard');

    // Articles Management
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('/', [WebArticleController::class, 'index'])->name('index');
        Route::get('create', [WebArticleController::class, 'create'])->name('create');
        Route::post('/', [WebArticleController::class, 'store'])->name('store');
        Route::get('{article}', [WebArticleController::class, 'show'])->name('show');
        Route::get('{article}/edit', [WebArticleController::class, 'edit'])->name('edit');
        Route::put('{article}', [WebArticleController::class, 'update'])->name('update');
        Route::delete('{article}', [WebArticleController::class, 'destroy'])->name('destroy');
        Route::post('{article}/publish', [WebArticleController::class, 'publish'])->name('publish');
        Route::post('{article}/unpublish', [WebArticleController::class, 'unpublish'])->name('unpublish');
    });

    // Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [WebCategoryController::class, 'index'])->name('index');
        Route::get('create', [WebCategoryController::class, 'create'])->name('create');
        Route::post('/', [WebCategoryController::class, 'store'])->name('store');
        Route::get('{category}/edit', [WebCategoryController::class, 'edit'])->name('edit');
        Route::put('{category}', [WebCategoryController::class, 'update'])->name('update');
        Route::delete('{category}', [WebCategoryController::class, 'destroy'])->name('destroy');
    });

    // Public Blog Pages
    Route::get('articles/{article:slug}', [WebArticleController::class, 'showPublic'])
        ->name('articles.public');

    Route::get('categories/{category:slug}', [WebCategoryController::class, 'showPublic'])
        ->name('categories.public');
});
