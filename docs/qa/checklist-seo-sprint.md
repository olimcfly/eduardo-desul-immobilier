# Check-list QA manuelle — Sprint Qualité SEO (1 jour)

## Pré-requis
- Être connecté avec un compte admin valide.
- Avoir des contenus de test dans `pages`, `articles` et `secteurs`.
- Avoir au moins un contenu avec métadonnées SEO manquantes.

## 1) Sanity API SEO
- [ ] `GET /admin/api/seo/seo-api.php?action=stats` retourne `success=true`.
- [ ] `GET /admin/api/seo/seo-api.php?action=list&type=page` retourne `items`, `total`, `page`, `limit`, `pages`.
- [ ] `GET /admin/api/seo/seo-api.php?action=list&type=article` idem.
- [ ] `GET /admin/api/seo/seo-api.php?action=list&type=secteur` idem.
- [ ] `GET /admin/api/seo/seo-api.php?action=get&type=page&id={id}` retourne le détail du contenu.

## 2) Filtres et tri
- [ ] `filter=no_meta` isole bien les contenus sans `meta_title`.
- [ ] `filter=good` retourne des scores SEO >= 70.
- [ ] `filter=issues` retourne des scores SEO < 50.
- [ ] `sort=updated_desc`, `sort=title_asc`, `sort=score_desc` changent bien l'ordre des résultats.

## 3) Pagination (gros volumes)
- [ ] Sur un dataset volumineux, `limit=25` + `page=1/2/3` renvoient des lots différents sans duplication.
- [ ] `total` reste stable entre les pages (hors modifications concurrentes).
- [ ] `pages` est cohérent avec `ceil(total/limit)`.
- [ ] Le temps de réponse de `type=page` reste acceptable (< 1s cible locale sur dataset volumineux).

## 4) Édition SEO
- [ ] `POST action=save` met à jour `meta_title`, `meta_description`, `seo_score`.
- [ ] `POST action=bulk-save` met à jour plusieurs lignes et retourne `updated`.
- [ ] `POST action=analyze` recalcule score + `checks`.

## 5) Non-régression
- [ ] Routes non authentifiées retournent 401 + JSON valide.
- [ ] Les écrans admin consommant `action=list` affichent correctement la pagination.
- [ ] Aucun warning PHP dans les logs pendant les appels API.

## Sortie QA (à compléter)
- Date:
- Environnement:
- Testeur:
- Résultat global: ✅ / ⚠️ / ❌
- Blocages:
