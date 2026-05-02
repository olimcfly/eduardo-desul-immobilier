<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Services\SeoAnalyzer;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['author', 'categories', 'tags'])
                          ->published()
                          ->latest()
                          ->paginate(10);
        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('articles.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'categories' => 'array',
            'tags' => 'array',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => \Str::slug($validated['title']),
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? now(),
            'author_id' => auth()->id(),
        ]);

        if (isset($validated['categories'])) {
            $article->categories()->attach($validated['categories']);
        }

        if (isset($validated['tags'])) {
            $article->tags()->attach($validated['tags']);
        }

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog_images', 'public');
            $media = \App\Models\Media::create([
                'filename' => $request->file('featured_image')->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $request->file('featured_image')->getClientMimeType(),
                'size' => $request->file('featured_image')->getSize(),
            ]);
            $article->featured_image_id = $media->id;
            $article->save();
        }

        app(SeoAnalyzer::class)->analyze($article);

        return redirect()->route('blog.articles.index')
                         ->with('success', 'Article créé avec succès !');
    }

    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }
}
