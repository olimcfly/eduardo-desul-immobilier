<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\SeoAnalyzer;

class SeoController extends Controller
{
    public function dashboard()
    {
        $articles = Article::with('seo')->published()->get();
        return view('seo.dashboard', compact('articles'));
    }

    public function analyze(Article $article)
    {
        $result = app(SeoAnalyzer::class)->analyze($article);
        return response()->json($result);
    }
}
