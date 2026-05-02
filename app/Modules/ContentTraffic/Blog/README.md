# Blog Module - IMMO LOCAL+ CRM

Module Blog modulaire et multi-tenant pour IMMO LOCAL+ CRM basé sur Laravel 10+.

## Installation

1. **Enregistrer le service provider** dans `config/app.php`:

```php
'providers' => [
    // ...
    App\Modules\ContentTraffic\Blog\Providers\BlogServiceProvider::class,
],
```

2. **Publier la configuration**:

```bash
php artisan vendor:publish --tag=blog-config
```

3. **Exécuter les migrations**:

```bash
php artisan migrate
```

## Structure du Module

```
Blog/
├── Config/
│   └── blog.php                 # Configuration du module
├── Database/
│   └── Migrations/              # Migrations des tables
├── Http/
│   └── Controllers/
│       ├── Api/                 # Contrôleurs API
│       └── Web/                 # Contrôleurs Web
├── Models/                      # Modèles Eloquent
├── Providers/
│   └── BlogServiceProvider.php  # Service Provider
├── Resources/
│   ├── Assets/                  # CSS, JS, images
│   └── Views/                   # Vues Blade
├── Routes/
│   ├── api.php                  # Routes API
│   └── web.php                  # Routes Web
└── Services/                    # Services métier
```

## Configuration

### Variables d'Environnement

```env
BLOG_MODULE_ENABLED=true
BLOG_TABLE_PREFIX=blog_
BLOG_TENANT_COLUMN=tenant_id

# Routes
BLOG_ROUTE_PREFIX=api/blog
BLOG_WEB_PREFIX=blog

# Articles
BLOG_ARTICLES_PER_PAGE=15
BLOG_EXCERPT_LENGTH=150

# Media
BLOG_MEDIA_ENABLED=true
BLOG_MEDIA_DISK=public
BLOG_MEDIA_PATH=blog/articles
BLOG_MEDIA_MAX_FILE_SIZE=5120

# SEO
BLOG_SEO_ENABLED=true

# Cache
BLOG_CACHE_ENABLED=true
BLOG_CACHE_TTL=3600

# Features
BLOG_COMMENTS_ENABLED=false
BLOG_RATINGS_ENABLED=false
BLOG_SHARING_ENABLED=true
BLOG_TAGS_ENABLED=true
BLOG_CATEGORIES_ENABLED=true
```

## Routes API

### Articles

```
GET     /api/blog/articles              # Lister les articles
POST    /api/blog/articles              # Créer un article
GET     /api/blog/articles/:id          # Détails d'un article
PUT     /api/blog/articles/:id          # Mettre à jour un article
DELETE  /api/blog/articles/:id          # Supprimer un article

GET     /api/blog/articles/:id/related  # Articles connexes
GET     /api/blog/articles/:id/seo-analysis
POST    /api/blog/articles/:id/increment-views

GET     /api/blog/published             # Articles publiés
GET     /api/blog/search?q=query        # Rechercher
```

### Catégories

```
GET     /api/blog/categories            # Lister les catégories
POST    /api/blog/categories            # Créer une catégorie
GET     /api/blog/categories/:id        # Détails d'une catégorie
PUT     /api/blog/categories/:id        # Mettre à jour une catégorie
DELETE  /api/blog/categories/:id        # Supprimer une catégorie

GET     /api/blog/categories/:id/articles
```

### Tags

```
GET     /api/blog/tags                  # Lister les tags
POST    /api/blog/tags                  # Créer un tag
GET     /api/blog/tags/:id              # Détails d'un tag
PUT     /api/blog/tags/:id              # Mettre à jour un tag
DELETE  /api/blog/tags/:id              # Supprimer un tag

GET     /api/blog/tags/:id/articles
```

### SEO

```
POST    /api/blog/seo/analyze           # Analyser le contenu
GET     /api/blog/seo/sitemap           # Sitemap XML
GET     /api/blog/seo/robots            # Robots.txt
```

## Routes Web

```
GET     /blog/                          # Dashboard
GET     /blog/articles                  # Liste des articles
GET     /blog/articles/create           # Créer un article
POST    /blog/articles                  # Enregistrer un article
GET     /blog/articles/:id              # Détails d'un article
GET     /blog/articles/:id/edit         # Éditer un article
PUT     /blog/articles/:id              # Mettre à jour un article
DELETE  /blog/articles/:id              # Supprimer un article
POST    /blog/articles/:id/publish      # Publier un article
POST    /blog/articles/:id/unpublish    # Dépublier un article

GET     /blog/articles/:slug            # Article public
```

## Utilisation

### Créer un Article

```php
use App\Modules\ContentTraffic\Blog\Services\ArticleService;

$service = app(ArticleService::class);

$article = $service->create([
    'title' => 'Mon Article',
    'slug' => 'mon-article',
    'content' => 'Contenu de l\'article',
    'excerpt' => 'Résumé',
    'category_id' => 1,
    'status' => 'draft',
    'tenant_id' => auth()->user()->tenant_id,
    'tags' => [1, 2, 3],
    'seo' => [
        'meta_title' => 'Titre SEO',
        'meta_description' => 'Description SEO',
        'meta_keywords' => 'mots-clés',
    ],
]);
```

### Analyser le SEO

```php
use App\Modules\ContentTraffic\Blog\Services\SeoAnalyzer;

$analyzer = app(SeoAnalyzer::class);

$analysis = $analyzer->analyze($article);
// Retourne: title_score, description_score, content_score, overall_score, recommendations
```

### Générer le Sitemap

```php
use App\Modules\ContentTraffic\Blog\Services\SitemapGenerator;

$generator = app(SitemapGenerator::class);

$xml = $generator->generate(auth()->user()->tenant_id);
```

## Multi-Tenant

Le module supporte complètement le multi-tenant:

- Préfixe de tables configurable
- Isolation des données par tenant via `tenant_id`
- Scopes automatiques dans les modèles
- Middleware de vérification du tenant

### Utiliser les Scopes

```php
use App\Modules\ContentTraffic\Blog\Models\Article;

// Récupérer les articles du tenant courant
$articles = Article::byTenant(auth()->user()->tenant_id)->get();

// Scope published
$published = Article::published()->get();

// Combiner les scopes
$articles = Article::byTenant(auth()->user()->tenant_id)
    ->published()
    ->get();
```

## Caching

Le module implémente le cache pour améliorer les performances:

```php
// Clés de cache
config('blog.cache.key_prefix') . 'articles'
config('blog.cache.key_prefix') . 'article:' . $id
```

Le cache est automatiquement invalidé lors de la création/modification/suppression.

## Sécurité

- Authorization policies intégrées
- Validation des fichiers uploadés
- Protection CSRF sur les formulaires
- Pagination automatique
- Sanitization des slugs

## Points d'Extension

### Service Provider Personnalisé

```php
namespace App\Modules\ContentTraffic\Blog\Providers;

use App\Modules\ContentTraffic\Blog\Services\ArticleService;

class YourServiceProvider extends BlogServiceProvider
{
    public function register(): void
    {
        parent::register();
        
        $this->app->bind(ArticleService::class, CustomArticleService::class);
    }
}
```

### Événements

```php
// À implémenter selon vos besoins
// ArticleCreated, ArticleUpdated, ArticleDeleted
// etc.
```

## Dépannage

### Les migrations ne trouvent pas le préfixe

Assurez-vous que `config('blog.table_prefix')` est défini avant la migration:

```php
// Dans bootstrap/app.php ou service provider
config(['blog.table_prefix' => env('BLOG_TABLE_PREFIX', 'blog_')]);
```

### Erreur de tenant non trouvé

Vérifiez que le middleware `tenant.check` est enregistré et fonctionne correctement.

### Fichiers non uploadés

Vérifiez la configuration du disque et les permissions du dossier:

```bash
chmod -R 755 storage/app/public/blog/
php artisan storage:link
```

## License

IMMO LOCAL+ CRM - Tous droits réservés
