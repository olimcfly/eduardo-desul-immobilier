# Guide d'installation - Ecosysteme Immo

## Dupliquer ce site pour un nouveau client

### Methode 1 : Installation automatique (recommandee)

1. **Cloner le repo**
```bash
git clone https://github.com/olimcfly/eduardo-desul-immobilier.git nom-nouveau-site
cd nom-nouveau-site
```

2. **Creer la base de donnees MySQL** sur votre hebergeur (cPanel, phpMyAdmin, etc.)
```sql
CREATE DATABASE nom_base DEFAULT CHARSET utf8mb4;
```

3. **Uploader les fichiers** sur le serveur (FTP ou SSH)

4. **Lancer l'installateur** dans le navigateur :
```
https://nouveau-domaine.fr/setup/install.php
```

5. **Remplir le formulaire** : nom du site, domaine, identifiants BDD, cles API

6. **Configurer l'email** :
```bash
cp config/smtp.example.php config/smtp.php
# Editer config/smtp.php avec les identifiants email du client
```

7. **Se connecter** : aller sur `/admin/login.php`

8. **Supprimer le fichier d'installation** :
```bash
rm setup/install.php
```

---

### Methode 2 : Installation manuelle

1. **Cloner et configurer**
```bash
git clone https://github.com/olimcfly/eduardo-desul-immobilier.git nom-nouveau-site
cd nom-nouveau-site
cp config/config.example.php config/config.php
cp config/smtp.example.php config/smtp.php
```

2. **Editer `config/config.php`** - modifier ces valeurs :

| Constante | Description | Exemple |
|-----------|-------------|---------|
| `INSTANCE_ID` | Identifiant unique | `dupont-bordeaux` |
| `SITE_TITLE` | Nom affiche | `Dupont Immobilier` |
| `SITE_DOMAIN` | Domaine | `dupont-immobilier.fr` |
| `ADMIN_EMAIL` | Email admin | `admin@dupont-immobilier.fr` |
| `DB_HOST` | Serveur BDD | `localhost` |
| `DB_NAME` | Nom base | `dupont_db` |
| `DB_USER` | Utilisateur BDD | `dupont_user` |
| `DB_PASS` | Mot de passe BDD | `motdepasse` |
| `OPENAI_API_KEY` | Cle OpenAI (optionnel) | `sk-proj-...` |
| `ANTHROPIC_API_KEY` | Cle Claude (optionnel) | `sk-ant-...` |

3. **Editer `config/smtp.php`** avec les identifiants email

4. **Creer les tables** via `/admin/install/migration_roles.php?key=install-roles-2024`

5. **Se connecter** sur `/admin/login.php`

---

## Structure des fichiers importants

```
/
├── config/
│   ├── config.example.php    <- Template (NE PAS MODIFIER)
│   ├── config.php            <- Config du site (gitignored)
│   ├── smtp.example.php      <- Template email
│   ├── smtp.php              <- Config email (gitignored)
│   └── database.php          <- Chargeur BDD
├── setup/
│   ├── install.php           <- Installateur web (supprimer apres)
│   └── dashboard.php         <- Dashboard setup
├── admin/                    <- Interface administration
├── front/                    <- Frontend public
├── core/                     <- Classes metier
├── includes/                 <- Classes utilitaires
├── logs/                     <- Logs (gitignored)
├── cache/                    <- Cache (gitignored)
└── uploads/                  <- Uploads media (gitignored)
```

## Fichiers a NE JAMAIS commiter

- `config/config.php` (contient mots de passe BDD et cles API)
- `config/smtp.php` (contient mots de passe email)
- `config/license.json`
- `logs/` et `cache/`

Ces fichiers sont dans le `.gitignore`.

## Checklist nouveau site

- [ ] Repo clone
- [ ] BDD MySQL creee
- [ ] `config/config.php` configure
- [ ] `config/smtp.php` configure
- [ ] Tables creees (via install.php ou migration)
- [ ] Premier admin cree (superuser)
- [ ] DNS pointe vers le serveur
- [ ] SSL/HTTPS actif
- [ ] `setup/install.php` supprime
- [ ] Test login admin OK
- [ ] Pages creees (accueil, contact, etc.)
- [ ] Header et footer configures
