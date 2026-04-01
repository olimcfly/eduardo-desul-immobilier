# ÉTAPE 2 : RENDERERS - AFFICHAGE DES BLOCS

## ✅ Ce qui a été créé

### 1. Renderers de blocs (10 fichiers)
**Dossier** : `front/renderers/blocks/`

| Fichier | Type bloc | Affiche |
|---------|-----------|---------|
| hero.php | hero | Titre + sous-titre + image fond + bouton |
| features.php | features | Grille de services avec icône/titre/description |
| cta.php | cta | Section appel à l'action |
| testimonials.php | testimonials | Cartes témoignages clients |
| steps.php | steps | Processus en étapes numérotées |
| faq.php | faq | Questions/réponses en accordéon |
| form.php | form | Formulaire contact/estimation |
| map.php | map | Localisation (adresse, téléphone, email) |
| richtext.php | richtext | Contenu HTML enrichi (pages légales) |
| heading.php | heading | Titre de section |
| filters.php | filters | Filtres de recherche propriétés |

### 2. Renderer CMS principal
**Fichier** : `front/renderers/cms-new.php`

**Logique** :
1. Charge la page depuis DB
2. Vérifie si elle a un template + blocs
3. **Si oui** : Affiche les blocs via leurs renderers
4. **Si non** : Affiche le contenu HTML direct (fallback legacy)

---

## 📊 Architecture du rendu

```
Front Router (front/page.php)
  ↓
Charge page slug
  ↓
cms.php (ou cms-new.php)
  ├─ if (has template + blocks)
  │   ├─ Load page_blocks from DB
  │   ├─ Loop template.blocks
  │   │   ├─ Load block data
  │   │   └─ Include renderers/blocks/{type}.php
  │   └─ Render with Header + Footer
  │
  └─ else (no template or no blocks)
      ├─ Display page.content HTML direct
      └─ Render with Header + Footer
```

---

## 🔄 COMMENT FONCTIONNE LE RENDU

### Exemple : Page "Home" avec template

**1. Base de données :**
```
pages:
  id: 1
  slug: 'accueil'
  template: 'home'
  title: 'Accueil'
  content: (NULL ou legacy)

page_blocks:
  id: 1 | page_id: 1 | block_key: 'hero' | block_type: 'hero' | block_data: {...}
  id: 2 | page_id: 1 | block_key: 'services' | block_type: 'features' | block_data: {...}
  id: 3 | page_id: 1 | block_key: 'cta' | block_type: 'cta' | block_data: {...}
  id: 4 | page_id: 1 | block_key: 'testimonials' | block_type: 'testimonials' | block_data: {...}
```

**2. Renderer (cms-new.php) :**
```php
// Charger la page
$page = pages where slug='accueil' and status='published'
  // Result: {id: 1, slug: 'accueil', template: 'home', ...}

// Charger les blocs
$pageBlocks = page_blocks where page_id=1
  // Result: {'hero': {...}, 'services': {...}, 'cta': {...}, ...}

// Récupérer le config du template
$templateConfig = templates_config['templates']['home']
  // Result: {name: 'Accueil', blocks: {hero: {...}, services: {...}, ...}}

// Afficher dans l'ordre du template
foreach (templateConfig['blocks'] as blockKey => blockDef) {
  blockData = pageBlocks[blockKey]
  include "renderers/blocks/{blockType}.php"  // ex: hero.php
}
```

**3. Affichage final :**
```html
<!DOCTYPE html>
<html>
<head>
  <title>Accueil - Eduardo De Sul</title>
</head>
<body>
  <!-- Header depuis renderHeader() -->
  <header>...</header>

  <!-- Blocs -->
  <main>
    <section><!-- Hero --></section>
    <section><!-- Services --></section>
    <section><!-- CTA --></section>
    <section><!-- Testimonials --></section>
  </main>

  <!-- Footer depuis renderFooter() -->
  <footer>...</footer>
</body>
</html>
```

---

## ⚙️ VARIABLES DISPONIBLES DANS LES RENDERERS

Chaque renderer a accès à :
- `$blockData` : Array avec les valeurs du bloc
  ```php
  // Exemple hero block
  $blockData = [
    'title' => 'Bienvenue',
    'subtitle' => 'Conseiller immobilier à Bordeaux',
    'background_image' => 'https://...',
    'button_text' => 'Commencer',
    'button_url' => '/acheter'
  ]
  ```

- Variables globales :
  ```php
  $page         // Infos page complète
  $pageBlocks   // Tous les blocs
  $db           // Connexion DB
  ```

---

## 🎯 UTILISATION

### Pour ajouter un nouveau bloc :

1. **Créer le renderer** : `front/renderers/blocks/myblock.php`
   ```php
   <?php
   if (!isset($blockData)) $blockData = [];
   $myField = htmlspecialchars($blockData['my_field'] ?? '');
   ?>
   <section>
     <!-- Contenu -->
   </section>
   ```

2. **Ajouter à la config** : `config/templates-config.php`
   ```php
   'block_types' => [
     'myblock' => [
       'name' => 'Mon bloc',
       'renderer' => 'blocks/myblock.php'
     ]
   ],

   'templates' => [
     'home' => [
       'blocks' => [
         'myblock' => [
           'type' => 'myblock',
           'label' => 'Mon bloc',
           'fields' => [
             'my_field' => ['type' => 'text', 'label' => 'Mon champ']
           ]
         ]
       ]
     ]
   ]
   ```

3. **Créer un bloc pour une page** :
   ```php
   // En admin ou migration
   savePageBlock($db, $pageId, 'myblock', 'myblock', [
     'my_field' => 'Contenu'
   ]);
   ```

---

## ✋ AVANT DE CONTINUER

### Questions de validation :

1. **Renderers proposés** : Couvrent-ils tous vos besoins ?
   - [ ] ✅ Oui, c'est complet
   - [ ] ❌ Il manque des types de blocs

2. **Structure cms-new.php** : Vous plaît-elle ?
   - [ ] ✅ Oui, remplacer cms.php
   - [ ] ❌ Non, modifications demandées

3. **Intégration cms-new.php** :
   - [ ] ✅ Remplacer cms.php immédiatement
   - [ ] ⏳ D'abord tester les renderers
   - [ ] ❌ Garder les deux pour transition lente

---

## 🔄 ÉTAPES SUIVANTES

1. ✅ **ÉTAPE 1** : Creation DB
2. ✅ **ÉTAPE 2** : Renderers blocs (ICI)
3. ⏳ **ÉTAPE 3** : Admin UI (template selector + block editor)
4. ⏳ **ÉTAPE 4** : front/page.php si nécessaire
5. ⏳ **ÉTAPE 5** : Finalization

**Attendre validation avant ÉTAPE 3 ✋**
