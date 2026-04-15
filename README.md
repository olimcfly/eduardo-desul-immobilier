# Pascal Hamm Immobilier

Ce dépôt inclut désormais un module SEO avancé prêt à l'emploi dans `admin/seo/` pour IMMO LOCAL+.

- Point d'entrée : `admin/seo/index.php`
- Documentation module : `admin/seo/README.md`
- Script SQL : `admin/seo/sql/seo_module.sql`

## Build des assets CSS/JS (minification + cache busting)

En production, lancez le pipeline suivant pour générer des fichiers minifiés hashés et le manifest d'assets :

```bash
php script/build-assets.php
```

Le script :
- génère des fichiers `*.min.css` et `*.min.js` versionnés par hash dans des dossiers `build/`;
- crée `storage/cache/assets-manifest.json` utilisé par `asset_url()` pour charger automatiquement la version hashée en priorité;
- supprime les anciens assets build devenus orphelins.
