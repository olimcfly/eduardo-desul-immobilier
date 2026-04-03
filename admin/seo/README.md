# Module SEO avancé — IMMO LOCAL+

## Installation
1. Copier le dossier `admin/seo` dans votre projet.
2. Exécuter le script SQL `admin/seo/sql/seo_module.sql`.
3. Vérifier les variables DB dans `.env` (utilisées via `config/database.php`).
4. Ouvrir `admin/seo/index.php?action=dashboard`.

## Structure
- `index.php` : routeur principal avec `action` (`dashboard`, `editor`, `keywords`, `serp`, `silo`).
- `init.php` : bootstrap PDO, CSRF, helpers d'échappement.
- `services/BlogService.php` : pagination, filtres dashboard, stats globales.
- `services/SeoService.php` : analyses SEO, grille mots-clés, SERP, silo.
- `views/*.php` : 5 vues interconnectées + layout partagé.
- `css/*` et `js/seo-dashboard.js` : design system, interactions front.

## Fonctionnalités clés
- Sécurité: statements préparés PDO, échappement `htmlspecialchars`, token CSRF.
- Dashboard: filtres multi-critères, tableaux, badges, graphiques miniatures.
- Éditeur: toolbar riche, score SEO en temps réel, aperçu SERP.
- Mots-clés: filtres volume/concurrence/intention, opportunités Golden Ratio.
- SERP: simulation top 10 concurrentielle, recommandations d'optimisation.
- Silo: vue pilier/satellites, complétion, opportunités éditoriales.

## Exemples d'usage
```php
$dashboard = $blogService->getDashboardData(['status' => 'published'], 1, 20);
$analysis = $seoService->analyzeArticleContent('<h2>Marché</h2><p>...</p>');
$opps = $seoService->getKeywordOpportunities();
```

## Données de test
- CSV exemple: `admin/seo/data/sample_keywords.csv`
