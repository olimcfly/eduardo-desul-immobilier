# 🚀 GUIDE DE DÉPLOIEMENT & INSTALLATION

**Version:** 8.6  
**Client:** Eduardo Desul Immobilier  
**Date:** 01/04/2026

---

## 1️⃣ PRÉ-REQUIS SERVEUR

```
PHP >= 7.4 (recommandé 8.2+)
MySQL >= 5.7 / MariaDB >= 10.3
Extension: PDO, PDO_MySQL, JSON, Curl, OpenSSL
```

**Vérification:**
```bash
php -v
php -m | grep -E "PDO|mysql|curl"
mysql --version
```

---

## 2️⃣ ÉTAPE 1: COPIER LES FICHIERS

### Sur le serveur:
```bash
# Clone ou upload des fichiers
git clone https://github.com/olimcfly/eduardo-desul-immobilier.git /var/www/site-domain.fr
cd /var/www/site-domain.fr

# Ou via FTP/SSH upload
# scp -r . user@host:/var/www/site-domain.fr
```

### Permissions:
```bash
chmod 755 /var/www/site-domain.fr
chmod 755 /var/www/site-domain.fr/{uploads,logs,cache,config}
chmod 644 /var/www/site-domain.fr/.htaccess
```

---

## 3️⃣ ÉTAPE 2: CRÉER LA BASE DE DONNÉES

### Via PHPMyAdmin (CPanel):
```
1. Aller à Databases → MySQL Databases
2. Nom: eduardo_desul_prod (ou votre choix)
3. Charset: utf8mb4
4. Créer l'utilisateur avec mot de passe sécurisé
5. Donner ALL PRIVILEGES
```

### Via SSH/MySQL CLI:
```sql
CREATE DATABASE eduardo_desul_prod DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ed_user'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON eduardo_desul_prod.* TO 'ed_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 4️⃣ ÉTAPE 3: CONFIGURER LE SITE

### A. Copier le fichier config:
```bash
cp config/config.example.php config/config.php
cp config/smtp.example.php config/smtp.php
```

### B. Éditer `/config/config.php`:

```php
<?php
// ═══════════════════════════════════════════════════════════
// 📌 À CHANGER POUR CE DÉPLOIEMENT
// ═══════════════════════════════════════════════════════════

define('INSTANCE_ID', 'desul-bordeaux');              // Identifiant unique du site
define('SITE_TITLE', 'Eduardo Desul - Immobilier');  // Titre affiché
define('SITE_DOMAIN', 'eduardodesulimmobilier.fr');  // Domaine principal
define('ADMIN_EMAIL', 'admin@eduardodesulimmobilier.fr'); // Email admin

// ═══════════════════════════════════════════════════════════
// 🗄️ BASE DE DONNÉES (valeurs de l'étape 3)
// ═══════════════════════════════════════════════════════════

define('DB_HOST', 'localhost');              // Ou adresse serveur
define('DB_PORT', '3306');                  // Par défaut 3306
define('DB_NAME', 'eduardo_desul_prod');    // Nom base créée
define('DB_USER', 'ed_user');               // Utilisateur BD
define('DB_PASS', 'SecurePassword123!');    // Mot de passe sécurisé

// ═══════════════════════════════════════════════════════════
// 🤖 CLÉS API (Optionnelles mais recommandées)
// ═══════════════════════════════════════════════════════════

// OpenAI (pour génération contenu AI)
define('OPENAI_API_KEY', 'sk-proj-YOUR_KEY_HERE');

// Anthropic Claude (prioritaire si défini)
define('ANTHROPIC_API_KEY', 'sk-ant-YOUR_KEY_HERE');

// ═══════════════════════════════════════════════════════════
// Reste inchangé (ne pas toucher)
// ═══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(dirname(__FILE__)));
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('PUBLIC_PATH', ROOT_PATH . '/public');

$detected_domain = $_SERVER['HTTP_HOST'] ?? SITE_DOMAIN;
$detected_domain = str_replace('www.', '', $detected_domain);

define('SITE_URL', 'https://' . $detected_domain);
define('ADMIN_URL', SITE_URL . '/admin');
define('API_URL', SITE_URL . '/api');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

define('DB_CHARSET', 'utf8mb4');
define('DB_TIMEZONE', 'Europe/Paris');
define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'ECOSYSTEM_' . strtoupper(INSTANCE_ID));

define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');
define('SITE_DESCRIPTION', 'Conseiller immobilier indépendant à Bordeaux. Achat, vente, location de biens immobiliers.');
define('DEBUG_MODE', false);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

?>
```

### C. Éditer `/config/smtp.php`:

```php
<?php
// Configuration email SMTP
// Obtenir ces valeurs chez votre hébergeur

define('SMTP_HOST', 'smtp.your-provider.com');    // Ex: mail.ovh.net
define('SMTP_PORT', 587);                         // Généralement 587 ou 465
define('SMTP_USER', 'noreply@eduardodesulimmobilier.fr');
define('SMTP_PASS', 'email_password');
define('SMTP_FROM', 'noreply@eduardodesulimmobilier.fr');
define('SMTP_FROM_NAME', 'Eduardo Desul - Immobilier');

// Test SSL (généralement false pour 587, true pour 465)
define('SMTP_SECURE', false);

?>
```

---

## 5️⃣ ÉTAPE 4: INITIALISER LA BASE DE DONNÉES

### Option A: Via navigateur (Installateur web)
```
1. Accéder à https://votredomaine.fr/setup/install.php
2. Remplir le formulaire
3. Cliquer "Installer"
4. Supprimer setup/install.php après succès
```

### Option B: Via SSH (Recommandé)
```bash
cd /var/www/site-domain.fr

# Exécuter les migrations SQL
mysql -u ed_user -p eduardo_desul_prod < database/migrations/20260325_client_instances.sql
mysql -u ed_user -p eduardo_desul_prod < database/migrations/20260325_estimateur_module.sql
mysql -u ed_user -p eduardo_desul_prod < database/migrations/20260325_market_analysis_phase1_phase2.sql
mysql -u ed_user -p eduardo_desul_prod < database/migrations/20260325_seo_columns_pages.sql
mysql -u ed_user -p eduardo_desul_prod < database/migrations/20260326_client_instance_wizard.sql
mysql -u ed_user -p eduardo_desul_prod < database/rgpd_schema.sql
```

### Créer l'admin initial (SQL):
```sql
-- Se connecter à la base
mysql -u ed_user -p eduardo_desul_prod

-- Créer table admins (si n'existe pas)
CREATE TABLE IF NOT EXISTS admins (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  role ENUM('superuser', 'admin', 'advisor') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Ajouter admin
-- Password: Admin@123 (à changer à la première connexion)
INSERT INTO admins (email, password_hash, name, role, status) VALUES (
  'admin@eduardodesulimmobilier.fr',
  '$2y$10$Y2Q3Yzg3MzM2ZDM4MjExN...',  -- hash bcrypt
  'Administrateur',
  'superuser',
  'active'
);
```

**⚠️ Générer hash bcrypt:**
```bash
# Via PHP
php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT);"

# Ou via openssl
openssl passwd -1 Admin@123
```

---

## 6️⃣ ÉTAPE 5: CONFIGURER LE DOMAINE

### DNS Configuration:
```
Type: A
Host: @
Points to: [votre_IP_serveur]

Type: CNAME
Host: www
Points to: @
```

### Apache VirtualHost:
```apache
<VirtualHost *:80>
    ServerName eduardodesulimmobilier.fr
    ServerAlias www.eduardodesulimmobilier.fr
    DocumentRoot /var/www/site-domain.fr
    
    <Directory /var/www/site-domain.fr>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog /var/log/apache2/site-domain-error.log
    CustomLog /var/log/apache2/site-domain-access.log combined
    
    # Redirect HTTP → HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
```

### SSL/HTTPS (Let's Encrypt):
```bash
sudo certbot certonly --apache -d eduardodesulimmobilier.fr -d www.eduardodesulimmobilier.fr
```

---

## 7️⃣ ÉTAPE 6: VÉRIFIER L'INSTALLATION

### Checklist:
```bash
# 1. Fichiers présents
[ ] config/config.php ✅
[ ] config/smtp.php ✅
[ ] .htaccess ✅

# 2. Permissions
[ ] chmod 755 uploads, logs, cache, config ✅
[ ] Dossiers writable ✅

# 3. Base de données
[ ] Créée et accessible ✅
[ ] Tables créées (run migrations) ✅
[ ] Admin user créé ✅

# 4. HTTPS
[ ] SSL certificat actif ✅
[ ] Redirect HTTP → HTTPS ✅

# 5. Test navigateur
[ ] https://eduardodesulimmobilier.fr → Affiche page accueil ✅
[ ] https://eduardodesulimmobilier.fr/admin/login.php → Form login ✅
```

### Tests fonctionnels:
```bash
# Test 1: Connexion admin
curl -X POST https://eduardodesulimmobilier.fr/admin/login.php \
  -d "email=admin@..." \
  -d "password=..."

# Test 2: Page publique
curl https://eduardodesulimmobilier.fr/accueil

# Test 3: Estimation formulaire
curl https://eduardodesulimmobilier.fr/estimation

# Test 4: API
curl https://eduardodesulimmobilier.fr/admin/api/router.php
```

---

## 8️⃣ ÉTAPE 7: SUPPRIMER FICHIERS SENSIBLES

```bash
# CRITICAL - Supprimer installateur
rm -f setup/install.php

# Optionnel - Diagnostic
rm -f routing.php

# Fichiers à ne jamais commit
# (Déjà dans .gitignore)
config/config.php
config/smtp.php
logs/*
cache/*
uploads/*
```

---

## 9️⃣ ÉTAPE 8: SETUP MONITORING & BACKUPS

### Logs:
```bash
# Créer logs directory
mkdir -p logs
chmod 755 logs

# Monitor errors
tail -f logs/*.log

# Setup logrotate
sudo vim /etc/logrotate.d/site-domain
```

### Backups:
```bash
# Database
mysqldump -u ed_user -p eduardo_desul_prod > backup_$(date +%Y%m%d).sql

# Full backup
tar -czf site-domain-backup-$(date +%Y%m%d).tar.gz /var/www/site-domain.fr

# Cron job (quotidien à 2h du matin)
0 2 * * * mysqldump -u ed_user -pPassword DATABASE > /backups/db_$(date +\%Y\%m\%d).sql
```

---

## 🔟 ÉTAPE 9: TRAINING CLIENT

### Accès admin:
```
URL: https://eduardodesulimmobilier.fr/admin/
Email: admin@eduardodesulimmobilier.fr
Password: [À changer à la première connexion]
```

### Modules disponibles:
```
✅ Pages (Créer/Éditer pages)
✅ Articles (Blog + SEO)
✅ Estimation (Formulaire estimation gratuite)
✅ Leads (CRM + suivi prospects)
✅ Réseaux Sociaux (Facebook, TikTok, etc.)
✅ SEO (Analyse + optimisation)
✅ Paramètres (Configuration site)
```

### Tâches importantes:
1. Changer le mot de passe admin
2. Configurer les réseaux sociaux
3. Créer les pages (accueil, contact, etc.)
4. Configurer le formulaire d'estimation
5. Ajouter un article test

---

## 🔧 DÉPANNAGE COURANT

### Erreur: "Cannot connect to database"
```
❌ Vérifier: DB_HOST, DB_NAME, DB_USER, DB_PASS dans config.php
❌ Vérifier: extension PDO_MySQL activée (php -m)
❌ Vérifier: serveur MySQL tourne (service mysql status)
```

### Erreur: "File not found" sur pages
```
❌ Vérifier: .htaccess en place
❌ Vérifier: AllowOverride All dans VirtualHost Apache
❌ Vérifier: RewriteEngine activated (a2enmod rewrite)
```

### Erreur: "Permission denied" sur uploads
```
❌ Vérifier: chmod 755 uploads/
❌ Vérifier: User Apache peut écrire (chown www-data:www-data uploads/)
❌ Vérifier: Espace disque disponible
```

### Erreur: "SMTP connection failed"
```
❌ Vérifier: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
❌ Tester: telnet SMTP_HOST SMTP_PORT
❌ Vérifier: Firewall bloque pas SMTP
❌ Essayer: Port 465 au lieu de 587
```

---

## ✅ CHECKLIST FINALE AVANT LIVRAISON

```
INFRASTRUCTURE
[ ] Domaine configuré et DNS actif
[ ] SSL/HTTPS actif
[ ] Apache/Nginx configured
[ ] PHP 7.4+ avec extensions
[ ] MySQL 5.7+ accessible

CONFIGURATION
[ ] config/config.php complété
[ ] config/smtp.php complété
[ ] Base de données créée
[ ] Migrations SQL exécutées
[ ] Admin user créé

SÉCURITÉ
[ ] Password admin fort changé
[ ] setup/install.php supprimé
[ ] config/config.php non commitée (gitignored)
[ ] Fichiers sensibles protégés
[ ] Backups en place

FONCTIONNALITÉ
[ ] Page accueil accessible
[ ] Admin login fonctionne
[ ] Estimation form visible
[ ] Leads peuvent être créés
[ ] Emails configurés (test)

TESTS
[ ] Page d'accueil charge correctement
[ ] Formulaire estimation valide
[ ] Admin dashboard accessible
[ ] CRM leads affichés
[ ] Aucun erreur PHP logs

CLIENT
[ ] Documentation livrée
[ ] Admin credentials
[ ] Support info
[ ] FAQ
[ ] Phone support setup
```

---

## 📞 SUPPORT APRÈS LIVRAISON

**Email:** support@company.com  
**Téléphone:** +33 X XX XX XX XX  
**Heures:** Lun-Ven 09h-18h  

**Que faire en cas de problème?**
1. Consulter FAQ (DELIVERY_ANALYSIS.md)
2. Vérifier les logs (logs/*.log)
3. Contacter support avec message d'erreur
4. Fournir logs serveur (tail -f logs/error.log)

---

**Version:** 1.0  
**Date:** 01/04/2026  
**Suivant:** Exécution du guide
