@extends('layouts.app')

@section('title', 'Blog Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3">Blog Dashboard</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Articles</h6>
                    <p class="h4">{{ $stats['total_articles'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Published</h6>
                    <p class="h4">{{ $stats['published_articles'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Drafts</h6>
                    <p class="h4">{{ $stats['draft_articles'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Views</h6>
                    <p class="h4">{{ $stats['total_views'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Articles -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Articles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_articles as $article)
                                    <tr>
                                        <td>{{ $article->title }}</td>
                                        <td>
                                            <span class="badge bg-{{ $article->status === 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($article->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $article->views_count }}</td>
                                        <td>{{ $article->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('blog.articles.edit', $article) }}" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No articles yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body">
                    @forelse($categories as $category)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $category->name }}</span>
                            <small class="text-muted">{{ $category->articles_count }} articles</small>
                        </div>
                    @empty
                        <p class="text-muted">No categories</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('blog.articles.create') }}" class="btn btn-primary">New Article</a>
            <a href="{{ route('blog.articles.index') }}" class="btn btn-secondary">Manage Articles</a>
            <a href="{{ route('blog.categories.index') }}" class="btn btn-secondary">Manage Categories</a>
        </div>
    </div>
</div>
@endsection
