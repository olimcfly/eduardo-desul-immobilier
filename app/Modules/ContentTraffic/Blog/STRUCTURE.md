# Structure Complète du Module Blog

## Arborescence

```
app/Modules/ContentTraffic/Blog/
│
├── Config/
│   └── blog.php                          # Configuration centralisée du module
│
├── Database/
│   └── Migrations/
│       ├── 2024_04_24_000001_create_blog_articles_table.php
│       ├── 2024_04_24_000002_create_blog_categories_table.php
│       ├── 2024_04_24_000003_create_blog_tags_table.php
│       ├── 2024_04_24_000004_create_blog_article_tags_table.php
│       └── 2024_04_24_000005_create_blog_seo_metadata_table.php
│
├── Http/
│   └── Controllers/
│       ├── Api/
│       │   ├── ArticleController.php     # CRUD API articles
│       │   ├── CategoryController.php    # CRUD API catégories
│       │   ├── TagController.php         # CRUD API tags
│       │   └── SeoController.php         # Analyse SEO, sitemap
│       │
│       └── Web/
│           ├── BlogController.php        # Dashboard
│           ├── ArticleController.php     # Gestion articles (admin)
│           └── CategoryController.php    # Gestion catégories (admin)
│
├── Models/
│   ├── Article.php                       # Modèle Article avec relations
│   ├── Category.php                      # Modèle Category
│   ├── Tag.php                           # Modèle Tag
│   └── SeoMetadata.php                   # Modèle SeoMetadata
│
├── Providers/
│   └── BlogServiceProvider.php            # Enregistrement du module
│
├── Resources/
│   ├── Assets/
│   │   ├── css/
│   │   │   └── blog.css                  # Styles du blog
│   │   ├── js/
│   │   │   └── blog.js                   # Scripts du blog
│   │   └── images/                       # Images par défaut
│   │
│   └── Views/
│       ├── dashboard.blade.php           # Dashboard
│       ├── articles/
│       │   ├── index.blade.php           # Liste des articles
│       │   ├── create.blade.php          # Formulaire création
│       │   ├── edit.blade.php            # Formulaire édition
│       │   ├── show.blade.php            # Détails (admin)
│       │   └── public.blade.php          # Affichage public
│       ├── categories/
│       │   ├── index.blade.php           # Liste des catégories
│       │   ├── create.blade.php          # Créer catégorie
│       │   ├── edit.blade.php            # Éditer catégorie
│       │   └── public.blade.php          # Catégorie publique
│       ├── tags/
│       │   └── public.blade.php          # Tag publique
│       └── components/
│           ├── article-form.blade.php    # Formulaire article réutilisable
│           ├── article-card.blade.php    # Carte article
│           └── pagination.blade.php      # Pagination
│
├── Routes/
│   ├── api.php                           # Routes API RESTful
│   └── web.php                           # Routes Web (admin + public)
│
├── Services/
│   ├── ArticleService.php                # Logique métier articles
│   ├── MediaUploader.php                 # Gestion des uploads
│   ├── SeoAnalyzer.php                   # Analyse SEO
│   └── SitemapGenerator.php              # Génération sitemap
│
├── Policies/
│   ├── ArticlePolicy.php                 # Authorization articles
│   └── CategoryPolicy.php                # Authorization catégories
│
├── Requests/
│   ├── StoreArticleRequest.php           # Validation création article
│   ├── UpdateArticleRequest.php          # Validation édition article
│   ├── StoreCategoryRequest.php          # Validation catégorie
│   └── StoreTagRequest.php               # Validation tag
│
├── Exceptions/
│   ├── ArticleNotFoundException.php
│   └── InvalidMediaException.php
│
├── Enums/
│   └── ArticleStatus.php                 # États possibles d'un article
│
├── README.md                             # Documentation du module
├── STRUCTURE.md                          # Ce fichier
└── composer.json                         # Dépendances du module (optionnel)
```

## Détail des Composants

### 1. **Models** (4 modèles)

#### Article.php
- Relations: `belongsTo(Category)`, `belongsToMany(Tag)`, `hasMany(SeoMetadata)`
- Scopes: `published()`, `byTenant()`
- Attributes: title, slug, content, excerpt, featured_image, category_id, status, published_at, views_count, tenant_id

#### Category.php
- Relations: `hasMany(Article)`
- Scopes: `byTenant()`
- Attributes: name, slug, description, tenant_id

#### Tag.php
- Relations: `belongsToMany(Article)`
- Scopes: `byTenant()`
- Attributes: name, slug, tenant_id

#### SeoMetadata.php
- Relations: `belongsTo(Article)`
- Attributes: meta_title, meta_description, meta_keywords, og_title, og_description, og_image, canonical_url, tenant_id

### 2. **Controllers API** (4 contrôleurs)

#### ArticleController.php
- `index()`, `show()`, `store()`, `update()`, `destroy()`
- `related()`, `seoAnalysis()`, `incrementViews()`, `search()`, `published()`

#### CategoryController.php
- `index()`, `store()`, `show()`, `update()`, `destroy()`
- `articles()` - articles d'une catégorie

#### TagController.php
- `index()`, `store()`, `show()`, `update()`, `destroy()`
- `articles()` - articles avec un tag

#### SeoController.php
- `analyze()` - analyse SEO d'un contenu
- `sitemap()` - génère sitemap XML
- `robots()` - génère robots.txt

### 3. **Controllers Web** (3 contrôleurs)

#### BlogController.php
- `dashboard()` - statistiques et articles récents

#### ArticleController.php (Web)
- `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- `publish()`, `unpublish()`, `showPublic()`

#### CategoryController.php (Web)
- `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
- `showPublic()` - affiche articles d'une catégorie

### 4. **Services** (4 services)

#### ArticleService.php
- `create()` - crée article avec média et tags
- `update()` - met à jour article
- `delete()` - supprime article + média
- `publish()`, `unpublish()`
- Gère cache et upload média

#### MediaUploader.php
- `upload()` - upload fichier
- `delete()` - supprime fichier
- `isAllowed()` - valide extension

#### SeoAnalyzer.php
- `analyze()` - analyse article
- `analyzeContent()` - analyse contenu brut
- Calcule scores et recommandations
- Valide longueurs titre/description

#### SitemapGenerator.php
- `generate()` - génère sitemap XML
- Inclut articles et catégories
- Multi-tenant

### 5. **Routes**

#### api.php
```
/api/blog/articles      - CRUD articles + related, seo-analysis, increment-views, search, published
/api/blog/categories    - CRUD + articles
/api/blog/tags          - CRUD + articles
/api/blog/seo/*         - analyze, sitemap, robots
```

#### web.php
```
/blog/                           - dashboard
/blog/articles                   - index, create, store, show, edit, update, destroy, publish, unpublish
/blog/categories                 - CRUD
/blog/articles/:slug             - public view
/blog/categories/:slug           - public view
```

## Configuration

### blog.php
- `enabled` - active/désactive le module
- `table_prefix` - préfixe multi-tenant
- `tenant_column` - colonne tenant
- `route_*` - préfixes et middleware des routes
- `article.*` - pagination, longueur excerpt, dimensions images
- `seo.*` - longueurs min/max titre, description, keywords
- `media.*` - disque, chemin, extensions autorisées, taille max
- `cache.*` - activation, TTL, préfixe clés
- `features.*` - activation commentaires, ratings, sharing, tags, catégories

## Migration Multi-Tenant

Les migrations utilisent `config('blog.table_prefix')` pour créer les tables dynamiquement:
- `blog_articles`
- `blog_categories`
- `blog_tags`
- `blog_article_tags`
- `blog_seo_metadata`

Colonne `tenant_id` (UUID) sur chaque table.

## Statut Article

Trois états:
- `draft` - brouillon non publié
- `published` - publié avec `published_at`
- `archived` - archivé/supprimé logiquement

## Caching

Clés:
- `blog:articles` - liste articles
- `blog:article:id` - article spécifique

Invalidation automatique sur create/update/delete.

## Sécurité

- Authorization policies (ArticlePolicy, CategoryPolicy)
- Validation (StoreArticleRequest, UpdateArticleRequest)
- Slug validation unique
- Multi-tenant isolation
- File upload validation (extension, taille)
- CSRF protection

## Points d'Extension

1. **Événements** - Émettre ArticleCreated, ArticleUpdated, ArticleDeleted
2. **Observers** - Observer modèles pour hooks supplémentaires
3. **Custom Services** - Remplacer services dans service provider
4. **Policies** - Ajouter policies personnalisées
5. **Middleware** - Ajouter middleware personnalisé

## Dépendances

- Laravel 10+
- PHP 8.2+

Optionnel:
- Laravel Livewire (pour interfaces réactives)
- Laravel Sanctum (pour API auth)
- Spatie Permission (pour rôles/permissions)
