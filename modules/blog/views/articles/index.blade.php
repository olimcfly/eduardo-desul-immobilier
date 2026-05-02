@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Articles du Blog</h1>
    <a href="{{ route('blog.articles.create') }}" class="btn btn-primary mb-4">Nouvel Article</a>

    <div class="row">
        @foreach($articles as $article)
        <div class="col-md-4 mb-4">
            <div class="card">
                @if($article->featuredImage)
                <img src="{{ asset('storage/' . $article->featuredImage->path) }}" class="card-img-top" alt="{{ $article->featuredImage->alt_text }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $article->title }}</h5>
                    <p class="card-text">{{ Str::limit($article->excerpt, 100) }}</p>
                    <a href="{{ route('blog.articles.show', $article) }}" class="btn btn-primary">Lire la suite</a>
                </div>
                <div class="card-footer text-muted">
                    Publié le {{ $article->published_at->format('d/m/Y') }} par {{ $article->author->name }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $articles->links() }}
</div>
@endsection
