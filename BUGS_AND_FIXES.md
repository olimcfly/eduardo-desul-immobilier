# 🐛 LISTE COMPLÈTE DES BUGS & CORRECTIONS REQUISES

**État du projet:** Version 8.6  
**Date d'analyse:** 01/04/2026  
**Statut:** À traiter AVANT livraison

---

## 🔴 BUGS CRITIQUES (Bloquants)

### Bug #1: Configuration manquante
**Sévérité:** 🔴 CRITIQUE  
**Localisation:** `/config/`  
**Problème:**
```
✗ config/config.php n'existe pas (seulement config.example.php)
✗ config/smtp.php n'existe pas (seulement smtp.example.php)
✗ Site ne peut pas démarrer sans config/config.php
```

**Impact:** Site complètement NON fonctionnel  
**Solution:**
```bash
# Créer les fichiers de config
cp /config/config.example.php /config/config.php
cp /config/smtp.example.php /config/smtp.php

# Éditer les valeurs (voir DEPLOYMENT_GUIDE.md)
```

**Fichiers à modifier:**
- config/config.php (créer)
- config/smtp.php (créer)

---

### Bug #2: Base de données non initialisée
**Sévérité:** 🔴 CRITIQUE  
**Localisation:** `/database/migrations/`  
**Problème:**
```
✗ Tables SQL non créées
✗ Pas de schema initial
✗ Migrations SQL jamais exécutées
```

**Impact:** Aucun data persistence possible  
**Solution:**
```bash
# Exécuter toutes les migrations
mysql -u user -p database < database/migrations/20260325_client_instances.sql
mysql -u user -p database < database/migrations/20260325_estimateur_module.sql
mysql -u user -p database < database/migrations/20260325_market_analysis_phase1_phase2.sql
mysql -u user -p database < database/migrations/20260325_seo_columns_pages.sql
mysql -u user -p database < database/migrations/20260326_client_instance_wizard.sql
mysql -u user -p database < database/rgpd_schema.sql
```

**Création admin:**
```sql
-- Vérifier table admins existe
SHOW TABLES LIKE 'admins';

-- Créer table si absent
CREATE TABLE IF NOT EXISTS admins (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  role ENUM('superuser', 'admin') DEFAULT 'admin',
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insérer admin initial
INSERT INTO admins (email, password_hash, name, role) VALUES (
  'admin@domain.fr',
  '$2y$10$HASH_BCRYPT_PASSWORD',
  'Administrateur',
  'superuser'
);
```

---

### Bug #3: Module Estimation incomplète
**Sévérité:** 🔴 CRITIQUE  
**Localisation:** `/admin/modules/immobilier/estimation/`  
**Problème:**
```
✗ Architecture instable (PSR-4 partiellement implémenté)
✗ Dépendances mal définies
✗ Auto-migration SQL "silencieuse" en page (bad practice)
✗ Frontend minimaliste (estimateur.js = 50 lignes)
✗ Pas d'API endpoint /api/estimation/submit
✗ Leads non créés depuis formulaire
✗ Validation côté client inexistante
✗ Emails de confirmation manquants
```

**Impact:** Feature clé non fonctionnelle  
**Solution:** Voir section "CORRECTIONS MAJEURS" ci-dessous

---

### Bug #4: Leads n'est pas intégré à l'estimation
**Sévérité:** 🔴 CRITIQUE  
**Localisation:** Multiple  
**Problème:**
```
✗ Formulaire estimation ne crée pas de lead
✗ Données soumises non stockées en DB
✗ CRM ne reçoit pas les prospects
✗ Admin ne peut pas suivre les demandes
```

**Impact:** Aucun suivi client possible  
**Solution:**
```php
// Dans estimation/public.php, ajouter après soumission:
$lead_data = [
    'first_name' => $_POST['first_name'],
    'email' => $_POST['email'],
    'phone' => $_POST['phone'],
    'source' => 'estimation',
    'created_at' => date('Y-m-d H:i:s')
];

$db->insert('leads', $lead_data);
```

---

## 🟠 BUGS IMPORTANTS (À corriger)

### Bug #5: Database::getInstance() pattern incohérent
**Sévérité:** 🟠 IMPORTANT  
**Localisation:** `/includes/classes/Database.php`  
**Problème:**
```
✗ getInstance() retourne PDO (pas Database object)
✗ Getter methods (query, insert, etc.) inaccessibles
✗ Confusion entre Database::getInstance() et getDB()
✗ Deux patterns différents utilisés dans le code
```

**Impact:** Code source confus et difficile à maintenir  
**Solution:**
```php
// Utiliser cohérent:
$db = Database::getConnection();      // Retourne Database object
$pdo = Database::getInstance();       // Retourne PDO directement

// OU uniquement:
$db = getDB();  // Helper function (voir includes/init.php)
```

---

### Bug #6: Gestion des erreurs incomplète
**Sévérité:** 🟠 IMPORTANT  
**Localisation:** Partout  
**Problème:**
```
✗ Try/catch pas systématique
✗ Erreurs DB silencieuses (migration SQL "silencieuse" en ligne 36 renderers/estimateur.php)
✗ Pas de logging centralisé
✗ Messages d'erreur génériques
```

**Solution:**
```php
// Utiliser fonction helper:
function logError($message, $context = []) {
    $log = date('Y-m-d H:i:s') . ' | ERROR | ' . $message . ' | ' . json_encode($context) . "\n";
    error_log($log, 3, ROOT_PATH . '/logs/error.log');
}

// Utiliser partout
try {
    // code...
} catch (Exception $e) {
    logError('Database query failed', ['error' => $e->getMessage()]);
    throw new Exception('Erreur serveur interne');
}
```

---

### Bug #7: SMTP config non testée
**Sévérité:** 🟠 IMPORTANT  
**Localisation:** `/config/smtp.php`, `/includes/classes/EmailService.php`  
**Problème:**
```
✗ SMTP.php existe mais paramètres non vérifiés
✗ Pas de fonction test SMTP
✗ Emails de confirmation estimation non implémentés
✗ EmailService peut échouer silencieusement
```

**Solution:** Créer fonction test
```php
function testSMTPConnection() {
    require_once ROOT_PATH . '/includes/classes/EmailService.php';
    try {
        $mailer = new EmailService();
        return [
            'success' => true,
            'message' => 'SMTP configuré correctement'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
```

---

## 🟡 BUGS MINEURS (À améliorer)

### Bug #8: Frontend non-professionnel
**Sévérité:** 🟡 MINEUR  
**Localisation:** `/front/` templates  
**Problème:**
```
✗ estimateur.js = 50 lignes (minimal)
✗ Pas de validation côté client
✗ Pas d'animations
✗ Pas de loading states
✗ Design incohérent entre modules
```

**Solution:** Améliorer frontend (voir section CORRECTIONS)

---

### Bug #9: Sessions mal nommées
**Sévérité:** 🟡 MINEUR  
**Localisation:** `/admin/login.php`, `/admin/dashboard.php`  
**Problème:**
```
✗ Variables session incohérentes:
   - $_SESSION['admin_id']
   - $_SESSION['admin_email']
   - $_SESSION['admin_logged_in']
   - $_SESSION['advisor_name']
   - $_SESSION['admin_name']
✗ Confus et sujet à bugs
```

**Solution:**
```php
// Standardiser:
$_SESSION['user_id']
$_SESSION['user_email']
$_SESSION['user_role']  // 'admin', 'advisor', etc.
$_SESSION['user_name']
$_SESSION['authenticated_at']
$_SESSION['last_activity']
```

---

### Bug #10: Validation input insuffisante
**Sévérité:** 🟡 MINEUR  
**Localisation:** Tous formulaires  
**Problème:**
```
✗ Formulaires not validating properly
✗ Pas de CSRF tokens
✗ HTML Purifier pas utilisé
✗ XSS possible sur certains inputs
```

---

## ✅ CORRECTIONS MAJEURES À IMPLÉMENTER

### Correction #1: Reconstruire Module Estimation
**Fichiers à créer/modifier:**

1. **API Endpoint** - Créer `/admin/api/estimation/submit.php`:
```php
<?php
/**
 * API: POST /admin/api/estimation/submit
 * Enregistrer une demande d'estimation
 */

// Sécurité & validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

// CSRF + Input validation
$data = $_POST;
$required = ['first_name', 'email', 'phone', 'property_type', 'address', 'surface'];

$errors = [];
foreach ($required as $field) {
    if (empty($data[$field])) {
        $errors[] = "Champ requis: $field";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    exit(json_encode(['errors' => $errors]));
}

// Valider email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Email invalide']));
}

// Connexion DB
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/config/database.php';

$db = getDB();

try {
    // 1. Créer ou récupérer lead
    $lead_stmt = $db->prepare(
        "SELECT id FROM leads WHERE email = ? LIMIT 1"
    );
    $lead_stmt->execute([$data['email']]);
    $lead = $lead_stmt->fetch();
    
    if (!$lead) {
        $db->insert('leads', [
            'first_name' => $data['first_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'source' => 'estimation',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $lead_id = $db->lastInsertId();
    } else {
        $lead_id = $lead['id'];
    }

    // 2. Créer demande d'estimation
    $estimation_id = $db->insert('estimation_requests', [
        'lead_id' => $lead_id,
        'property_type' => $data['property_type'],
        'address' => $data['address'],
        'surface' => (int) $data['surface'],
        'rooms' => (int) ($data['rooms'] ?? 0),
        'year_built' => (int) ($data['year_built'] ?? 0),
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ]);

    // 3. Envoyer email au client
    require_once dirname(__DIR__, 3) . '/includes/classes/EmailService.php';
    $mailer = new EmailService();
    $mailer->sendEmail(
        $data['email'],
        'Votre demande d\'estimation a été reçue',
        'Nous traiterons votre demande sous 24h.',
        ['from_name' => SITE_TITLE]
    );

    // 4. Répondre avec succès
    http_response_code(200);
    exit(json_encode([
        'success' => true,
        'estimation_id' => $estimation_id,
        'message' => 'Demande enregistrée avec succès'
    ]));

} catch (Exception $e) {
    error_log('Estimation API Error: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['error' => 'Erreur serveur']));
}
?>
```

2. **JavaScript Handler** - Créer `/front/assets/js/estimation-handler.js`:
```javascript
/**
 * Gestion du formulaire d'estimation
 */
class EstimationHandler {
  constructor(formSelector = '#estimation-form') {
    this.form = document.querySelector(formSelector);
    if (!this.form) return;
    
    this.setupListeners();
  }

  setupListeners() {
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));
  }

  validateForm() {
    const required = ['first_name', 'email', 'phone', 'property_type', 'address', 'surface'];
    const errors = [];

    required.forEach(field => {
      const input = this.form.querySelector(`[name="${field}"]`);
      if (!input || !input.value.trim()) {
        errors.push(`Le champ ${field} est requis`);
      }
    });

    // Valider email
    const email = this.form.querySelector('[name="email"]')?.value;
    if (email && !this.validateEmail(email)) {
      errors.push('Email invalide');
    }

    return errors;
  }

  validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  async handleSubmit(e) {
    e.preventDefault();

    // Validation
    const errors = this.validateForm();
    if (errors.length > 0) {
      alert('Erreurs:\n' + errors.join('\n'));
      return;
    }

    // Show loading
    const btn = this.form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Traitement...';

    try {
      const formData = new FormData(this.form);
      const response = await fetch('/admin/api/estimation/submit', {
        method: 'POST',
        body: formData
      });

      const json = await response.json();

      if (!response.ok) {
        alert('Erreur: ' + (json.error || 'Erreur inconnue'));
        return;
      }

      // Succès
      alert('✅ Votre demande a été enregistrée!\nNous vous recontacterons sous 24h.');
      this.form.reset();

    } catch (err) {
      alert('Erreur réseau: ' + err.message);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Envoyer ma demande';
    }
  }
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
  new EstimationHandler();
});
```

---

### Correction #2: Standardiser architecture Database
**Fichier:** `/includes/classes/Database.php`

Améliorer les méthodes de requête:
```php
// Ajouter à Database class:

public function transaction(callable $callback) {
    try {
        $this->connection->beginTransaction();
        $result = $callback();
        $this->connection->commit();
        return $result;
    } catch (Exception $e) {
        $this->connection->rollBack();
        throw $e;
    }
}

public function count($table, $where = '', $params = []) {
    $whereStr = $where ? " WHERE $where" : '';
    $sql = "SELECT COUNT(*) as cnt FROM $table$whereStr";
    $stmt = $this->query($sql, $params);
    return $stmt->fetch()['cnt'];
}
```

---

### Correction #3: Ajouter Validation globale
**Créer:** `/includes/Validator.php`

```php
<?php
class Validator {
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && empty($data[$field])) {
                $errors[$field] = "{$field} est requis";
            }
            
            if (isset($rule['email']) && $rule['email'] && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Email invalide";
            }
            
            if (isset($rule['min']) && strlen($data[$field]) < $rule['min']) {
                $errors[$field] = "Minimum {$rule['min']} caractères";
            }
        }
        
        return $errors;
    }
}
```

---

## 📋 PRIORITÉS DE CORRECTION

### Phase 1: Bloquants (Aujourd'hui)
- [ ] Créer config/config.php
- [ ] Créer config/smtp.php  
- [ ] Créer base de données
- [ ] Exécuter migrations SQL
- [ ] Créer admin initial
- [ ] Tester login

### Phase 2: API Estimation (Demain)
- [ ] Créer /admin/api/estimation/submit.php
- [ ] Créer /front/assets/js/estimation-handler.js
- [ ] Créer formulaire HTML
- [ ] Tester soumission
- [ ] Vérifier leads créés

### Phase 3: Améliorations (Cette semaine)
- [ ] Standardiser Database pattern
- [ ] Ajouter validation globale
- [ ] Ajouter CSRF tokens
- [ ] Tester sécurité
- [ ] Tests email

### Phase 4: Polish (Avant livraison)
- [ ] Frontend animations
- [ ] Loading states
- [ ] Error messages améliorés
- [ ] Mobile responsiveness
- [ ] Tests UAT

---

## 🔍 COMMENT TESTER LES CORRECTIONS

### Test Configuration:
```bash
# Accéder au diagnostic
https://domain.fr/DIAGNOSTIC.php

# Vérifier tous les tests passent
```

### Test Estimation:
```bash
# 1. Aller à page estimation
https://domain.fr/estimation

# 2. Remplir formulaire
# 3. Vérifier données en DB
mysql> SELECT * FROM leads WHERE email='test@example.com';
mysql> SELECT * FROM estimation_requests WHERE lead_id=X;

# 4. Vérifier email reçu
```

### Test Leads:
```bash
# 1. Aller admin
https://domain.fr/admin/

# 2. Afficher module leads/CRM
# 3. Vérifier nouveau lead visible
```

---

## ✅ CHECKLIST AVANT LIVRAISON

```
CONFIGURATION
[ ] config/config.php créé et rempli
[ ] config/smtp.php créé et rempli
[ ] Base de données créée
[ ] Migrations exécutées
[ ] Admin initial créé et fonctionnel

API ESTIMATION
[ ] /admin/api/estimation/submit.php créé
[ ] Validation formulaire côté client
[ ] Validation formulaire côté server
[ ] Leads créés depuis formulaire
[ ] Estimation enregistrée en DB

EMAILS
[ ] SMTP testé et fonctionnel
[ ] Email de confirmation envoyé
[ ] Email admin de notification

TESTS
[ ] Login admin OK
[ ] Formulaire estimation OK
[ ] Leads visibles en CRM
[ ] Aucun erreur PHP (logs)
[ ] Diagnostic.php = tout vert

SÉCURITÉ
[ ] config.php pas commitée
[ ] Pas de données sensibles en logs
[ ] HTTPS actif
[ ] Setup/install.php supprimé

LIVRAISON
[ ] Documentation client fournie
[ ] Admin credentials sécurisé
[ ] Support info fourni
[ ] DIAGNOSTIC.php supprimé
```

---

**Généré:** 01/04/2026  
**Version:** 1.0  
**Statut:** En cours de correction
