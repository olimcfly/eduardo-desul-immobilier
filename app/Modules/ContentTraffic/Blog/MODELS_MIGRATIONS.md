# Modèles et Migrations - Module Blog

## 📋 Résumé

6 modèles Eloquent + 8 migrations créées pour une architecture blog complète et multi-tenant.

---

## 🗂️ Modèles Eloquent

### 1. **Post.php** - Article/Post
**Champs**:
- `id` (PK)
- `title` - Titre de l'article
- `slug` - URL-friendly (unique)
- `content` - Contenu HTML/Markdown
- `excerpt` - Résumé court
- `featured_image` - Image principale
- `status` - draft | scheduled | published | archived
- `published_at` - Date de publication
- `author_id` (FK users) - Auteur
- `category_id` (FK categories) - Catégorie
- `views_count` - Compteur de vues
- `comment_count` - Compteur de commentaires
- `tenant_id` (UUID) - Multi-tenant
- `created_at`, `updated_at`, `deleted_at` - Timestamps + soft delete

**Relations Eloquent**:
```php
$post->author          // BelongsTo User
$post->category        // BelongsTo Category
$post->tags()          // BelongsToMany Tag (post_tags pivot)
$post->media()         // BelongsToMany Media (post_media pivot)
$post->meta()          // HasMany PostMeta
$post->revisions()     // HasMany Revision
```

**Scopes**:
- `published()` - Posts publiés
- `draft()` - Brouillons
- `scheduled()` - Posts programmés
- `archived()` - Posts archivés
- `byTenant($id)` - Filtre par tenant
- `byAuthor($id)` - Filtre par auteur
- `byCategory($id)` - Filtre par catégorie
- `byTag($id)` - Filtre par tag
- `searchTitle($q)` - Recherche dans titre
- `searchContent($q)` - Recherche dans contenu
- `recent($limit)` - Posts récents
- `mostViewed($limit)` - Plus vus

**Méthodes**:
```php
$post->getMeta($key)              // Récupère une métadonnée
$post->setMeta($key, $value)      // Définit une métadonnée
$post->publish()                  // Publie le post
$post->unpublish()                // Dépublie
$post->archive()                  // Archive
$post->createRevision()           // Crée une révision
$post->restoreRevision($rev)      // Restaure révision
$post->incrementViews()           // Incrémente vues
$post->isPublished()              // Vérification statut
$post->isDraft()
$post->isScheduled()
$post->isArchived()
$post->canPublish()               // Validation pub
$post->reading_time               // Temps de lecture (accesseur)
```

---

### 2. **Category.php** - Catégorie
**Champs**:
- `id` (PK)
- `name` - Nom de la catégorie
- `slug` - URL-friendly (unique)
- `description` - Description
- `parent_id` (FK categories) - Catégorie parent (hiérarchie)
- `display_order` - Ordre d'affichage
- `tenant_id` (UUID) - Multi-tenant
- `created_at`, `updated_at`

**Relations Eloquent**:
```php
$category->parent       // BelongsTo Category (parent)
$category->children()   // HasMany Category (enfants)
$category->posts()      // HasMany Post
```

**Scopes**:
- `byTenant($id)` - Filtre par tenant
- `root()` - Catégories racine
- `ordered()` - Triée par order + name
- `withPostCount()` - Charge le nombre de posts
- `search($q)` - Recherche

**Méthodes**:
```php
$category->getHierarchy()         // Tableau hiérarchie [id => name]
$category->getHierarchyPath()     // String "Parent / Enfant"
$category->hasChildren()          // Boolean
$category->isRoot()               // Boolean
$category->getDescendants()       // Array tous les enfants
$category->getPublishedPostsCount()
```

**Support hiérarchie**: Parents imbriqués à plusieurs niveaux

---

### 3. **Tag.php** - Tag/Étiquette
**Champs**:
- `id` (PK)
- `name` - Nom du tag
- `slug` - URL-friendly (unique)
- `description` - Description
- `tenant_id` (UUID) - Multi-tenant
- `created_at`, `updated_at`

**Relations Eloquent**:
```php
$tag->posts()          // BelongsToMany Post (post_tags pivot)
```

**Scopes**:
- `byTenant($id)` - Filtre par tenant
- `popular($limit)` - Tags populaires
- `search($q)` - Recherche
- `ordered()` - Triée alphabétiquement

**Méthodes**:
```php
$tag->getPostsCount()             // Nombre de posts
$tag->getPublishedPosts()         // Relation Query posts publiés
```

---

### 4. **PostMeta.php** - Métadonnées Post (SEO)
**Champs**:
- `id` (PK)
- `post_id` (FK posts) - Post associé
- `meta_key` - Clé de métadonnée
- `meta_value` - Valeur
- `tenant_id` (UUID) - Multi-tenant

**Relations Eloquent**:
```php
$meta->post            // BelongsTo Post
```

**Scopes**:
- `byKey($key)` - Filtre par clé
- `byPost($id)` - Filtre par post
- `byTenant($id)` - Filtre par tenant

**Clés SEO Prédéfinies**:
```php
'seo_title'          // Titre SEO
'seo_description'    // Description SEO
'seo_keywords'       // Mots-clés
'focus_keyword'      // Mot-clé focus
'og_title'           // Titre Open Graph
'og_description'     // Description OG
'og_image'           // Image OG
'canonical_url'      // URL canonique
'robots'             // Directives robots (index, follow)
'read_more_text'     // Texte "Lire plus"
```

**Méthodes**:
```php
PostMeta::getMetaKeyLabel($key)   // Label humain d'une clé
PostMeta::getAvailableKeys()      // Toutes les clés disponibles
```

---

### 5. **Media.php** - Fichiers/Images
**Champs**:
- `id` (PK)
- `path` - Chemin du fichier
- `filename` - Nom du fichier
- `mime_type` - Type MIME (image/jpeg, etc)
- `alt_text` - Texte alternatif
- `size` - Taille en bytes
- `width` - Largeur (images)
- `height` - Hauteur (images)
- `tenant_id` (UUID) - Multi-tenant
- `created_at`, `updated_at`

**Relations Eloquent**:
```php
$media->posts()        // BelongsToMany Post (post_media pivot)
```

**Scopes**:
- `byTenant($id)` - Filtre par tenant
- `images()` - Uniquement images
- `documents()` - Uniquement documents
- `videos()` - Uniquement vidéos
- `byType($type)` - Filtre par type MIME
- `recent($limit)` - Récents
- `search($q)` - Recherche
- `unused()` - Médias non utilisés

**Accesseurs**:
```php
$media->url                 // URL publique du fichier
$media->is_image            // Boolean
$media->is_video
$media->is_document
$media->filesize_mb         // Taille en MB formatée
$media->type                // image, video, application
$media->extension           // jpg, png, pdf, etc
```

**Méthodes**:
```php
$media->delete()            // Supprime fichier + BD
$media->exists()            // Vérifie existence fichier
$media->getResponsiveUrl($size)   // thumb, small, medium, large
$media->setAltText($text)   // Définit texte alt
$media->getPostsCount()     // Nombre de posts utilisant le média
$media->isUsed()            // Boolean
$media->isDuplicate($other) // Compare MD5
```

**Gestion du stockage**: Utilise le disque configuré dans `config('blog.media.disk')`

---

### 6. **Revision.php** - Révisions/Versions
**Champs**:
- `id` (PK)
- `post_id` (FK posts) - Post associé
- `content` - Contenu sauvegardé
- `created_at` - Timestamp création (pas d'update)
- `tenant_id` (UUID) - Multi-tenant

**Relations Eloquent**:
```php
$revision->post        // BelongsTo Post
```

**Scopes**:
- `byPost($id)` - Filtre par post
- `byTenant($id)` - Filtre par tenant
- `latest()` - Triée par date décroissante
- `recent($limit)` - Dernières révisions
- `olderThan($days)` - Plus anciennes que X jours

**Méthodes**:
```php
$revision->restore()          // Restaure le post avec ce contenu
$revision->diff($other)       // Compare avec autre révision
$revision->getContentLength() // Longueur du contenu
$revision->getWordCount()     // Nombre de mots
$revision->getReadingTime()   // Temps de lecture estimé
$revision->getCharacterCount()
$revision->isOlderThan($days) // Boolean

// Statiques
Revision::cleanOldRevisions($days)         // Supprime anciennes revisions
Revision::getPostRevisionCount($postId)    // Compte revisions
Revision::getLatestRevision($postId)       // Dernière révision
```

---

## 🗄️ Migrations (8 fichiers)

### Migration 1: `create_blog_posts_table`
```sql
posts (
  id, title, slug, content, excerpt, featured_image, 
  category_id, author_id, status, published_at, views_count, 
  comment_count, tenant_id, created_at, updated_at, deleted_at
)
Indexes: slug (UNIQUE), status, published_at, 
         (tenant_id, status, published_at), (tenant_id, author_id)
Soft Delete: YES
```

### Migration 2: `create_blog_categories_table`
```sql
categories (
  id, name, slug, description, parent_id, display_order,
  tenant_id, created_at, updated_at
)
Indexes: slug (UNIQUE), (tenant_id, parent_id), (tenant_id, slug)
Hiérarchie: parent_id auto-référencée
```

### Migration 3: `create_blog_tags_table`
```sql
tags (
  id, name, slug, description, tenant_id, created_at, updated_at
)
Indexes: slug (UNIQUE), (tenant_id, slug), name
```

### Migration 4: `create_blog_post_tags_table` (Pivot)
```sql
post_tags (
  id, post_id, tag_id, created_at, updated_at
)
Indexes: (post_id, tag_id) UNIQUE, tag_id
Relations: FK posts, FK tags (cascade delete)
```

### Migration 5: `create_blog_post_meta_table`
```sql
post_meta (
  id, post_id, meta_key, meta_value, tenant_id
)
Indexes: meta_key, (post_id, meta_key) UNIQUE, (tenant_id, meta_key)
Relations: FK posts (cascade delete)
Storage: longText pour meta_value (JSON, HTML, etc)
```

### Migration 6: `create_blog_media_table`
```sql
media (
  id, path, filename, mime_type, alt_text, size, width, height,
  tenant_id, created_at, updated_at
)
Indexes: filename, (tenant_id, mime_type), created_at
Storage: path (500 chars) pour chemins longs
```

### Migration 7: `create_blog_post_media_table` (Pivot)
```sql
post_media (
  id, post_id, media_id, display_order, created_at, updated_at
)
Indexes: (post_id, media_id) UNIQUE, media_id
Relations: FK posts, FK media (cascade delete)
Ordre: support affichage ordonné des médias
```

### Migration 8: `create_blog_revisions_table`
```sql
revisions (
  id, post_id, content, tenant_id, created_at
)
Indexes: (post_id, created_at), (tenant_id, created_at)
Relations: FK posts (cascade delete)
Timestamps: Création uniquement (pas de update)
```

---

## 🔗 Relations Récapitulatives

```
Post (1:N) Category ← Hiérarchie possible via parent_id
Post (1:1) User (author_id)
Post (M:N) Tag ← via post_tags
Post (M:N) Media ← via post_media
Post (1:N) PostMeta ← SEO, métadonnées
Post (1:N) Revision ← Historique versions

Category (1:N) Category (parent_id) ← Auto-hiérarchie
Tag (M:N) Post ← Inverse de Post:tags
Media (M:N) Post ← Inverse de Post:media
```

---

## 🔐 Multi-Tenant

Chaque table a une colonne `tenant_id` (UUID) sauf `post_tags` et `post_media` qui héritent du post.

**Isolation garantie via**:
- Scopes `byTenant()` sur tous les modèles
- Index sur `tenant_id` pour performance
- Queries auto-filtrées par `auth()->user()->tenant_id`

---

## 📊 Indexes pour Performance

**Slug (UNIQUE)**:
- Posts: `slug` (UNIQUE)
- Categories: `slug` (UNIQUE)
- Tags: `slug` (UNIQUE)

**Temps réel**:
- Posts: `published_at` (pour tri récent)
- Media: `created_at` (pour tri récent)
- Revisions: `created_at` (historique)

**Tenant + Status**:
- Posts: `(tenant_id, status, published_at)` (combiné pour listes)
- Categories: `(tenant_id, parent_id)` (hiérarchie)
- Media: `(tenant_id, mime_type)` (type média)

---

## 🚀 Utilisation

### Créer un Post avec tout
```php
$post = Post::create([
    'title' => 'Mon Article',
    'slug' => 'mon-article',
    'content' => '<p>Contenu HTML</p>',
    'excerpt' => 'Résumé',
    'featured_image' => '/path/image.jpg',
    'category_id' => 1,
    'author_id' => auth()->id(),
    'status' => 'draft',
    'tenant_id' => auth()->user()->tenant_id,
]);

// Ajouter des tags
$post->tags()->attach([1, 2, 3]);

// Ajouter des images
$post->media()->attach([1, 2], ['display_order' => 1]);

// Ajouter métadonnées SEO
$post->setMeta('seo_title', 'Titre SEO 60 chars');
$post->setMeta('seo_description', 'Description 160 chars');
$post->setMeta('seo_keywords', 'mot1, mot2, mot3');

// Publier
$post->publish();
```

### Récupérer et Filtrer
```php
// Posts publiés du tenant
$posts = Post::byTenant()->published()->paginate();

// Posts d'une catégorie
$posts = Post::byCategory(1)->published()->get();

// Posts avec tags
$posts = Post::byTag(5)->published()->latest('published_at')->get();

// Recherche
$posts = Post::searchTitle('laravel')->get();
```

### Gérer Révisions
```php
// Créer révision avant édition
$post->createRevision();

// Modifier
$post->update(['content' => 'Nouveau contenu']);

// Restaurer ancienne version
$post->revisions()->latest()->first()->restore();

// Nettoyer vieilles revisions (>90 jours)
Revision::cleanOldRevisions(90);
```

---

## ✅ Checklist Migration

```bash
# 1. Créer les fichiers (✓ Fait)
# 2. Enregistrer dans BlogServiceProvider
# 3. Exécuter migrations
php artisan migrate

# 4. Vérifier les tables
php artisan db:table blog_posts

# 5. Créer Factories et Seeders (À faire)
# 6. Créer Policies (À faire)
# 7. Créer Form Requests (À faire)
```

---

**Créé**: 24 avril 2026  
**Compatibilité**: Laravel 10+, PHP 8.2+  
**Architecture**: Multi-tenant, Modulaire, Sans hardcoding
