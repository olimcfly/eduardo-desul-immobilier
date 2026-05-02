# Guide d'Installation du Module Blog

## Prérequis

- Laravel 10+ ou 11+
- PHP 8.2+
- Base de données MySQL, PostgreSQL ou SQLite
- Composer

## Étapes d'Installation

### 1. Vérifier la Structure du Module

Le module doit être localisé à:
```
app/Modules/ContentTraffic/Blog/
```

Si vous créez manuellement, assurez-vous d'avoir ces dossiers:
```bash
mkdir -p app/Modules/ContentTraffic/Blog/{Config,Database/Migrations,Http/Controllers/{Api,Web},Models,Providers,Resources/{Assets,Views},Routes,Services,Policies,Requests,Exceptions,Enums}
```

### 2. Enregistrer le Service Provider

Dans `config/app.php`, ajoutez le service provider:

```php
'providers' => [
    // ... autres providers
    App\Modules\ContentTraffic\Blog\Providers\BlogServiceProvider::class,
],
```

Ou dans `bootstrap/app.php` (Laravel 11):

```php
->withProviders([
    App\Modules\ContentTraffic\Blog\Providers\BlogServiceProvider::class,
])
```

### 3. Publier la Configuration

Créez le fichier de config `config/blog.php` soit en le copiant manuellement depuis le module, soit avec:

```bash
# Si le provider publie les assets
php artisan vendor:publish --tag=blog-config
```

### 4. Configurer les Variables d'Environnement

Dans `.env`, ajoutez (optionnel - les valeurs par défaut sont fournies):

```env
BLOG_MODULE_ENABLED=true
BLOG_TABLE_PREFIX=blog_
BLOG_TENANT_COLUMN=tenant_id

BLOG_ROUTE_PREFIX=api/blog
BLOG_WEB_PREFIX=blog

BLOG_ARTICLES_PER_PAGE=15
BLOG_EXCERPT_LENGTH=150

BLOG_MEDIA_DISK=public
BLOG_MEDIA_PATH=blog/articles

BLOG_SEO_ENABLED=true
BLOG_CACHE_ENABLED=true
BLOG_CACHE_TTL=3600

BLOG_TAGS_ENABLED=true
BLOG_CATEGORIES_ENABLED=true
BLOG_SHARING_ENABLED=true
BLOG_COMMENTS_ENABLED=false
BLOG_RATINGS_ENABLED=false
```

### 5. Exécuter les Migrations

```bash
# Créer les tables du blog
php artisan migrate

# Spécifier une base de données spécifique
php artisan migrate --database=mysql
```

Cela créera les tables:
- `blog_articles`
- `blog_categories`
- `blog_tags`
- `blog_article_tags`
- `blog_seo_metadata`

### 6. Vérifier les Routes

```bash
# Lister les routes du blog
php artisan route:list | grep blog
```

Vous devriez voir:
```
GET|HEAD  /blog/...
POST|GET  /api/blog/...
```

### 7. Configurer le Disque de Stockage (Optionnel)

Pour activer les uploads de médias, assurez-vous que le disque `public` est configuré dans `config/filesystems.php`:

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'permissions' => [
            'file' => [
                'public' => 0644,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ],
        ],
    ],
],
```

Créez le lien symbolique:
```bash
php artisan storage:link
```

### 8. Créer les Dossiers de Stockage

```bash
# Créer les dossiers pour les uploads
mkdir -p storage/app/public/blog/articles

# Définir les permissions
chmod -R 755 storage/app/public/blog/
chmod -R 755 storage/logs/
```

## Configuration Multi-Tenant

### Utiliser un Préfixe de Table Différent

Pour chaque tenant, vous pouvez utiliser un préfixe différent:

```env
# Tenant 1
BLOG_TABLE_PREFIX=acme_blog_

# Tenant 2
BLOG_TABLE_PREFIX=bestco_blog_
```

Ou dynamiquement dans le code:

```php
config(['blog.table_prefix' => 'tenant_' . $tenantId . '_blog_']);
```

### Middleware Multi-Tenant

Ajoutez un middleware personnalisé `app/Http/Middleware/TenantCheck.php`:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantCheck
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = auth()->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant not found'], 401);
        }

        // Optionnel: Vérifier que le tenant existe
        // $tenant = Tenant::findOrFail($tenantId);
        
        return $next($request);
    }
}
```

Enregistrez-le dans `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'tenant.check' => \App\Http\Middleware\TenantCheck::class,
];
```

## Test du Module

### Test des Routes API

```bash
# Créer un article (avec authentification)
curl -X POST http://localhost:8000/api/blog/articles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Mon Premier Article",
    "slug": "mon-premier-article",
    "content": "Contenu de l'\''article...",
    "excerpt": "Résumé court",
    "category_id": 1,
    "status": "draft"
  }'

# Lister les articles
curl http://localhost:8000/api/blog/articles \
  -H "Authorization: Bearer YOUR_TOKEN"

# Analyser le SEO
curl -X POST http://localhost:8000/api/blog/seo/analyze \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Mon Article",
    "description": "Description du contenu",
    "content": "Contenu avec au moins 300 mots pour une bonne analyse SEO...",
    "keywords": ["article", "test"]
  }'

# Récupérer le sitemap
curl http://localhost:8000/api/blog/seo/sitemap
```

### Test des Routes Web

```bash
# Accéder au dashboard
http://localhost:8000/blog/

# Accéder à la liste des articles
http://localhost:8000/blog/articles

# Créer un article
http://localhost:8000/blog/articles/create
```

## Utilisation des Services

### ArticleService

```php
use App\Modules\ContentTraffic\Blog\Services\ArticleService;

$articleService = app(ArticleService::class);

// Créer
$article = $articleService->create([
    'title' => 'Title',
    'slug' => 'title',
    'content' => 'Content',
    'tenant_id' => auth()->user()->tenant_id,
]);

// Mettre à jour
$articleService->update($article, ['title' => 'New Title']);

// Supprimer
$articleService->delete($article);

// Publier
$articleService->publish($article);

// Dépublier
$articleService->unpublish($article);
```

### SeoAnalyzer

```php
use App\Modules\ContentTraffic\Blog\Services\SeoAnalyzer;

$analyzer = app(SeoAnalyzer::class);

$analysis = $analyzer->analyze($article);
// Retourne: title_score, description_score, content_score, overall_score, recommendations
```

### SitemapGenerator

```php
use App\Modules\ContentTraffic\Blog\Services\SitemapGenerator;

$generator = app(SitemapGenerator::class);

$xml = $generator->generate($tenantId);
// Retourne le XML du sitemap
```

## Dépannage

### Les tables n'ont pas été créées

```bash
# Vérifier que les migrations sont détectées
php artisan migrate:status

# Exécuter avec verbosité
php artisan migrate --verbose

# Réinitialiser et relancer
php artisan migrate:reset
php artisan migrate
```

### Erreur "Class not found"

Vérifiez que le service provider est bien enregistré:

```bash
php artisan tinker
> app('App\Modules\ContentTraffic\Blog\Providers\BlogServiceProvider')
```

### Erreur de middleware

```bash
# Vérifier que le middleware est enregistré
php artisan route:list | grep blog

# S'il manque, l'ajouter à config/blog.php
```

### Fichiers non uploadés

```bash
# Vérifier les permissions
ls -la storage/app/public/blog/

# Définir les permissions
chmod -R 755 storage/app/public/blog/
chown -R www-data:www-data storage/app/public/blog/
```

### Erreur de connexion multi-tenant

```bash
# Vérifier que tenant_id existe en base de données
php artisan tinker
> auth()->user()->tenant_id
```

## Mise à Jour du Module

### Depuis GitHub (si disponible)

```bash
git pull origin main
composer update
php artisan migrate
```

### Manuellement

Copiez les fichiers du module par-dessus vos fichiers existants.

## Désinstallation

```bash
# Supprimer les tables
php artisan migrate:rollback --path=app/Modules/ContentTraffic/Blog/Database/Migrations

# Supprimer le dossier du module
rm -rf app/Modules/ContentTraffic/Blog

# Supprimer la configuration publiée
rm config/blog.php
```

## Support et Contribution

Pour les problèmes, consultez le README.md ou reportez un issue.

## Libreuse

IMMO LOCAL+ CRM - Tous droits réservés
