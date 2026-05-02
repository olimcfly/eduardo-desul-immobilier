<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->group(function () {
    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('blog.articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('blog.articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('blog.articles.store');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('blog.articles.show');

    // SEO
    Route::get('/seo', [SeoController::class, 'dashboard'])->name('blog.seo.dashboard');
});
