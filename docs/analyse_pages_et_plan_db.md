# Analyse des pages et recommandation base de données

## Ce que j'ai vérifié
- Pages front publiques (`/contact`, `/estimation-gratuite`, `/guide-offert`, `/ressources/guides/...`, `/biens`, `/blog`, `/actualites`, `/guide-local`).
- Services backend qui écrivent réellement en base (`LeadService`, route capture des guides).
- Migrations et schémas SQL existants (`database/migrations/*.sql`, `modules/*/sql/*.sql`, `admin/seo/sql/seo_module.sql`).

## Résultat
Le projet est pensé pour **une base unique** (multi-modules) et non pour plusieurs bases séparées.

### Pourquoi une seule DB est préférable ici
1. Les modules partagent la table `users` (FK partout : settings, SEO, social, GMB, API credits, etc.).
2. Les modules se croisent fonctionnellement (`social_posts.bien_id` vers `biens.id`, dashboard global, etc.).
3. La maintenance est plus simple (un dump, des migrations centralisées, un seul `.env` DB).

## Quand créer plusieurs DB ?
Seulement si vous avez un besoin explicite de séparation forte :
- contraintes légales / isolation client (multi-tenant strict),
- charge énorme par module,
- équipes complètement séparées avec cycles indépendants.

Dans l'état actuel du code, cela ajouterait de la complexité sans gain immédiat.

## Livrable
Le script `sql/schema_complet_plateforme.sql` créé un schéma consolidé pour tout le projet.
