# Plan d'installation rapide — passage en mode multi-conseiller

Ce document décrit la manière **la plus rapide** de rendre le site réellement multi-conseiller, en restant compatible avec l'architecture actuelle.

## 1) État actuel (important avant d'agir)

Le projet contient déjà des briques multi-utilisateur :

- Auth admin basée sur la table `users` (OTP + rôles).
- Système de paramètres `settings` scoppé par `user_id`.
- Plusieurs modules (`social`, `gmb`, etc.) déjà filtrés par `user_id`.

Mais certaines pages et données restent mono-conseiller :

- Pages front fortement personnalisées (nom, ville, branding en dur).
- Au moins une page (`guide-acheteur`) lit un conseiller fixe (`id = 1`).
- Des tables métier historiques (`biens`, `contacts`, `estimations`) ne portent pas de `user_id` dans les migrations historiques.

## 2) Objectif “installation rapide” (MVP en 24–48h)

### Résultat visé

- Un **superadmin** crée un conseiller.
- Le conseiller se connecte en OTP.
- Il ne voit **que ses propres données** (biens/leads/settings/modules).
- Le front affiche l'identité du bon conseiller selon le contexte (compte, domaine, sous-domaine ou slug).

## 3) Étapes techniques prioritaires (ordre recommandé)

## Étape A — Normaliser le modèle de données (priorité 1)

Ajouter `user_id` (FK vers `users.id`) partout où la donnée appartient à un conseiller.

Tables minimales à aligner :

- `biens`
- `contacts`
- `estimations`
- `leads` / `crm_leads` (si utilisées en production)
- `pages`, `articles`, `actualites`, `guide_local` (si CMS par conseiller)

Règles :

1. `user_id` nullable au départ pour migration progressive.
2. Backfill initial sur un compte “historique” (ex. admin principal).
3. Puis passage en `NOT NULL` + index `(user_id, created_at)` sur les tables volumineuses.

## Étape B — Appliquer un filtre `user_id` systématique (priorité 1)

Dans tous les repositories/controllers admin :

- `SELECT ... WHERE user_id = :user_id`
- `UPDATE ... WHERE id = :id AND user_id = :user_id`
- `DELETE ... WHERE id = :id AND user_id = :user_id`

Objectif : empêcher tout accès croisé entre conseillers.

## Étape C — Corriger les points mono-conseiller du front (priorité 1)

1. Remplacer les valeurs hardcodées (nom conseiller, ville, titres SEO) par `setting(...)`.
2. Supprimer les lectures fixes de type `WHERE id = 1`.
3. Centraliser la résolution du conseiller courant (ex. helper `currentAdvisorUserId()`).

## Étape D — Provisioning “1 clic” d'un nouveau conseiller (priorité 1)

Créer une action back-office (superadmin) qui :

1. Crée l'utilisateur (`users`).
2. Initialise les paramètres par défaut (`initUserSettings(...)`).
3. Duplique les templates nécessaires (social/GMB/email/etc.).
4. Envoie un email d'activation OTP.

Résultat : onboarding en moins de 2 minutes.

## Étape E — Routage multi-conseiller (priorité 2)

Choisir **un seul mode** pour aller vite :

- Option rapide : `/{slug-conseiller}/...`
- Option pro : sous-domaines (`prenom-nom.votre-domaine.com`)

Puis mapper ce contexte vers `user_id` avant le rendu des pages.

## Étape F — Contrôles sécurité/qualité (priorité 2)

Checklist avant mise en prod :

- Aucune requête métier sans filtre `user_id`.
- Aucune donnée front/admin affichée depuis un autre conseiller.
- Index SQL présents sur `user_id`.
- Test manuel avec 2 conseillers + 1 superadmin.

## 4) Plan d'exécution ultra-pragmatique

## Jour 1 (4–6h)

- Migration SQL `user_id` (tables critiques).
- Backfill des lignes existantes.
- Filtrage `user_id` sur `biens`, `contacts`, `estimations`.

## Jour 2 (4–6h)

- Dé-hardcoder le front (home/contact/a-propos/guide).
- Ajouter l'onboarding superadmin “créer conseiller”.
- Valider en test manuel multi-comptes.

## 5) Définition de “Done” (MVP)

Le site est considéré multi-conseiller quand :

1. Chaque conseiller voit uniquement ses propres données.
2. Le front affiche ses infos personnalisées sans hardcode.
3. La création d'un nouveau conseiller est automatisée.
4. Aucune régression mono-conseiller n'est visible après test croisé.

## 6) Risques à éviter

- Migrer la DB sans backfill (`user_id` null partout).
- Garder des pages avec branding en dur.
- Oublier `AND user_id = ...` sur les `UPDATE/DELETE`.
- Mélanger plusieurs stratégies de routage (slug + sous-domaine) dès le début.

---

## Recommandation finale

Pour une **installation rapide**, la meilleure stratégie est :

1. **Isolation data par `user_id`** (indispensable).
2. **Provisioning automatique** d'un conseiller.
3. **Routage simple par slug** dans un premier temps.

Ensuite seulement, vous pourrez faire évoluer vers une version “agence multi-marques” (thèmes, domaines dédiés, permissions fines).
