# Module « Studio Visuel IA » — Audit existant + plan technique MVP/V2

## 0) Vérification rapide : le module existe-t-il déjà ?

Oui, **partiellement**.

Le projet contient déjà un module `image-editor` côté admin (`/admin/modules/social/image-editor`) avec :
- une interface de génération,
- une prévisualisation via `iframe`,
- une sauvegarde en base,
- une mini-bibliothèque,
- un endpoint API dédié (`/admin/api/social/image-editor.php`).

En revanche, il ne couvre pas encore le besoin produit complet « Studio Visuel IA » demandé (moteur IA réel, statuts de cycle de vie, multi-modes de création complets, modèles métiers riches, liaison multi-objets, architecture services/contrôleurs dédiés par tenant, etc.).

---

## 1) Plan technique détaillé (modular monolith)

## 1.1 Découpage modulaire proposé

Créer un module `visual-studio` (social/marketing) structuré ainsi :

- `app/Domain/VisualStudio/`
  - `Enums/` (`TargetPlatform`, `TargetFormat`, `VisualGoal`, `VisualStatus`, `EngineType`)
  - `DTO/` (`CreateVisualCommand`, `GenerateVisualCommand`, `LinkVisualCommand`)
  - `Policies/` (règles tenant + permissions)

- `app/Services/VisualStudio/`
  - `VisualStudioService` (orchestration métier)
  - `VisualGenerationService` (choix moteur et rendu)
  - `TemplateEngineService` (HTML5/canvas)
  - `AiImageEngineService` (moteur image IA)
  - `HybridCompositionService` (fond IA + overlay HTML5)
  - `VisualLinkService` (liaison article/page/post/GMB)
  - `VisualLibraryService` (save/use/archive/download/duplicate)
  - `VisualPreviewService` (preview live + variations format)

- `app/Repositories/VisualStudio/`
  - `VisualAssetRepository`
  - `VisualTemplateRepository`
  - `VisualLinkRepository`
  - `VisualGenerationJobRepository`

- `app/Controllers/`
  - `VisualStudioController` (écran principal)
  - `VisualStudioApiController` (actions JSON)
  - `VisualStudioLinkController` (actions contextuelles depuis article/page/post)

- `admin/modules/social/visual-studio/`
  - `index.php` (wizard)
  - `editor.php` (édition)
  - `library.php` (bibliothèque)
  - `partials/` (composants réutilisables)

## 1.2 Workflow métier cible

1. **Entrée** via 3 modes :
   - libre (prompt texte),
   - depuis contenu existant,
   - depuis modèle système.
2. **Paramétrage obligatoire** : plateforme, format, objectif, style, texte image (oui/non), CTA (oui/non).
3. **Génération** par moteur : template HTML5/canvas, IA image, ou hybride.
4. **Preview** multi-formats.
5. **Édition** (texte, style, overlay, CTA, dimensions).
6. **Sorties** : save bibliothèque / utiliser immédiatement / télécharger / dupliquer / archiver.
7. **Statuts** : `draft -> generated -> editing -> validated -> saved -> used -> archived`.

## 1.3 Principes d’architecture

- Contrôleurs fins = validation/sérialisation + appel service.
- Services métiers = règles de génération, transitions statut, liaison contenu.
- Données isolées par tenant (`tenant_id` obligatoire + index composite).
- Événements internes pour extensibilité (`VisualGenerated`, `VisualLinked`, `VisualUsed`).
- Moteurs branchables via interface (`VisualEngineInterface`).

---

## 2) Schéma de base de données (proposition)

## 2.1 `visual_assets`

- `id` (PK)
- `tenant_id` (BIGINT, index)
- `mode` ENUM(`free`,`from_content`,`from_template`)
- `engine` ENUM(`template_html5`,`ai_image`,`hybrid`)
- `status` ENUM(`draft`,`generated`,`editing`,`validated`,`saved`,`used`,`archived`)
- `target_platform` ENUM(`gmb`,`facebook`,`instagram`,`instagram_story`,`article_cover`,`banner`,`local_real_estate`)
- `target_format` VARCHAR(40) (ex: `1200x630`, `1080x1920`)
- `goal` ENUM(`engagement`,`credibility`,`conversion`,`information`)
- `style` VARCHAR(120)
- `has_text_overlay` TINYINT(1)
- `has_cta` TINYINT(1)
- `title` VARCHAR(255)
- `source_text` LONGTEXT
- `render_payload` JSON (prompt final, blocks, layer config)
- `render_html` LONGTEXT NULL
- `image_url` VARCHAR(500) NULL
- `thumb_url` VARCHAR(500) NULL
- `created_by` BIGINT
- `created_at`, `updated_at`, `archived_at`

Index recommandé :
- `(tenant_id, status, created_at)`
- `(tenant_id, target_platform, target_format)`

## 2.2 `visual_system_templates`

- `id` (PK)
- `tenant_id` NULL (NULL = système global)
- `slug` unique (`question-magique-facebook`, etc.)
- `name`
- `category`
- `default_platform`
- `default_format`
- `default_goal`
- `default_style`
- `template_payload` JSON (zones textuelles, structure)
- `is_active`
- timestamps

## 2.3 `visual_links`

- `id` (PK)
- `tenant_id` (index)
- `visual_asset_id` (FK)
- `entity_type` ENUM(`article`,`page`,`social_post`,`gmb_post`)
- `entity_id`
- `usage_context` ENUM(`cover`,`inline`,`thumbnail`,`story`,`post_media`)
- `is_primary`
- timestamps

Index recommandé :
- `(tenant_id, entity_type, entity_id)`
- `(tenant_id, visual_asset_id)`

## 2.4 `visual_generation_jobs` (optionnel MVP+, utile si async)

- `id`, `tenant_id`, `visual_asset_id`
- `engine`, `job_status`, `job_payload`, `result_payload`, `error_message`
- timestamps

---

## 3) Routes (proposition)

## 3.1 Admin pages

- `GET /admin/dashboard.php?page=visual-studio` (wizard + listing)
- `GET /admin/dashboard.php?page=visual-studio-editor&id={id}`
- `GET /admin/dashboard.php?page=visual-studio-library`

## 3.2 API JSON

- `POST /admin/api/social/visual-studio.php?action=create`
- `POST /admin/api/social/visual-studio.php?action=generate`
- `POST /admin/api/social/visual-studio.php?action=preview`
- `POST /admin/api/social/visual-studio.php?action=update`
- `POST /admin/api/social/visual-studio.php?action=change_status`
- `POST /admin/api/social/visual-studio.php?action=link_entity`
- `POST /admin/api/social/visual-studio.php?action=unlink_entity`
- `POST /admin/api/social/visual-studio.php?action=duplicate`
- `POST /admin/api/social/visual-studio.php?action=archive`
- `GET  /admin/api/social/visual-studio.php?action=list`
- `GET  /admin/api/social/visual-studio.php?action=get&id={id}`
- `GET  /admin/api/social/visual-studio.php?action=system_templates`
- `GET  /admin/api/social/visual-studio.php?action=content_candidates&type=article|page|social_post|gmb_post`

## 3.3 Actions contextuelles depuis modules existants

- `GET /admin/dashboard.php?page=articles-edit&id={id}&action=create-visual`
- `GET /admin/dashboard.php?page=pages-edit&id={id}&action=create-visual`
- `GET /admin/dashboard.php?page=facebook&action=create-visual&post_id={id}`
- `GET /admin/dashboard.php?page=gmb&action=create-visual&post_id={id}`

---

## 4) Contrôleurs (responsabilités)

- `VisualStudioController`
  - rend le wizard,
  - injecte listes plateformes/formats/objectifs/styles,
  - récupère templates système.

- `VisualStudioApiController`
  - valide payload,
  - applique contrôle tenant,
  - délègue aux services,
  - renvoie DTO JSON.

- `VisualStudioLinkController`
  - point d’entrée “Créer un visuel” depuis article/page/post,
  - préremplit automatiquement : titre, résumé, plateforme recommandée, format.

---

## 5) Services (logique métier)

- `VisualStudioService`
  - création d’un draft,
  - gestion des transitions statut autorisées,
  - centralisation des règles métier.

- `VisualGenerationService`
  - choisit moteur selon mode + besoin,
  - lance génération,
  - persiste `render_payload` + sorties.

- `TemplateEngineService`
  - prend un template système,
  - injecte variables,
  - génère HTML5/canvas éditable.

- `AiImageEngineService`
  - construit prompt image,
  - gère appels provider IA,
  - stocke URL/metadata.

- `HybridCompositionService`
  - fusionne fond IA + calques overlay (titre, logo, CTA),
  - sort preview/export.

- `VisualPreviewService`
  - rend preview en temps réel,
  - recadre aux formats cibles.

- `VisualLinkService`
  - lie un visuel à article/page/post/GMB,
  - marque `used` quand attaché à une publication active.

- `VisualLibraryService`
  - save, duplicate, archive, download.

---

## 6) Vues admin (UX)

- `visual-studio/index.php`
  - étape 1: mode de création,
  - étape 2: paramètres obligatoires,
  - étape 3: génération + variantes,
  - étape 4: actions de sortie.

- `visual-studio/editor.php`
  - édition texte, style, CTA,
  - zone de layers (overlay),
  - preview instantanée.

- `visual-studio/library.php`
  - filtres (plateforme, statut, objectif, date),
  - cartes miniatures,
  - actions rapides (utiliser, dupliquer, archiver, télécharger).

- Composants réutilisables :
  - `VisualFormFields`, `VisualPreviewCanvas`, `VisualStatusBadge`, `VisualLinkedEntities`.

---

## 7) Logique de preview

- Prévisualisation côté serveur + client :
  - serveur : consolidation payload et normalisation,
  - client : rendu rapide (iframe/canvas) pour ajustements.
- Modes preview :
  - plein format,
  - safe-zone plateforme,
  - simulation mobile (story/feed).
- Versionning léger : garder au moins 1 snapshot précédent par édition.

---

## 8) Logique de liaison article/page/post

- Ajouter bouton **« Créer un visuel »** dans les écrans d’édition : article, page, post social, post GMB.
- Au clic :
  - ouvrir wizard prérempli,
  - alimenter `source_text` depuis titre + résumé + extrait contenu,
  - proposer plateforme/format selon type contenu,
  - créer un lien dans `visual_links` après sauvegarde/usage.
- À la publication :
  - si un visuel est choisi comme média principal, statut passe à `used`.

---

## 9) Tests manuels (checklist)

1. **Création libre**
   - Saisir un prompt + paramètres obligatoires.
   - Vérifier génération + statut `generated`.

2. **Création depuis contenu existant**
   - Lancer depuis article.
   - Vérifier préremplissage titre/résumé/format/canal.

3. **Création depuis modèle système**
   - Tester les 10 modèles demandés.
   - Vérifier cohérence des champs préremplis.

4. **Moteurs**
   - Générer via HTML5/template.
   - Générer via IA image.
   - Générer en hybride.

5. **Cycle de statut**
   - Enchaîner `draft -> generated -> editing -> validated -> saved -> used -> archived`.
   - Bloquer transitions illégales.

6. **Bibliothèque**
   - Save, duplicate, download, archive.
   - Filtrage par plateforme + statut.

7. **Isolation tenant**
   - Un tenant A ne voit pas les visuels de B.

8. **Liaison publication**
   - Attacher un visuel à un post social puis GMB.
   - Vérifier table de liaison + statut `used`.

---

## 10) MVP réaliste avant V2

## MVP (2–3 sprints)

- Conserver et faire évoluer l’existant `image-editor` en `visual-studio`.
- Couvrir 3 modes de création.
- Paramètres obligatoires complets.
- Moteur template HTML5 **+** un moteur IA image (provider unique).
- Bibliothèque avec actions : save / use / download / duplicate / archive.
- Statuts de base complets.
- Liaisons article + page + post social + GMB (au moins 1 contexte par type).
- 10 modèles système livrés en base.

## V2 (après validation usage)

- Moteur hybride avancé (calques multi-zones, masques, branding kit).
- Générations asynchrones avec queue et retry.
- A/B variants automatiques.
- Score qualité visuel par objectif (engagement/conversion).
- Analytics d’usage par plateforme + modèle.
- Multi-provider IA + fallback automatique.

---

## Écart concret entre existant et cible

### Ce qui existe déjà
- module admin `image-editor`,
- API dédiée,
- génération template simple,
- sauvegarde et listing basique.

### Ce qu’il manque pour atteindre la cible
- architecture service/repository dédiée,
- moteur IA image réel + hybride,
- statuts métier complets,
- modèles système métiers (10 types demandés),
- liaisons robustes multi-modules,
- isolation tenant explicite dans le schéma,
- preview avancée multi-format/safe-zone,
- tests fonctionnels formalisés.

