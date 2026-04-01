# Plan d’intégration — Module Welcome Page

## Objectif
Afficher une page d’accueil standard avant l’interface technique d’un module afin de cadrer l’utilisateur avec:
- HERO
- 3R (Réalité, Résultat recherché, Risque à éviter)
- MERE (Motivation, Explication, Résultat, Exercice)
- Bloc ACTION

## Implémentation actuelle
- Composant réutilisable: `components/modules/ModuleWelcomePage.php`
- Configuration par module: `config/module-welcome.php`
- Affichage conditionnel dans le routeur admin: `admin/dashboard.php`

## Modules déjà branchés (exemples)
1. `launchpad` (fondation)
2. `seo` (trafic)

## Étapes pour brancher un nouveau module
1. Ajouter une entrée dans `config/module-welcome.php`:
   - `title`
   - `subtitle`
   - `three_r` (3 items)
   - `mere` (4 items)
   - `choices` (3 à 5 items)
   - `free_text` (bool)
2. Vérifier que le slug du module correspond à la clé config.
3. Tester:
   - premier accès => welcome affichée
   - bouton « Accéder directement » => module technique
   - bouton « Relancer le guidage » => welcome réaffichée

## Persistance
- Vu / non-vu: `$_SESSION['module_welcome_seen'][<module>]`
- Contexte utilisateur: `$_SESSION['module_welcome_context'][<module>]`

## Évolutions recommandées
- Persister en base (table `advisor_module_state`) pour une conservation multi-session.
- Injecter le contexte dans les modules (filtres/onglets/priorités par défaut).
- Ajouter un interrupteur global dans les paramètres pour activer/désactiver le guidage.
