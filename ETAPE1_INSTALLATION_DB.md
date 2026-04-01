# ÉTAPE 1 : INSTALLATION BASE DE DONNÉES

## ✅ Ce qui a été créé

### 1. Migration SQL
**Fichier** : `database/migrations/20260401_page_blocks_structure.sql`

**Tables créées** :
- `page_blocks` - Stocke les valeurs des blocs (modifiables par client)
- `template_definitions` - Documentation des templates (optionnel)

**Colonnes ajoutées** :
- `pages.template` - Clé du template utilisé
- `pages.website_id` - Support futur multi-websites

### 2. Configuration PHP
**Fichier** : `config/templates-config.php`

**Contient** :
- `templates` - Définition de 6 templates (home, acheter, vendre, landing, legal, contact)
- `block_types` - Types de blocs disponibles (hero, features, cta, form, map, etc.)
- Pour chaque template :
  - `name`, `description`, `icon`
  - Liste des blocs avec leurs champs
  - Champs modifiables par client (type, label, obligatoire/optionnel)

### 3. Helper PHP
**Fichier** : `includes/helpers/templates-helper.php`

**Fonctions disponibles** :
```php
getTemplatesConfig()           // Charge config
getAvailableTemplates()        // Liste templates
getTemplate($templateKey)      // Détails template
getPageBlocks($db, $pageId)    // Charge tous blocs page
getPageBlock($db, $pageId, $blockKey)  // Un bloc
savePageBlock(...)             // Sauvegarde bloc
deletePageBlock(...)           // Supprime bloc
initializePageBlocks(...)      // Crée blocs vides pour nouveau page
validateBlockData(...)         // Valide données bloc
getPageWithBlocks(...)         // Page complète + blocs
```

---

## 📋 PROCÉDURE D'INSTALLATION

### A) Exécuter la migration SQL

```bash
# Option 1 : Via MySQL CLI
mysql -h [HOST] -u [USER] -p[PASS] [DB] < database/migrations/20260401_page_blocks_structure.sql

# Option 2 : Via PHP (auto-run au démarrage de l'admin)
```

### B) Inclure le helper dans les fichiers qui l'utilisent

```php
// Dans front/page.php, admin/modules/content/pages/index.php, etc.
require_once ROOT_PATH . '/includes/helpers/templates-helper.php';
```

---

## 📊 STRUCTURE DB (Schéma)

### Table `page_blocks`
```sql
+--------------+------------------+
| Colonne      | Type             |
+--------------+------------------+
| id           | INT UNSIGNED     | PK
| page_id      | INT UNSIGNED     | FK pages(id)
| block_key    | VARCHAR(100)     | Ex: "hero", "services"
| block_type   | VARCHAR(50)      | Ex: "hero", "features"
| block_data   | JSON             | {"title": "...", "image": "...", etc}
| block_order  | INT              | Ordre dans page
| is_visible   | TINYINT(1)       | Visible/Masqué
| created_at   | DATETIME         |
| updated_at   | DATETIME         |
+--------------+------------------+
```

**Indexes** :
- PRIMARY : `id`
- UNIQUE : `(page_id, block_key)` - Un seul bloc "hero" par page
- FOREIGN KEY : `page_id` → `pages(id)` ON DELETE CASCADE

---

## 🎯 FLUX DE DONNÉES

### Création d'une page
```
Admin → Choisir template "home"
  ↓
initializePageBlocks($db, $pageId, 'home')
  ↓
Crée 4 blocs vides :
  - page_blocks(page_id=5, block_key="hero", block_type="hero", block_data={})
  - page_blocks(page_id=5, block_key="services", block_type="features", block_data={})
  - page_blocks(page_id=5, block_key="cta", block_type="cta", block_data={})
  - page_blocks(page_id=5, block_key="testimonials", block_type="testimonials", block_data={})
```

### Modification d'un bloc
```
Admin → Édite bloc "services"
  ↓
savePageBlock($db, 5, 'services', 'features', {
  "section_title": "Nos services",
  "items": [
    {"icon": "🏠", "title": "Vendre", "description": "..."},
    {"icon": "🔑", "title": "Acheter", "description": "..."}
  ]
})
  ↓
UPDATE page_blocks SET block_data = JSON, updated_at = NOW()
```

### Affichage d'une page (frontend)
```
front/page.php
  ↓
getPageWithBlocks($db, 'accueil')
  ↓
Retourne :
  {
    id: 1,
    slug: 'accueil',
    template: 'home',
    blocks: {
      hero: {type: 'hero', data: {...}, visible: true},
      services: {type: 'features', data: {...}, visible: true},
      ...
    },
    template_config: {name: 'Accueil', blocks: {...}}
  }
  ↓
Rendu dans renderers/cms.php (ÉTAPE 3)
```

---

## ✋ AVANT DE PASSER À ÉTAPE 2

### Vérifications à faire

- [ ] Migration SQL exécutée sans erreurs
- [ ] Tables `page_blocks` et `template_definitions` créées
- [ ] Colonnes `template` et `website_id` ajoutées à `pages`
- [ ] Fichier `config/templates-config.php` est accessible
- [ ] Helper `includes/helpers/templates-helper.php` importable

### Données de test (optionnel)
```php
// Test dans un fichier PHP
require_once 'config/templates-config.php';
require_once 'includes/helpers/templates-helper.php';

$config = getTemplatesConfig();
echo "Templates disponibles: ";
print_r(array_keys($config['templates']));
// Affiche: ["home", "acheter", "vendre", "landing", "legal", "contact"]
```

---

## 📝 NOTES

- **`page_blocks` vs `content` colonne** : Les blocs sont stockés en `page_blocks`. La colonne `content` dans `pages` sera ignorée pour les pages templates.
- **Backward compatibility** : Pages existantes sans template continuent à utiliser colonne `content`
- **JSON en DB** : Plus flexible que colonnes séparées. Client peut voir/modifier via l'admin UI
- **Validation** : Faire côté admin avant savePageBlock()

---

## 🔄 ÉTAPES SUIVANTES

1. ✅ **ÉTAPE 1** : Creation DB (FAIT - ici)
2. ⏳ **ÉTAPE 2** : Définir blocs de chaque template (config statique)
3. ⏳ **ÉTAPE 3** : Mettre à jour renderers/cms.php pour charger blocs depuis DB
4. ⏳ **ÉTAPE 4** : Créer/améliorer UI admin (template selector + block editor)
5. ⏳ **ÉTAPE 5** : Mettre à jour front/page.php si nécessaire

**Attendre validation avant ÉTAPE 2 ✋**
