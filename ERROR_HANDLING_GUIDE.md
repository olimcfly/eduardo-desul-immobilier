# 🛡️ ERROR HANDLING GUIDE

## Vue d'ensemble

Deux nouvelles classes pour une gestion d'erreurs systématique dans toute l'application :

1. **ErrorHandler** - Gestion centralisée des erreurs et logging
2. **BaseController** - Classe de base pour les contrôleurs avec try/catch intégré

## 📁 Fichiers Créés

- `/includes/classes/ErrorHandler.php` - Gestionnaire d'erreurs
- `/includes/classes/BaseController.php` - Classe de base pour contrôleurs

## 🚀 Utilisation

### 1️⃣ Dans les Contrôleurs

**Avant (sans gestion d'erreurs) :**

```php
class ArticleController {
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        // Pas de gestion d'erreurs
        $stmt = $this->db->prepare("INSERT INTO articles (title, content) VALUES (?, ?)");
        $stmt->execute([$data['title'], $data['content']]);
        return $this->db->lastInsertId();
    }
}
```

**Après (avec ErrorHandler) :**

```php
class ArticleController extends BaseController {
    public function __construct() {
        parent::__construct(); // Initialise $this->db avec gestion d'erreurs
    }

    public function create($data) {
        try {
            // Valider les données
            if (!$this->validate($data, [
                'title' => ['required' => true, 'min' => 3, 'max' => 255],
                'content' => ['required' => true, 'min' => 10]
            ])) {
                throw new Exception('Validation failed');
            }

            // Sanitiser les données
            $data = $this->sanitize($data);

            // Exécuter la requête
            $stmt = $this->query(
                "INSERT INTO articles (title, content) VALUES (?, ?)",
                [$data['title'], $data['content']]
            );

            return [
                'id' => $this->db->lastInsertId(),
                'message' => 'Article created successfully'
            ];

        } catch (Exception $e) {
            // Les erreurs sont loggées automatiquement
            ErrorHandler::respond(
                'Failed to create article: ' . $e->getMessage(),
                ErrorHandler::HTTP_BAD_REQUEST,
                [],
                $e
            );
        }
    }
}
```

### 2️⃣ Dans les API Endpoints

**Avant :**

```php
<?php
$db = Database::getInstance();
$email = $_POST['email'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email required']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo json_encode(['data' => $user]);
```

**Après :**

```php
<?php
try {
    if (!class_exists('ErrorHandler')) {
        require_once dirname(__DIR__) . '/includes/classes/ErrorHandler.php';
    }

    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        ErrorHandler::respond('Email is required', ErrorHandler::HTTP_BAD_REQUEST);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ErrorHandler::respond('Invalid email format', ErrorHandler::HTTP_BAD_REQUEST);
    }

    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        ErrorHandler::respond('User not found', ErrorHandler::HTTP_NOT_FOUND);
    }

    // Réponse réussie
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $user,
        'message' => 'User found'
    ]);

} catch (Exception $e) {
    ErrorHandler::respond(
        'An error occurred: ' . $e->getMessage(),
        ErrorHandler::HTTP_INTERNAL_ERROR,
        [],
        $e
    );
}
```

### 3️⃣ Logging Centralisé

```php
// Les erreurs sont automatiquement loggées dans /logs/errors.log

try {
    // Code qui peut échouer
} catch (Exception $e) {
    // Cette ligne log automatiquement dans /logs/errors.log
    ErrorHandler::log($e, 'MonController::method', ['extra' => 'data']);
}
```

## 🎯 Features

### ErrorHandler

| Méthode | Description |
|---------|-------------|
| `log($exception, $context, $extra)` | Logger une exception |
| `respond($message, $status, $data, $exception)` | Répondre avec erreur (JSON ou HTML) |
| `detectContext()` | Détecter contexte (API/Web/CLI) |
| `getErrors()` | Récupérer toutes les erreurs |
| `hasErrors()` | Vérifier s'il y a des erreurs |

### BaseController

| Méthode | Description |
|---------|-------------|
| `__construct()` | Initialise la BD avec gestion d'erreurs |
| `executeAction($action, $params)` | Exécuter action avec try/catch |
| `validate($data, $rules)` | Valider les données |
| `sanitize($data)` | Nettoyer les données |
| `query($sql, $params)` | Exécuter requête BD |
| `respondJson($msg, $success, $status, $data, $exception)` | Répondre JSON |
| `redirect($url, $message, $type)` | Redirection avec message |

## 🔒 Avantages

✅ **Gestion centralisée** - Toutes les erreurs au même endroit  
✅ **Logging automatique** - Chaque erreur est enregistrée  
✅ **Contexte intelligent** - Détecte API vs Web automatiquement  
✅ **Réponses cohérentes** - Erreurs formatées uniformément  
✅ **Debug en développement** - Stack traces disponibles en DEBUG_MODE  
✅ **Sécurité en production** - Erreurs génériques pour l'utilisateur  

## 📝 Exemple Complet

```php
<?php
require_once dirname(__DIR__) . '/includes/classes/ErrorHandler.php';
require_once dirname(__DIR__) . '/includes/classes/BaseController.php';

class UserController extends BaseController {

    public function getUser($userId) {
        try {
            // Valider l'ID
            if (!is_numeric($userId)) {
                throw new Exception('Invalid user ID');
            }

            // Récupérer l'utilisateur
            $stmt = $this->query(
                "SELECT id, email, name FROM users WHERE id = ?",
                [$userId]
            );

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->statusCode = ErrorHandler::HTTP_NOT_FOUND;
                throw new Exception('User not found');
            }

            // Réponse réussie
            return $this->respondJson(
                'User retrieved successfully',
                true,
                ErrorHandler::HTTP_OK,
                ['user' => $user]
            );

        } catch (Exception $e) {
            return $this->handleError($e, 'getUser');
        }
    }

    public function createUser($data) {
        try {
            // Valider les données
            if (!$this->validate($data, [
                'email' => ['required' => true, 'email' => true],
                'name' => ['required' => true, 'min' => 2, 'max' => 255]
            ])) {
                throw new Exception('Validation failed');
            }

            // Sanitiser
            $data = $this->sanitize($data);

            // Insérer l'utilisateur
            $stmt = $this->query(
                "INSERT INTO users (email, name, created_at) VALUES (?, ?, NOW())",
                [$data['email'], $data['name']]
            );

            $newId = $this->db->lastInsertId();

            return $this->respondJson(
                'User created successfully',
                true,
                ErrorHandler::HTTP_CREATED,
                ['userId' => $newId]
            );

        } catch (Exception $e) {
            $this->statusCode = ErrorHandler::HTTP_BAD_REQUEST;
            return $this->handleError($e, 'createUser');
        }
    }
}

// Utilisation
$controller = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getUser($_GET['id'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->createUser($_POST);
}
```

## 📚 Intégration Progressive

1. **Phase 1** - Créer ErrorHandler et BaseController ✅
2. **Phase 2** - Mettre à jour les contrôleurs existants
3. **Phase 3** - Ajouter try/catch aux API endpoints
4. **Phase 4** - Implémenter le logging centralisé

## 🔍 Vérification

Pour vérifier la gestion d'erreurs :

1. Regarder `/logs/errors.log` pour les erreurs enregistrées
2. En DEBUG_MODE, les messages d'erreur incluent les stack traces
3. En production, les utilisateurs voient des messages génériques
4. Les API reçoivent des réponses JSON bien formatées

## 🚨 Bonnes Pratiques

✅ Toujours utiliser try/catch autour du code qui peut échouer  
✅ Logger les erreurs avec contexte utile  
✅ Répondre avec les codes HTTP appropriés  
✅ Ne pas afficher les détails en production  
✅ Valider et nettoyer toujours les données utilisateur  
✅ Utiliser des messages d'erreur clairs