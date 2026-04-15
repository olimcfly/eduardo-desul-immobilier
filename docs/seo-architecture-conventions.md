# SEO — Conventions d'architecture (verrouillage)

> Statut: **référence officielle** pour tout nouveau développement SEO.
> Date: 2026-04-09

## 1) Identifiant principal retenu

- **Identifiant principal SEO: `advisor_id`**.
- Justification:
  - le produit est piloté par conseiller (back-office IMMO LOCAL+),
  - cohérent avec les nouveaux modules SEO (mots-clés/performance),
  - permet de découpler le SEO métier de la seule table `users`.

### Règle de transition
- Tant que le legacy existe, `user_id` peut rester présent.
- Toute nouvelle écriture SEO doit renseigner `advisor_id`.
- Mapping transitoire autorisé: `advisor_id = user_id`.

---

## 2) Tables pivot officielles

Les tables SEO officielles à utiliser sont:

- `seo_city_pages`
- `seo_sitemap_urls`
- `seo_sitemap_logs`
- `seo_keywords`
- `seo_keyword_positions`
- `seo_technical_audits`
- `seo_audit_issues`

### Relations officielles

- `seo_keyword_positions.keyword_id -> seo_keywords.id`
- `seo_audit_issues.audit_id -> seo_technical_audits.id`
- `seo_city_pages` alimente `seo_sitemap_urls` (`source_type='city'`)
- `seo_keywords.target_url` peut pointer vers des URLs issues de pages/articles/fiches villes/secteurs

---

## 3) Structure officielle des modules SEO

Pattern obligatoire pour tout nouveau module SEO:

```
modules/seo/<module>/
  index.php        # écran principal
  edit.php         # CRUD édition (si applicable)
  history.php      # historique/logs (si applicable)
  preview.php      # prévisualisation (si applicable)
  audit.php        # exécution d'action lourde (si applicable)
  api.php          # endpoints JSON du module
```

- Services: `modules/seo/services/Seo<Module>Service.php`
- Le Hub route les modules via `modules/seo/accueil.php?action=...`

---

## 4) Conventions statuts

### 4.1 Contenu publiable
- `draft`
- `ready`
- `published`

### 4.2 Objets de suivi
- `active`
- `paused`
- `archived`

### 4.3 Transition legacy
- valeur legacy `pause` tolérée en lecture, migrée vers `paused` en écriture.

---

## 5) Conventions timestamps

Champs standards:
- `created_at` (obligatoire)
- `updated_at` (obligatoire)

Champs métier:
- `published_at` (publication)
- `last_checked_at` (vérification position/état)
- `audited_at` (audit technique)
- `generated_at` (log de génération)

Règle: ne pas créer de nouveau champ temporel sémantiquement équivalent sans justification.

---

## 6) Conventions API

- Auth obligatoire (`Auth::check()` / `Auth::requireAuth()`)
- CSRF obligatoire (`verifyCsrf()`)
- Réponse JSON homogène:
  - succès: `{ "success": true, "message": "...", "data": ... }`
  - erreur: `{ "success": false, "message": "...", "data": null }`
- Pas de nouvelle logique métier dans `modules/seo/ajax/*`.
- Toute nouvelle API SEO doit être dans `modules/seo/<module>/api.php`.

---

## 7) Couches legacy gelées (ne plus utiliser pour du neuf)

### 7.1 Legacy PHP monolithique
- `modules/seo/mots-cles.php`
- `modules/seo/villes.php`

### 7.2 Legacy ajax
- `modules/seo/ajax/save-keyword.php`
- `modules/seo/ajax/check-position.php`
- `modules/seo/ajax/run-audit.php`
- `modules/seo/ajax/performance-audit.php`
- `modules/seo/ajax/check-keyword.php`

### 7.3 Legacy includes
- `modules/seo/includes/KeywordTracker.php`
- `modules/seo/includes/PerformanceAudit.php`
- `modules/seo/includes/SitemapGenerator.php`
- `modules/seo/includes/SeoService.php`

> Politique: ces fichiers sont **compatibilité uniquement**. Corrections de sécurité/bug uniquement, aucun ajout fonctionnel.

---

## 8) Principe de non-duplication

Interdit:
- créer une table quasi identique à une table pivot SEO existante,
- dupliquer une logique déjà présente dans `services/` vers `includes/` ou `ajax/`,
- introduire un nouveau statut sans alignement global.

Obligatoire:
- réutiliser les services modernes (`services/SeoKeywordPilotService.php`, `services/SeoTechnicalPerformanceService.php`, `services/SitemapGenerator.php`).
