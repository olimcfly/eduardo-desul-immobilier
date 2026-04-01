# 🚀 GUIDE DE CONFIGURATION RAPIDE

**Fichier:** `config/config.php`  
**Date:** 01/04/2026

---

## ✅ CONFIGURATION CRÉÉE

Le fichier `config/config.php` a été créé avec:
- ✅ Connexion base de données
- ✅ Constantes de sécurité
- ✅ Chemins absolus
- ✅ Mode debug
- ✅ Timezone France (Europe/Paris)
- ✅ 30+ fonctions utilitaires

---

## 🔧 À ADAPTER POUR VOTRE SERVEUR

### 1️⃣ Informations du Site (Lignes 14-19)

```php
define('INSTANCE_ID', 'desul-bordeaux');              // ← Identifiant unique du site
define('SITE_TITLE', 'Eduardo Desul - Immobilier');  // ← Titre qui s'affiche
define('SITE_DOMAIN', 'eduardodesulimmobilier.fr');  // ← Votre domaine
define('ADMIN_EMAIL', 'admin@eduardodesulimmobilier.fr'); // ← Email du site
```

**À changer:**
```php
define('INSTANCE_ID', 'mon-agence-bordeaux');              // Exemple
define('SITE_TITLE', 'Mon Agence Immobilière');
define('SITE_DOMAIN', 'monagence.fr');
define('ADMIN_EMAIL', 'contact@monagence.fr');
```

---

### 2️⃣ Base de Données (Lignes 25-31)

```php
define('DB_HOST', 'localhost');          // ← Serveur MySQL
define('DB_PORT', 3306);                 // ← Port (3306 par défaut)
define('DB_NAME', 'eduardo_desul_prod'); // ← Nom de la BD
define('DB_USER', 'ed_user');            // ← Utilisateur BD
define('DB_PASS', 'ChangeMe123!');       // ← Mot de passe BD
```

**Trouver ces infos chez votre hébergeur (cPanel, Ionos, OVH, etc.):**

```
Chez OVH/Ionos:
- Aller à cPanel → Databases
- Créer nouvelle BD
- Créer utilisateur avec mot de passe

Exemple de valeurs:
define('DB_HOST', 'localhost');
define('DB_NAME', 'monagenc_bd');
define('DB_USER', 'monagenc_user');
define('DB_PASS', 'SecurePassword123!');
```

---

### 3️⃣ Clés API (Lignes 37-42) - OPTIONNEL

Pour la v1, laissez vides. À configurer plus tard si vous voulez l'IA:

```php
define('OPENAI_API_KEY', '');     // Vide pour l'instant
define('ANTHROPIC_API_KEY', '');  // Vide pour l'instant
```

---

### 4️⃣ Mode Debug (Ligne 102)

```php
define('DEBUG_MODE', (ENVIRONMENT === 'development'));
```

**En PRODUCTION:** Laisser `false`  
**En DÉVELOPPEMENT:** Mettez `true`

```php
// Pour développement local:
define('ENVIRONMENT', 'development');

// Pour production:
define('ENVIRONMENT', 'production');
```

---

## ⚡ VÉRIFIER LA CONFIGURATION

### Après modification, tester:

```bash
# 1. Créer la base de données MySQL (via cPanel)
CREATE DATABASE nom_db DEFAULT CHARSET utf8mb4;
CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON nom_db.* TO 'db_user'@'localhost';

# 2. Exécuter migrations SQL
mysql -u db_user -p nom_db < database/migrations/20260325_client_instances.sql

# 3. Accéder au diagnostic
https://votredomaine.fr/DIAGNOSTIC.php
```

---

## 📋 CHECKLIST RAPIDE

```
[ ] Adapter INSTANCE_ID
[ ] Adapter SITE_TITLE
[ ] Adapter SITE_DOMAIN
[ ] Adapter ADMIN_EMAIL
[ ] Configurer DB_HOST
[ ] Configurer DB_NAME
[ ] Configurer DB_USER
[ ] Configurer DB_PASS
[ ] Tester connexion BD
[ ] Créer la BD MySQL
[ ] Exécuter migrations
[ ] Tester /DIAGNOSTIC.php
```

---

## 🔒 SÉCURITÉ

**IMPORTANT:**
- ❌ Ne JAMAIS commiter config.php en git
- ✅ Ajouter à `.gitignore`:
```
config/config.php
config/smtp.php
logs/
cache/
uploads/
```

- ✅ Utiliser mot de passe BD fort (minimum 12 caractères)
- ✅ Changer mot de passe par défaut admin à la première connexion
- ✅ Activer HTTPS en production

---

## 🆘 PROBLÈMES COURANTS

### ❌ "Cannot connect to database"
```
Vérifier:
1. DB_HOST correct (localhost ou adresse serveur)
2. DB_NAME existe (créé via cPanel)
3. DB_USER et DB_PASS corrects
4. Extension PDO activée (php -m | grep PDO)
```

### ❌ "Permission denied" sur dossiers
```bash
chmod 755 uploads/ logs/ cache/
```

### ❌ "HTTPS mixed content"
```php
// Vérifier:
define('SITE_URL', 'https://...'); // Doit être HTTPS
```

---

## 📞 FICHIERS À ADAPTER

| Fichier | À faire | Importance |
|---------|---------|-----------|
| config/config.php | Adapter BD + domaine | 🔴 CRITIQUE |
| config/smtp.php | Adapter si emails | 🟠 Important |
| .htaccess | Vérifier réwrite | 🟡 Recommandé |

---

## ✅ CONFIGURATION COMPLÈTE!

Après adaptation:
1. ✅ Fichier créé et adapté
2. ✅ BD créée et configurée
3. ✅ Migrations exécutées
4. ✅ Site prêt pour déploiement

**Prochaine étape:** Voir `DEPLOYMENT_GUIDE.md`

---

*Guide créé: 01/04/2026*  
*Version: 1.0*
