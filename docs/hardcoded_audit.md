# Audit hardcoding CRM immobilier (PHP + JS)

## Méthodologie
- Scan global des fichiers `*.php` et `*.js` via `rg`.
- Vérification manuelle des vues publiques, templates, configs core, services API, scripts admin.
- Périmètre : modules Construire, Attirer, Capturer, Convertir, Optimiser, Assistant, Biens, GMB, SEO, Social, Paramètres + front/public + admin.

---

## Catégorie A — Identité du conseiller

### Éléments hardcodés trouvés
- **Nom/prénom** : `Eduardo Desul`, `Eduardo De Sul`.
- **Titre pro** : `Conseiller immobilier`, `Conseiller Immobilier`, `Conseiller`.
- **Photo** : `/assets/images/eduardo-portrait.jpg`.
- **Slogan/bio marketing** : textes fixes type “Expert immobilier indépendant à Bordeaux...”.
- **Signature/personnalisation messages** : textes “Eduardo vous répondra...”, “Eduardo vous contactera...”.

### Fichiers principaux impactés
- `public/templates/header.php`
- `public/templates/footer.php`
- `public/pages/home.php`
- `public/pages/a-propos.php`
- `public/pages/contact.php`
- `public/pages/estimation.php`
- `public/blog/article.php`
- `public/actualites/article.php`
- `public/capture/*.php`
- `admin/views/layout.php`, `admin/views/login.php`
- `public/assets/js/contact.js`, `public/assets/js/biens.js`

---

## Catégorie B — Agence / Réseau

### Éléments hardcodés trouvés
- **Nom agence** : `Eduardo Desul Immobilier`.
- **Email pro/agence** : `contact@eduardo-desul-immobilier.fr`.
- **Adresse agence** : `Bordeaux, France`.
- **SIRET/RSAC/CPI** : mentions textuelles figées dans pages légales.
- **Réseaux/outils** : mentions fixes Google My Business, LinkedIn, Facebook.

### Fichiers principaux impactés
- `config/config.php`
- `core/config/config.php`
- `core/config/constants.php`
- `public/legal/mentions-legales.php`
- `public/templates/footer.php`
- `admin/views/layout.php`
- `database/seed_admin.php`

---

## Catégorie C — Zone géographique

### Éléments hardcodés trouvés
- **Ville principale** : Bordeaux.
- **Villes/quartiers** : Chartrons, Mérignac, Pessac, Saint-Michel, Victoire.
- **Adresse exemple** : `12 rue des Chartrons, Bordeaux`.
- **Pages/labels SEO** : “Guide local Bordeaux”, “Conseiller à Bordeaux”.

### Fichiers principaux impactés
- `public/pages/home.php`
- `public/pages/biens.php`
- `public/pages/services.php`
- `public/pages/a-propos.php`
- `public/pages/estimation.php`
- `public/capture/estimation-gratuite.php`
- `public/blog/*.php`, `public/actualites/*.php`
- `public/templates/layout.php`
- `modules/construire/accueil.php`, `modules/assistant/accueil.php`

---

## Catégorie D — Données métier

### Éléments hardcodés trouvés
- **Années d'expérience** : `15 ans`.
- **Volume ventes/transactions** : `+200`, `200+`.
- **Note clients** : `4.9★`, `4.9/5`.
- **Délais** : réponse 24h / 48h, vente en 3 semaines (contenu figé).
- **Prix/biens de démonstration** : cartes annonces statiques avec prix/surfaces/pièces.

### Fichiers principaux impactés
- `public/pages/home.php`
- `public/pages/a-propos.php`
- `public/pages/biens.php`
- `public/pages/estimation.php`
- `public/actualites/article.php`
- `public/blog/article.php`

---

## Catégorie E — Configuration technique

### Éléments hardcodés trouvés
- **URLs hardcodées** : `https://eduardo-desul-immobilier.fr`, endpoints externes.
- **Secrets en clair (critique)** :
  - identifiants DB dans `core/config/database.php`
  - identifiants SMTP dans `core/config/constants.php` (avant refactor de ce ticket)
- **SMTP fixe** : host/port/secure/user/from/name.
- **Timezone/langue figées** : `Europe/Paris`, `fr_FR`.
- **Chemins absolus** : `/home/...` dans `core/config/config.php` (legacy).

### Fichiers principaux impactés
- `core/config/database.php`
- `core/config/constants.php`
- `config/config.php`
- `core/config/config.php`
- `core/services/*Service.php` (Cloudinary, GoogleMaps, AI, QuickChart)

---

## Catégorie F — Contenu marketing

### Éléments hardcodés trouvés
- **Accroches fixes** : hero, CTA, promesses de délai, argumentaires vente.
- **Scripts de prospection figés** : contenus pédagogiques/modules.
- **Templates emails / messages automatiques fixes** : JS contact/biens + pages capture.
- **Textes site vitrine statiques** : home, services, guides, blog, actualités.

### Fichiers principaux impactés
- `public/pages/*.php`
- `public/capture/*.php`
- `public/ressources/*.php`
- `admin/data/marketing_modules.php`
- `public/assets/js/contact.js`
- `public/assets/js/biens.js`

---

## Recommandation structurelle
1. **Migrer toutes ces valeurs vers `settings` par utilisateur** (fait dans cette PR au niveau schéma + helpers).
2. Ajouter un écran admin “Paramètres” par groupe (`conseiller`, `agence`, `zone`, `metier`, `technique`, `notifications`, `site_vitrine`).
3. Remplacer progressivement toutes les chaînes métier/marketing par `setting('...')`.
4. Supprimer les secrets du code et imposer `.env` + chiffrement en base (`is_encrypted=1`).
