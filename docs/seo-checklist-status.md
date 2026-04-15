# Checklist SEO — État d'implémentation

Date d'audit : 2026-04-07

## ✅ Implémenté dans le code

- Redirection vers version préférée (HTTPS + non-www) via `.htaccess`.
- Certificat SSL attendu (pré-requis infra, redirection HTTPS activée).
- `robots.txt` présent.
- `sitemap.xml` présent.
- `sitemap.xml` inclus dans `robots.txt`.
- Sitemap HTML créé (`/plan-du-site`).
- Lien vers sitemap HTML ajouté dans le footer.
- Pages About (`/a-propos`) et Contact (`/contact`) présentes.
- Favicon installé.
- Pages Terms of Service (CGV) et Privacy Policy présentes.
- Meta keywords retirés.
- Règles noindex/nofollow pour pages de merci et patterns techniques.
- Exemples de règles 301/410 ajoutées dans `.htaccess`.
- Google Analytics prévu via setting `google_analytics_id`.
- OG data globale maintenue dans le layout.

## ⚠️ À vérifier côté outils externes / infra

- SSL certificate effectivement installé côté hébergement.
- Vitesse page < 2s (mesure Lighthouse / Core Web Vitals).
- Sitemap soumis à Search Console.
- Site soumis à Search Console.
- Erreurs de sitemap Search Console.
- Pénalités manuelles.
- Indexation et couverture globale.
- Goals / conversions Analytics + tracking offline.
- Schémas avancés (blog/news/event/open house) selon types de contenus réellement publiés.
- Conflits de mots-clés (cannibalisation).
- 404 avec trafic à rediriger en 301 (nécessite logs/GA/GSC).
- 404 sans trafic à retourner en 410 (nécessite logs).
- Détection fine du contenu mince.

## ℹ️ Recommandations opérationnelles

1. Connecter Search Console et soumettre `https://pascalhamm.fr/sitemap.xml`.
2. Activer l'ID GA4 via `google_analytics_id` dans les settings.
3. Mettre en place un rapport mensuel 404 + redirections.
4. Ajouter des schémas supplémentaires uniquement sur les templates concernés (article, événement, etc.).
