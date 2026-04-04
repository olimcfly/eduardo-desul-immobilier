# Module Admin GMB (Google My Business)

Ce module ajoute un **HUB GMB** calqué sur le HUB SEO (mêmes classes visuelles: `seo-hub`, `seo-grid`, `seo-card`, `btn`, `btn-sm`, `badges`, `small`).

## Fichiers à coller

- `admin/modules/gmb/index.php`
- `admin/modules/gmb/view.php`
- `admin/modules/gmb/controller.php`
- `admin/modules/gmb/Service/GmbService.php`
- `assets/admin/gmb/gmb-hub.js`
- `assets/admin/gmb/gmb-hub.css`

## Pattern utilisé

- `index.php` = wrapper léger (récupère `Auth::user()`, calcule `$userId`, instancie `GmbService(db())`, charge les stats, puis inclut `view.php`).
- `view.php` = rendu HTML uniquement (cartes + boutons + fallback par `href`).

## Endpoints API attendus (par défaut)

- `GET  /admin/api/gmb/stats`
- `POST /admin/api/gmb/sync`
- `POST /admin/api/gmb/request-review/test`

Le JavaScript est centralisé dans `assets/admin/gmb/gmb-hub.js`.
Si vous voulez des endpoints différents, modifiez l'objet `ENDPOINTS` en haut du fichier.

## Service

`GmbService` contient des méthodes mock:
- `getHubStats(int $userId): array`
- `syncNow(int $userId): array`
- `requestReviewTest(int $userId): array`

Des `TODO` sont déjà en place pour remplacer les mocks par des accès DB/API réels.

## Patch

Le patch prêt à appliquer est fourni ici:

- `patches/0001-add-gmb-module.patch`

Application:

```bash
git apply patches/0001-add-gmb-module.patch
```
