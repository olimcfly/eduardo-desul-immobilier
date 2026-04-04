# Superadmin — vérification locale et merge

## 1) Vérifications minimales avant push

```bash
php -l core/services/ModuleService.php
php -l modules/superadmin/accueil.php
php -l modules/superadmin/dashboard.php
php -l modules/superadmin/page_request.php
php -l modules/superadmin/toggle_module.php
php -l modules/superadmin/toggle_user.php
php -l modules/superadmin/update_profile.php
```

## 2) Vérifications Git

```bash
git status --short
git log --oneline -n 5
```

## 3) Commandes de merge (workflow recommandé)

```bash
# depuis la branche feature
git fetch origin
git rebase origin/main

# push de la branche
git push -u origin HEAD
```

Puis ouvrir la PR, attendre la CI verte, et merger avec l’option **Squash and merge** (ou la stratégie standard de votre équipe).

## 4) Vérification post-merge

```bash
git checkout main
git pull --ff-only origin main
```
