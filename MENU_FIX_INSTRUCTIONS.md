# 🔧 CORRECTION MENU - Instructions

## Problème Identifié

Le lien du menu `/estimation-gratuite` ne correspond à aucune page.  
**La vraie page est** : `/estimation`

### Status Current
```
Lien menu         | Page en DB    | Status
/                 | accueil       | ✅ OK
/a-propos         | a-propos      | ✅ OK
/contact          | contact       | ✅ OK
/estimation-gratuite | ❌ NONE    | 404 ERROR
```

### Solution
Remplacer dans le template `header` :
```html
<!-- ANCIEN -->
<a href="/estimation-gratuite">Estimation</a>

<!-- NOUVEAU -->
<a href="/estimation">Estimation</a>
```

---

## 🔨 COMMENT CORRIGER

### Option 1: Via phpMyAdmin (Recommandé)

1. Ouvrir **phpMyAdmin**
2. Sélectionner la base `mahe6420_site_immo`
3. Accéder à la table `templates`
4. Trouver la ligne avec `slug = 'header'`
5. Éditer le champ `content`
6. **Chercher** : `href="/estimation-gratuite"`
7. **Remplacer par** : `href="/estimation"`
8. Cliquer **Enregistrer**

### Option 2: Via SQL directe

```sql
UPDATE templates 
SET content = REPLACE(
    content, 
    'href="/estimation-gratuite"', 
    'href="/estimation"'
),
updated_at = NOW()
WHERE slug = 'header' AND is_active = 1;
```

### Option 3: Via WP-CLI (si disponible)

```bash
wp db query "UPDATE templates SET content = REPLACE(content, 'href=\"/estimation-gratuite\"', 'href=\"/estimation\"') WHERE slug = 'header';"
```

### Option 4: Via Admin Panel

Si vous avez une interface d'édition de templates dans l'admin :
1. Aller à **Templates**
2. Ouvrir **Header**
3. Trouver le lien
4. Changer `/estimation-gratuite` → `/estimation`
5. Sauvegarder

---

## ✅ Vérification

Après correction, tester :
```
GET /estimation-gratuite  → Doit rediriger vers /estimation
GET /estimation            → Doit afficher la page d'estimation
```

Or vérifier en SQL :
```sql
SELECT content FROM templates WHERE slug = 'header' LIMIT 1;
```

Le résultat doit contenir : `href="/estimation"`

---

## 📝 Commit

Une fois corrigé, vous pouvez créer un commit :

```bash
git add -A
git commit -m "fix: update menu header link /estimation-gratuite → /estimation"
git push origin main
```

---

**Date**: 2026-04-01  
**Impact**: Menu navigation fix  
**Status**: Ready to implement
