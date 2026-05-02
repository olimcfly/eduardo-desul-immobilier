IMMO Local+ — extensions WordPress (un plugin par module admin)
================================================================

Contenu
-------
Dossiers : immolocal-module-<slug>/ nomme un module (ex. dashboard, attirer).
Chaque dossier contient un fichier PHP au meme nom (WordPress exige ce fichier a la racine du plugin).

Installation
------------
1. Copier le contenu de ce repertoire dans wp-content/plugins/ (ou deployer un sous-dossier).
2. Dans WordPress : Extensions > Extensions installees > activer les modules souhaites.
   - Le menu d'administration "IMMO Local+" regroupe les modules actives.
3. Ouvrir IMMO Local+ > Tableau de bord et saisir l'URL de base de l'application PHP (sans /admin final),
   par ex. https://votre-domaine.tld
4. Les autres entrees du sous-menu ouvrent une page avec un lien vers /admin?module=<slug> sur cette base.

Regenerer les plugins
----------------------
Apres modification de config/admin_module_plugin_meta.php :
  php scripts/wordpress/build-wp-module-plugins.php

Remarque
--------
Ces plugins ne remplacent pas l'outil PHP : ils fournissent des raccourcis vers l'admin existante.
Le site public (theme, pages, plan du site) reste gere par WordPress independamment.
