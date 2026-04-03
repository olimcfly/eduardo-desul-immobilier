# Analyse backend du dépôt pour le module CMS Immobilier

## 1) État actuel du dépôt (constats rapides)

Le dépôt contient déjà une base CMS immobilière, mais la partie "gestion des biens" est incomplète côté implémentation métier :

- Les routes front-end incluent déjà `/biens` et `/biens/{slug}`.  
- Une migration SQL initiale définit les tables `biens` et `bien_photos` avec des champs adaptés à un catalogue immobilier.  
- Le hub admin "Biens" existe avec des entrées de navigation vers le catalogue et la création.  
- Les fichiers `controller/repository/service/view/sql` du module `modules/biens` sont actuellement vides (squelettes à compléter).

## 2) Ce que cela implique pour votre plan

Votre proposition (classe `Property`, CRUD admin, upload photos, filtres) est cohérente avec le besoin, mais il faut l’adapter à la convention existante du projet :

- Le projet utilise des noms FR (`biens`, `bien_photos`, `titre`, `ville`, `statut`) plutôt que `properties` en anglais.
- Le socle SQL est déjà présent dans `database/migrations/001_init.sql`; il faut **réutiliser ce schéma** plutôt que créer de nouvelles tables parallèles.
- Les URL visées côté admin semblent orientées `/admin?module=biens` + sous-actions (et non un arborescence `admin/properties/*.php` classique).

## 3) Plan d’implémentation recommandé (adapté à ce repo)

### Étape A — Couche données (Repository)

Compléter `modules/biens/repositories/BienRepository.php` avec :

- `findAll(array $filters, int $page, int $perPage)`
- `countAll(array $filters)`
- `findById(int $id)` / `findBySlug(string $slug)`
- `create(array $data)` / `update(int $id, array $data)` / `delete(int $id)`
- `findPhotos(int $bienId)` / `addPhoto(...)` / `deletePhoto(...)` / `setPrimaryPhoto(...)`

### Étape B — Couche métier (Service)

Compléter `modules/biens/services/BienService.php` pour :

- centraliser validation/normalisation (prix, surfaces, coordonnées, statut)
- gérer la transformation des champs JSON (`caracteristiques`)
- fournir les métriques utilisées par le dashboard (`countActiveProperties()`)

### Étape C — Couche controller admin

Compléter `modules/biens/controllers/BienAdminController.php` avec actions :

- `index` (liste + filtres + pagination)
- `create` / `store`
- `edit` / `update`
- `delete`
- `photos` (upload/ordre/photo principale)

### Étape D — Vues admin

Implémenter les écrans `modules/biens/views/admin/*.php` :

- `index.php` (table + filtres)
- `form.php` (création/édition)
- `photos.php` (galerie + upload)

### Étape E — Front public (catalogue + détail)

Raccorder `modules/biens/controllers/BienController.php` et vues `catalogue/` + `detail/` pour :

- affichage des biens actifs
- filtrage public (ville, budget, type)
- fiche détail avec galerie et formulaire contact

## 4) Écarts techniques à corriger dès le départ

1. **Nommage domaine** : rester 100% sur `Bien*` (pas de `Property*`) pour éviter une double couche difficile à maintenir.
2. **Uploads** : stocker chemins relatifs + whitelist MIME stricte + limite taille.
3. **Validation serveur** : ne pas dépendre uniquement du front (prix numériques, lat/lng, enum `statut`).
4. **Transactions DB** : utiliser transaction lors d’opérations combinées (création bien + photos).
5. **Sécurité** : échapper toutes sorties vues, et filtrer strictement les accès admin.

## 5) Ordre de livraison conseillé (MVP)

1. Repository + Service (lecture seule)
2. Listing admin + pagination + filtres
3. Création/édition d’un bien
4. Upload photos + photo principale
5. Détail front public
6. Optimisations SEO/exports portails

## 6) Conclusion

Vous n’avez pas besoin de repartir d’une structure générique depuis zéro : le dépôt contient déjà l’ossature (routes, migration SQL, hub admin). La meilleure stratégie est de **compléter les fichiers `modules/biens` existants** en respectant la convention de données actuelle (`biens`, `bien_photos`, champs FR).
