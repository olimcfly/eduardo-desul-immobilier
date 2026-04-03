# Audit blog (02/04/2026)

## Synthèse

Le rendu blog est **globalement très avancé côté front** (listing + article + UX), mais l’architecture demandée dans l’audit initial n’est pas strictement respectée (fichiers/noms et séparation CSS/JS).

## Vérification point par point

### 1) `Blog.php` (classe métier)

- **Statut : ❌ Non conforme au périmètre demandé.**
- Constat : le dépôt contient `includes/classes/Article.php` (avec `getPublished`, `getBySlug`, `countPublished`), mais pas de fichier `Blog.php` dédié.
- Constat : les fonctionnalités « similaires », « catégories avec comptage » et filtres/pagination sont principalement codées directement dans les renderers (`front/renderers/blog-listing.php`, `front/renderers/article.php`) et/ou dans `BlogRenderer`.

### 2) `blog.php` (page liste)

- **Statut : ✅ Fonctionnellement présent (via `front/renderers/blog-listing.php`).**
- Présent : filtres catégories sticky, featured article, grille, pagination, CTA newsletter.
- Présent : recherche newsletter async (soumission Ajax vers endpoint capture).
- Écart : la page n’est pas un fichier nommé `blog.php` unique ; logique et styles sont inline dans le renderer.

### 3) `article.php` (page article)

- **Statut : ✅ Fonctionnellement présent (via `front/renderers/article.php`).**
- Présent : hero, image, méta, contenu HTML riche, sidebar sticky visuelle/structurelle, TOC, share bar Facebook/LinkedIn/WhatsApp, author box, related articles.
- Écart : bouton « copy link » non présent dans la share bar.

### 4) Article pilier (contenu CMS)

- **Statut : ⚠️ Non vérifiable dans ce dépôt sans données DB.**
- Le code de rendu est prêt pour afficher un article long structuré, mais l’existence réelle d’un article pilier avec les 7 sections, checklist, tableau comparatif et CTA contextuels dépend des données en base.

### 5) `blog.css`

- **Statut : ❌ Non conforme au périmètre demandé.**
- Constat : pas de fichier `blog.css` identifié ; les styles blog/article sont majoritairement inline dans les renderers.

### 6) `blog.js`

- **Statut : ❌ Non conforme au périmètre demandé.**
- Constat : pas de fichier `blog.js` identifié ; interactions JS embarquées inline (TOC, smooth scroll, newsletter async).
- Écart : pas d’`IntersectionObserver` pour highlight actif du TOC ; pas de progress bar de lecture dédiée.

## Conclusion

- **Produit actuel :** très bon niveau d’UI/UX et de fonctionnalités blog en rendu direct.
- **Écarts principaux vs cahier des charges fourni :**
  1. Absence d’une vraie classe `Blog.php` centralisant la logique métier.
  2. Absence de séparation propre `blog.css` / `blog.js`.
  3. Quelques micro-features manquantes (copy link, TOC active highlight, progress bar lecture).

## Recommandation immédiate

1. Créer une classe `Blog` dédiée (ou étendre `Article`) pour centraliser :
   - `getPublished(filters, pagination)`
   - `getBySlug(withViewIncrement=true)`
   - `getRelated(slug|id, limit)`
   - `getCategories(withCount=true)`
   - `countPublished(filters)`
2. Extraire les styles inline vers `front/assets/css/blog.css`.
3. Extraire le JS inline vers `front/assets/js/blog.js` avec :
   - progress bar,
   - TOC active via `IntersectionObserver`,
   - copy link.
