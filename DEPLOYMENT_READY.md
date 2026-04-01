# 🚀 DEPLOYMENT - READY TO EXECUTE

**Status:** ✅ All code complete  
**Date:** April 1, 2026  
**Next Step:** Database setup and migrations

---

## 📋 STEP-BY-STEP DEPLOYMENT

### Step 1️⃣: Update Configuration Files

Edit `/config/config.php` with your actual server values:

```bash
nano config/config.php
```

Key values to change:
```php
// Line 16-19: Site Information
define('INSTANCE_ID', 'desul-bordeaux');           // Keep as is or customize
define('SITE_TITLE', 'Eduardo Desul - Immobilier');
define('SITE_DOMAIN', 'eduardodesulimmobilier.fr');  // ← Change to your domain
define('ADMIN_EMAIL', 'admin@eduardodesulimmobilier.fr'); // ← Change to your email

// Line 28-32: Database Credentials (CRITICAL)
define('DB_HOST', 'localhost');        // Your database host
define('DB_PORT', 3306);               // Usually 3306
define('DB_NAME', 'eduardo_desul_prod');  // ← Change to your DB name
define('DB_USER', 'ed_user');          // ← Change to your DB user
define('DB_PASS', 'ChangeMe123!');     // ← Change to STRONG password
```

Edit `/config/smtp.php` with email configuration:

```bash
nano config/smtp.php
```

Key values to change:
```php
'smtp_host'   => 'smtp.your-provider.com',  // ← Your SMTP host
'smtp_port'   => 587,                       // Usually 587 or 465
'smtp_user'   => 'noreply@yourdomain.fr',   // ← Your email
'smtp_pass'   => 'your_email_password',     // ← Your email password
'smtp_from'   => 'noreply@yourdomain.fr',   // ← Your email
```

---

### Step 2️⃣: Create MySQL Database

Connect to your MySQL server and run:

```sql
-- Create database with UTF-8 support
CREATE DATABASE eduardo_desul_prod DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'ed_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';

-- Grant all permissions
GRANT ALL PRIVILEGES ON eduardo_desul_prod.* TO 'ed_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
```

**Via command line:**
```bash
mysql -u root -p -e "CREATE DATABASE eduardo_desul_prod DEFAULT CHARSET utf8mb4;"
mysql -u root -p -e "CREATE USER 'ed_user'@'localhost' IDENTIFIED BY 'password123';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON eduardo_desul_prod.* TO 'ed_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

---

### Step 3️⃣: Execute Database Migrations

Run the migration script to create all database tables:

```bash
# From project root directory
php database/migrate.php
```

Expected output:
```
════════════════════════════════════════════════════════════
  Running Migrations
════════════════════════════════════════════════════════════

Migrations découvertes:
  1. ✅ 20260325_client_instances.sql
  2. ✅ 20260325_estimateur_module.sql
  3. ✅ 20260325_market_analysis_phase1_phase2.sql
  4. ✅ 20260325_seo_columns_pages.sql
  5. ✅ 20260326_client_instance_wizard.sql
  6. ✅ rgpd_schema.sql

Execution Results:
✅ Migration 1 (X statements executed) (123ms)
✅ Migration 2 (X statements executed) (456ms)
...

Summary:
  ✅ Executed: 6
  ⏳ Skipped: 0
  ❌ Failed: 0
```

---

### Step 4️⃣: Create Initial Admin User

Connect to your database and create the first admin:

```sql
-- Generate bcrypt hash (PHP command):
-- php -r "echo password_hash('YourPassword123!', PASSWORD_BCRYPT);"

-- Insert admin user (use the generated hash):
INSERT INTO admins (email, password_hash, name, role, status) VALUES (
  'admin@eduardodesulimmobilier.fr',
  '$2y$10$PASTE_YOUR_BCRYPT_HASH_HERE',
  'Administrator',
  'superuser',
  'active'
);
```

---

### Step 5️⃣: Verify Setup

Access the diagnostic tool from your browser:

```
https://your-domain.fr/DIAGNOSTIC.php
```

Run all checks and verify they pass ✅:
- Database connection
- Configuration files
- Directory permissions
- PHP extensions
- SMTP connectivity
- File structure

All tests **MUST** be green (✅) before proceeding.

---

### Step 6️⃣: Test Core Features

#### Test 1: Admin Login
```
1. Go to https://domain.fr/admin/login.php
2. Login with admin@... + password
3. Verify dashboard loads ✅
```

#### Test 2: Estimation Form
```
1. Go to https://domain.fr/estimation
2. Fill in test data
3. Submit form
4. Verify:
   - ✅ Success message displayed
   - ✅ Lead created in admin panel
   - ✅ Confirmation email received
   - ✅ Admin notification email received
```

#### Test 3: Email Sending
```
1. Test SMTP connection
2. Send test email from admin
3. Verify email received (check spam folder)
4. Check reply capability
```

---

## 🔧 Useful Migration Commands

```bash
# View migration status
php database/migrate.php --status

# List all migrations
php database/migrate.php --list

# Force re-execute all (careful!)
php database/migrate.php --force

# Reset migration tracking
php database/migrate.php --reset
```

---

## ⚠️ Critical Before Go-Live

- [ ] Database created and accessible
- [ ] All migrations executed successfully
- [ ] Admin user created and can login
- [ ] SMTP configured and tested
- [ ] DIAGNOSTIC.php shows all green ✅
- [ ] Estimation form submits and creates leads
- [ ] Email notifications received
- [ ] SSL/HTTPS configured
- [ ] DIAGNOSTIC.php deleted (security)
- [ ] setup/install.php deleted (security)

---

## 🆘 Troubleshooting

### "Cannot connect to database"
```
✓ Check DB_HOST, DB_NAME, DB_USER, DB_PASS in config/config.php
✓ Verify MySQL user has correct permissions
✓ Test with: mysql -u ed_user -p -h localhost eduardo_desul_prod
✓ Check MySQL service is running
```

### "Permission denied" errors
```bash
# Fix permissions
chmod 755 uploads/ logs/ cache/ config/
chmod 644 config/config.php config/smtp.php
```

### "File not found" errors
```
✓ Verify .htaccess exists at project root
✓ Enable mod_rewrite: sudo a2enmod rewrite
✓ Set AllowOverride All in Apache config
✓ Restart Apache: sudo systemctl restart apache2
```

### Migrations fail to run
```bash
# Check MySQL extension
php -m | grep mysql
php -m | grep PDO

# Test config directly
php -r "require 'config/config.php'; echo 'DB OK';"
```

---

## 📞 Quick Support Checklist

| Issue | Solution | Command |
|-------|----------|---------|
| DB won't connect | Check credentials | `mysql -u user -p -h host db` |
| Migrations stuck | Check logs | `tail -f logs/php_errors.log` |
| Emails not sending | Test SMTP | See DIAGNOSTIC.php |
| Forms not submitting | Check JS errors | Browser DevTools F12 |
| Admin won't load | Check permissions | `chmod 755 admin/` |

---

**Once all checks pass ✅ → Site is ready for client access!**

Next: Provide client with login credentials and brief training.
