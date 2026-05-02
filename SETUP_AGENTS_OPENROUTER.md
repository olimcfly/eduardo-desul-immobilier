# 🤖 Configuration des Agents IA avec OpenRouter

**Date:** 24 avril 2026  
**Statut:** 🟢 **PRÊT À CONFIGURER**

---

## 📋 Vue d'ensemble

Un système complet d'agents IA a été créé pour automatiser les tâches de votre site immobilier. Chaque agent:
- ✅ Utilise un modèle open source via OpenRouter
- ✅ A ses propres instructions (system prompt)
- ✅ Peut effectuer des tâches spécifiques
- ✅ Est loggé et monitoré

---

## 🔑 Configuration OpenRouter

### 1. Obtenir ta clé API

1. Va sur [openrouter.ai](https://openrouter.ai)
2. Crée un compte ou connecte-toi
3. Va à **API Keys** → Crée une nouvelle clé
4. Copie la clé (format: `sk-or-...`)

### 2. Ajouter ta clé au site

1. Va à `/admin?module=parametres`
2. Clique sur **API** 
3. Ajoute ta clé dans le champ **OpenRouter API Key**
4. Enregistre

---

## 🎯 Agents Recommandés pour Immobilier

### 1️⃣ **Agent Contenu** 
**Tâche:** Générer descriptions de propriétés, articles SEO, annonces

| Aspect | Détail |
|--------|--------|
| **Modèle** | `meta-llama/llama-3-70b-instruct` |
| **Catégorie** | Génération de Contenu |
| **Température** | 0.8 (créatif mais cohérent) |
| **Capacités** | Texte long |
| **Prompt système** | "Vous êtes un expert en rédaction immobilière. Créez des descriptions élégantes et persuasives qui mettent en avant les points forts des propriétés." |

**Exemples de tâches:**
- Générer 5 variantes de description pour une villa
- Écrire un article SEO sur un quartier
- Créer une annonce pour LinkedIn/Instagram

---

### 2️⃣ **Agent Analyse Images**
**Tâche:** Décrire et analyser photos de propriétés

| Aspect | Détail |
|--------|--------|
| **Modèle** | `meta-llama/llama-2-13b-chat` (vision) ou `claude-3-vision` |
| **Catégorie** | Vision & Images |
| **Température** | 0.5 (objectif) |
| **Capacités** | Vision, Texte |
| **Prompt système** | "Analysez les images de propriétés immobilières. Décrivez l'état, les caractéristiques visibles, et la qualité. Soyez précis et professionnel." |

**Exemples de tâches:**
- Décrire une photo de salon
- Lister les défauts visibles
- Évaluer la qualité de la photo
- Extraire les éléments clés (piscine, jardin, etc.)

---

### 3️⃣ **Agent Email Marketing**
**Tâche:** Générer les séquences email pour les campagnes

| Aspect | Détail |
|--------|--------|
| **Modèle** | `mistralai/mistral-7b-instruct` ou `qwen/qwen-14b-chat` |
| **Catégorie** | Génération de Contenu |
| **Température** | 0.7 (persuasif) |
| **Capacités** | Texte |
| **Prompt système** | "Vous êtes un copywriter email expert. Créez des emails marketing persuasifs, avec objet accrocheur et contenu qui convertit. Structurez avec CTA clairs." |

**Exemples de tâches:**
- Générer 5 emails pour séquence prospect
- Créer email de relance post-RDV
- Écrire newsletter hebdomadaire

---

### 4️⃣ **Agent Prospection**
**Tâche:** Analyser et qualifier les leads

| Aspect | Détail |
|--------|--------|
| **Modèle** | `mistralai/mistral-medium` ou `meta-llama/llama-3-8b-instruct` |
| **Catégorie** | Analyse & Extraction |
| **Température** | 0.3 (très objectif) |
| **Capacités** | Texte, Extraction |
| **Prompt système** | "Analysez les informations de prospect et qualifiez-les selon votre pipeline de vente. Extrayez les informations clés et proposez les prochaines actions." |

**Exemples de tâches:**
- Analyser un formulaire de contact
- Qualifier un lead (chaud/tiède/froid)
- Proposer la meilleure approche de relance
- Détecter les anomalies/spammeurs

---

### 5️⃣ **Agent Recherche Marché**
**Tâche:** Analyser marché, tendances, prix

| Aspect | Détail |
|--------|--------|
| **Modèle** | `meta-llama/llama-3-70b-instruct` |
| **Catégorie** | Analyse & Extraction |
| **Température** | 0.4 (analytique) |
| **Capacités** | Texte long, Analyse |
| **Prompt système** | "Vous êtes un analyste immobilier expérimenté. Fournissez des analyses de marché précises basées sur les données. Identifiez les tendances et opportunités." |

**Exemples de tâches:**
- Analyser les prix du marché local
- Identifier les quartiers en hausse
- Comparer avec la concurrence
- Produire un rapport d'analyse

---

### 6️⃣ **Agent Embeddings**
**Tâche:** Créer des vecteurs pour recherche sémantique

| Aspect | Détail |
|--------|--------|
| **Modèle** | `mixedbread-ai/mxbai-embed-large` |
| **Catégorie** | Embeddings |
| **Température** | N/A (déterministe) |
| **Capacités** | Embeddings |
| **Utilisation** | Indexer descriptions de propriétés, articles pour recherche rapide |

---

## 📊 Modèles Open Source Disponibles via OpenRouter

### 🔤 **Text Generation** (Texte)

| Modèle | Org | Qualité | Vitesse | Prix | Notes |
|--------|-----|---------|---------|------|-------|
| `meta-llama/llama-3-70b-instruct` | Meta | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | $$ | Meilleur rapport qualité/prix, excellent français |
| `mistralai/mistral-medium` | Mistral | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $ | Très rapide, bon pour génération masse |
| `mistralai/mistral-7b-instruct` | Mistral | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | $ | Léger, chat conversationnel |
| `qwen/qwen-14b-chat` | Alibaba | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $ | Excellent français, multilingue |
| `qwen/qwen-32b-chat` | Alibaba | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | $$ | Plus puissant, français parfait |
| `deepseek/deepseek-chat` | DeepSeek | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $ | Bon pour code, très bon français |
| `meta-llama/llama-2-70b-chat` | Meta | ⭐⭐⭐⭐ | ⭐⭐⭐ | $$ | Stable, bon pour production |

### 👁️ **Vision** (Analyse d'images)

| Modèle | Org | Qualité | Notes |
|--------|-----|---------|-------|
| `meta-llama/llama-2-13b-chat` | Meta | ⭐⭐⭐⭐ | Bon pour descriptions basiques |
| `claude-3-vision` (via OR) | Anthropic | ⭐⭐⭐⭐⭐ | Meilleur pour analyse détaillée |
| `gpt-4-vision` (via OR) | OpenAI | ⭐⭐⭐⭐⭐ | Très précis mais cher |
| `llava-1.5-7b` | LMSYS | ⭐⭐⭐ | Léger, open source |

### 🔗 **Embeddings** (Vectorisation)

| Modèle | Dim | Qualité | Notes |
|--------|-----|---------|-------|
| `mixedbread-ai/mxbai-embed-large` | 1024 | ⭐⭐⭐⭐⭐ | Meilleur français, multilingue |
| `nomic-ai/nomic-embed-text-v1` | 768 | ⭐⭐⭐⭐ | Bon rapport quali/prix |
| `sentence-transformers/all-minilm-l6-v2` | 384 | ⭐⭐⭐ | Très léger, pour appareils limités |
| `intfloat/e5-large` | 1024 | ⭐⭐⭐⭐ | Très bon pour français |

### 🎤 **Speech-to-Text** (Audio)

| Modèle | Qualité | Notes |
|--------|---------|-------|
| `openai/whisper-large` | ⭐⭐⭐⭐⭐ | Via OpenRouter, très bon français |
| `openai/whisper-base` | ⭐⭐⭐ | Plus léger, moins précis |

### 📈 **Reranking** (Pertinence)

| Modèle | Qualité | Notes |
|--------|---------|-------|
| `mixedbread-ai/mxbai-rerank-base-v1` | ⭐⭐⭐⭐⭐ | Classement pertinence résultats |
| `jina-ai/jina-reranker-v1` | ⭐⭐⭐⭐ | Bon pour search |

---

## 🚀 Étapes de Configuration

### 1. Configure ta clé OpenRouter dans Paramètres

```
/admin?module=parametres → API → OpenRouter API Key
```

### 2. Va au module Agents

```
/admin?module=agents
```

### 3. Synchronise les modèles

Clique sur **"Synchroniser Modèles OpenRouter"** → La liste se remplit

### 4. Crée tes agents

**Clique sur "Nouveau Agent"** et configure:

- **Nom**: Agent Contenu
- **Slug**: agent-contenu
- **Catégorie**: Génération de Contenu
- **System Prompt**: (voir ci-dessus)

### 5. Assigne des modèles

Pour chaque agent:
- Clique **Éditer**
- **Assigner un Modèle**
- Sélectionne le modèle + température + max_tokens
- Marque en tant que **Modèle principal** si désiré

### 6. Teste un agent

```php
$service = new AgentService(db(), $_ENV['OPENROUTER_API_KEY']);
$result = $service->executeAgent(1, 'Décris cette maison');
var_dump($result);
```

---

## 💾 Fichiers Créés

### Database
- `database/migrations/031_agents_system.sql` - Tables pour agents, modèles, tâches, logs

### Services
- `core/services/AgentService.php` - Logique complète d'agents

### Admin Module
- `modules/agents/accueil.php` - Interface de gestion

### Configuration
- `core/bootstrap.php` - Service chargé au démarrage

---

## 📖 Utiliser un Agent dans le Code

```php
$service = new AgentService(db(), $_ENV['OPENROUTER_API_KEY']);

// Exécuter un agent
$result = $service->executeAgent(
    agentId: 1,  // ID de l'agent
    task: 'Générer description pour villa luxe',
    input: [
        'type' => 'villa',
        'surface' => '250m²',
        'prix' => '750000€'
    ]
);

if ($result['success']) {
    echo $result['output'];      // Réponse
    echo $result['tokens'];      // Tokens utilisés
    echo $result['time_ms'];     // Temps d'exécution
} else {
    echo $result['error'];       // Message d'erreur
}
```

---

## 🔍 Monitoring & Logs

Tous les appels sont loggés dans `agent_logs`:

```sql
SELECT * FROM agent_logs WHERE created_at > NOW() - INTERVAL 1 DAY ORDER BY created_at DESC;
```

Colonnes utiles:
- `agent_id` - Quel agent
- `task_name` - La tâche
- `model_used` - Quel modèle
- `tokens_used` - Consommation
- `execution_time_ms` - Performance
- `status` - success/error
- `error_message` - Si erreur

---

## 💰 Estimation de Coûts

OpenRouter propose les modèles **avec pricing très avantageux**:

| Modèle | Entrée | Sortie |
|--------|--------|--------|
| Mistral 7B | $0.07/1M tokens | $0.07/1M tokens |
| Llama 3 70B | $0.59/1M tokens | $0.79/1M tokens |
| Qwen 32B | $0.35/1M tokens | $0.35/1M tokens |

**Exemple:** 1000 pages générées à 500 tokens = 500k tokens input + 500k tokens output ≈ **$1-2**

---

## ✅ Checklist

Avant utilisation:

- [ ] Compte OpenRouter créé
- [ ] Clé API obtenue
- [ ] Clé ajoutée aux paramètres
- [ ] Module agents accessible
- [ ] Modèles synchronisés
- [ ] 1er agent créé
- [ ] Modèle assigné à l'agent
- [ ] Test d'exécution réussi

---

## 🐛 Troubleshooting

### "Aucun modèle disponible"
- Clé OpenRouter manquante ou invalide
- Clique "Synchroniser Modèles"

### "Erreur d'exécution"
- Vérifier les logs: `tail -f storage/logs/php_errors.log`
- Vérifier le system prompt
- Vérifier les paramètres du modèle (température, max_tokens)

### "Modèle non trouvé"
- Synchronise à nouveau les modèles
- Vérifie que le modèle est marqué "disponible"

---

**Version:** 1.0  
**Statut:** Production Ready  
**Généré par:** Claude Code
