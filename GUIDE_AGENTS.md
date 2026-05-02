# 📚 Guide d'Utilisation des Agents IA

## Quick Start

### 1. Configuration Initiale

```php
// Dans bootstrap.php ou n'importe quel fichier
$agentService = new AgentService(
    db(),                              // Connexion PDO
    $_ENV['OPENROUTER_API_KEY'] ?? ''  // Clé API
);
```

### 2. Exécuter un Agent

```php
// Récupérer un agent par ID ou slug
$agent = $agentService->getAgentBySlug('agent-contenu');

// Exécuter une tâche
$result = $agentService->executeAgent(
    agentId: $agent['id'],
    task: 'Générer une description de propriété',
    input: [
        'property_type' => 'villa',
        'surface' => '250m²',
        'rooms' => 5,
        'price' => '750000€',
        'location' => 'Côte d\'Azur'
    ]
);

// Vérifier le résultat
if ($result['success']) {
    echo $result['output'];      // La réponse du modèle
    echo $result['tokens'];      // Tokens consommés
    echo $result['time_ms'];     // Temps d'exécution en ms
    echo $result['model'];       // Modèle utilisé
} else {
    error_log('Agent error: ' . $result['error']);
}
```

---

## 🎯 Agents Prédéfinis

### 1. Agent Contenu (Génération)

**Slug:** `agent-contenu`  
**Rôle:** Générer descriptions, articles, annonces

```php
$result = $agentService->executeAgent(
    $agentService->getAgentBySlug('agent-contenu')['id'],
    'Écrire une annonce immobilière',
    [
        'type' => 'T4 Neuf',
        'lieu' => 'Paris 15e',
        'surface' => '85m²',
        'prix' => '550000€',
        'points_forts' => ['Balcon', 'Proche RER', 'Standing']
    ]
);
```

**Prompt système:**
> "Vous êtes un expert en rédaction immobilière. Créez des descriptions élégantes et persuasives qui mettent en avant les points forts des propriétés. Utilisez un ton professionnel mais sympathique."

**Température:** 0.8 (créatif)

---

### 2. Agent Analyse Images (Vision)

**Slug:** `agent-images`  
**Rôle:** Analyser et décrire des photos de propriétés

```php
$result = $agentService->executeAgent(
    $agentService->getAgentBySlug('agent-images')['id'],
    'Analyser une photo de salon',
    [
        'image_url' => 'https://exemple.com/salon.jpg',
        'focus' => 'État général, éléments clés, défauts visibles'
    ]
);
```

**Prompt système:**
> "Analysez les images de propriétés immobilières. Décrivez l'état général, les caractéristiques visibles, les éléments attractifs et les défauts. Soyez précis et professionnel."

**Température:** 0.5 (objectif)

---

### 3. Agent Email (Marketing)

**Slug:** `agent-email`  
**Rôle:** Générer séquences email et campagnes

```php
$result = $agentService->executeAgent(
    $agentService->getAgentBySlug('agent-email')['id'],
    'Générer séquence d\'emails pour prospect neuf',
    [
        'prospect_name' => 'Jean Dupont',
        'property_interest' => 'Villa Côte d\'Azur',
        'sequence_length' => 5,
        'goal' => 'Convertir en RDV'
    ]
);
```

**Prompt système:**
> "Vous êtes un copywriter email expert. Créez des emails marketing persuasifs avec objet accrocheur et contenu qui convertit. Structurez avec CTA clairs. Le ton doit être professionnel mais chaleureux."

**Température:** 0.7 (persuasif)

---

### 4. Agent Prospection (Qualification)

**Slug:** `agent-prospect`  
**Rôle:** Analyser et qualifier les leads

```php
$result = $agentService->executeAgent(
    $agentService->getAgentBySlug('agent-prospect')['id'],
    'Qualifier ce lead immobilier',
    [
        'nom' => 'Marie Martin',
        'email' => 'marie@example.com',
        'message' => 'Intéressée par maison 150m² avec jardin, budget 400k€',
        'source' => 'Contact form',
        'budget' => 400000,
        'timeline' => 'Urgent'
    ]
);
```

**Réponse type:**
```json
{
    "score": 8.5,
    "qualité": "Excellent",
    "type_contact": "Acheteur sérieux",
    "action_suivante": "Envoyer 3 propriétés correspondantes",
    "notes": "Client sérieux, budget défini, timeline urgente"
}
```

---

### 5. Agent Recherche (Analyse Marché)

**Slug:** `agent-recherche`  
**Rôle:** Analyser le marché et tendances

```php
$result = $agentService->executeAgent(
    $agentService->getAgentBySlug('agent-recherche')['id'],
    'Analyser le marché immobilier local',
    [
        'zone' => 'Marseille 13e',
        'type_bien' => 'T3-T4',
        'surface_range' => '80-120m²',
        'period' => 'Derniers 6 mois'
    ]
);
```

---

### 6. Agent Embeddings (Vectorisation)

**Slug:** `agent-embed`  
**Rôle:** Créer des vecteurs pour recherche sémantique

```php
// Vectoriser une description
$descriptions = [
    'Belle villa avec piscine et jardin de 500m² vue mer',
    'Appartement spacieux près des transports',
    'Studio cosy dans le quartier historique'
];

foreach ($descriptions as $desc) {
    $result = $agentService->executeAgent(
        $agentService->getAgentBySlug('agent-embed')['id'],
        'Vectoriser cette description',
        ['text' => $desc]
    );
    
    // Stocker le vecteur en DB pour recherche future
    $embedding = json_decode($result['output'], true);
    // INSERT INTO property_embeddings (vector, property_id) ...
}
```

---

## 🔌 Intégration dans les Modules

### Exemple: Génération Auto de Description

```php
// Dans modules/biens/accueil.php
if ($action === 'generate-description') {
    $agentService = new AgentService(db(), $_ENV['OPENROUTER_API_KEY'] ?? '');
    
    $result = $agentService->executeAgent(
        $agentService->getAgentBySlug('agent-contenu')['id'],
        'Générer description de bien immobilier',
        [
            'type' => $_POST['property_type'] ?? '',
            'surface' => $_POST['surface'] ?? '',
            'rooms' => $_POST['rooms'] ?? '',
            'price' => $_POST['price'] ?? '',
            'location' => $_POST['location'] ?? '',
            'features' => $_POST['features'] ?? []
        ]
    );
    
    if ($result['success']) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'description' => $result['output'],
            'tokens' => $result['tokens'],
            'model' => $result['model']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    exit;
}
```

### AJAX Frontend

```javascript
document.getElementById('generate-btn').addEventListener('click', async () => {
    const response = await fetch('/admin?module=biens&action=generate-description', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            property_type: 'villa',
            surface: '250',
            rooms: 5,
            price: '750000',
            location: 'Cannes'
        })
    });
    
    const data = await response.json();
    if (data.success) {
        document.getElementById('description').value = data.description;
        console.log(`Généré en ${data.time}ms avec ${data.tokens} tokens`);
    }
});
```

---

## 📊 Monitoring et Logs

### Voir les exécutions récentes

```php
$logs = db()->query('
    SELECT al.*, a.name as agent_name
    FROM agent_logs al
    JOIN agents a ON a.id = al.agent_id
    WHERE al.created_at > NOW() - INTERVAL 24 HOUR
    ORDER BY al.created_at DESC
    LIMIT 100
')->fetchAll(PDO::FETCH_ASSOC);

foreach ($logs as $log) {
    echo "{$log['agent_name']}: {$log['tokens_used']} tokens en {$log['execution_time_ms']}ms - {$log['status']}\n";
}
```

### Statistiques par agent

```php
$stats = db()->query('
    SELECT 
        agent_id,
        COUNT(*) as executions,
        AVG(tokens_used) as avg_tokens,
        AVG(execution_time_ms) as avg_time_ms,
        SUM(CASE WHEN status = \'success\' THEN 1 ELSE 0 END) as successes,
        SUM(CASE WHEN status = \'error\' THEN 1 ELSE 0 END) as errors
    FROM agent_logs
    GROUP BY agent_id
    ORDER BY executions DESC
')->fetchAll(PDO::FETCH_ASSOC);
```

---

## 🛠️ Créer un Nouvel Agent

### 1. Via l'Admin

1. Va à `/admin?module=agents`
2. Clique "Nouveau Agent"
3. Remplis les champs:
   - **Nom**: Agent Traductor
   - **Slug**: agent-traductor
   - **Catégorie**: Traitement du texte
   - **System Prompt**: "Vous êtes traducteur expert français-anglais..."
4. Enregistre
5. Assigne un modèle (Llama 3 70B par exemple)

### 2. Via le Code

```php
$agentService = new AgentService(db(), $_ENV['OPENROUTER_API_KEY']);

$agentId = $agentService->createAgent([
    'slug' => 'agent-custom',
    'name' => 'Agent Personnalisé',
    'description' => 'Fait des trucs spécifiques',
    'task_category' => 'custom',
    'system_prompt' => 'Vous êtes...',
    'is_active' => 1
]);

// Assigner un modèle
$agentService->assignModel($agentId, 'meta-llama/llama-3-70b-instruct', [
    'provider' => 'openrouter',
    'temperature' => 0.5,
    'max_tokens' => 2048,
    'is_primary' => 1
]);
```

---

## 💡 Bonnes Pratiques

### ✅ À Faire

```php
// 1. Vérifier la clé API
if (empty($_ENV['OPENROUTER_API_KEY'])) {
    throw new Exception('Clé OpenRouter manquante');
}

// 2. Utiliser les slugs plutôt que les IDs
$agent = $agentService->getAgentBySlug('agent-contenu');

// 3. Toujours vérifier le résultat
if (!$result['success']) {
    error_log("Agent error: {$result['error']}");
    // Fallback ou message utilisateur
}

// 4. Limiter les tokens
$config = ['max_tokens' => 1024]; // Pas 32000 par défaut

// 5. Monitorer les coûts
// Chaque 1M tokens = ~$1-2 selon le modèle
```

### ❌ À Éviter

```php
// ❌ Clés API en dur
$service = new AgentService(db(), 'sk-or-xxxx');

// ❌ Pas de vérification d'erreur
$result = $agentService->executeAgent(...);
echo $result['output']; // Crash si erreur!

// ❌ Modèles trop puissants pour tout
executeAgent(..., 'claude-opus'); // Lent et cher pour du simple

// ❌ Contexte très large sans besoin
'max_tokens' => 16000; // Trop pour la plupart des tâches
```

---

## 🚀 Optimisations

### Température

- **0.0-0.3**: Déterministe (analyse, classification)
- **0.5-0.7**: Équilibré (contenu, email)
- **0.8-1.0**: Créatif (brainstorm, fiction)

### Modèles par Cas d'Usage

| Cas | Modèle Recommandé | Raison |
|-----|-------------------|--------|
| Chat conversationnel | Mistral 7B | Rapide, bon français |
| Contenu long | Llama 3 70B | Capacité contexte, qualité |
| Email marketing | Mistral Medium | Rapide, persuasif |
| Analyse images | Claude 3 | Vision supérieure |
| Embeddings | MXBai Embed | Français optimal |
| Réaction critique | Qwen 32B | Très analytique |

---

## 📞 Support

Pour les problèmes:

1. Vérifier `storage/logs/php_errors.log`
2. Vérifier la table `agent_logs` pour les détails d'exécution
3. Tester manuellement via `/admin?module=agents`
4. Vérifier la clé OpenRouter est valide
5. Vérifier le modèle est disponible dans la sync

---

**Version:** 1.0  
**Last Updated:** 2026-04-24
