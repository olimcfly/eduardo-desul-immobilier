<?php

namespace App\Modules\ContentTraffic\Blog\Http\Controllers\Web;

use App\Modules\ContentTraffic\Blog\Models\Article;
use App\Modules\ContentTraffic\Blog\Models\Category;
use Illuminate\View\View;

class BlogController
{
    public function dashboard(): View
    {
        $tenantId = auth()->user()?->tenant_id;

        $stats = [
            'total_articles' => Article::byTenant($tenantId)->count(),
            'published_articles' => Article::byTenant($tenantId)->published()->count(),
            'draft_articles' => Article::byTenant($tenantId)->where('status', 'draft')->count(),
            'total_categories' => Category::byTenant($tenantId)->count(),
            'total_views' => Article::byTenant($tenantId)->sum('views_count'),
        ];

        $recent_articles = Article::byTenant($tenantId)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $categories = Category::byTenant($tenantId)->get();

        return view('blog::dashboard', compact('stats', 'recent_articles', 'categories'));
    }
}
