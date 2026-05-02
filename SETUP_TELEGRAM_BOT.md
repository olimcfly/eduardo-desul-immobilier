# 🤖 Configuration du Bot Telegram

**Date:** 24 avril 2026  
**Status:** 🟢 **PRÊT À CONFIGURER**

---

## 📋 Résumé

Un bot Telegram complet a été créé pour vous permettre de gérer votre site immobilier directement depuis votre téléphone.

**Avec ce bot, vous pouvez:**
- ✅ Voir et gérer les séquences email (activer/désactiver)
- ✅ Consulter les prospects et contacts
- ✅ Voir les statistiques globales
- ✅ Contrôler les clés API
- ✅ Tout depuis Telegram!

---

## 🚀 Étapes de Configuration

### 1️⃣ Créer votre Bot Telegram (BotFather)

1. Ouvrez Telegram et recherchez **@BotFather**
2. Envoyez `/newbot`
3. Choisissez un **nom** pour votre bot (ex: "Immobilier Bot")
4. Choisissez un **username** unique (ex: "immo_bot_2024")
5. BotFather vous donnera un **TOKEN API** (gardez-le secret!)

Exemple de token: `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`

---

### 2️⃣ Ajouter à votre fichier `.env`

Ouvrez votre fichier `.env` et ajoutez:

```env
# ============ TELEGRAM BOT ============
TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
TELEGRAM_WEBHOOK_TOKEN=random_secret_token_12345678
TELEGRAM_ADMIN_IDS=123456789,987654321
```

**Explication:**
- `TELEGRAM_BOT_TOKEN`: Le token de votre bot (de BotFather)
- `TELEGRAM_WEBHOOK_TOKEN`: Un token secret pour sécuriser le webhook (générez quelque chose de random)
- `TELEGRAM_ADMIN_IDS`: Vos ID Telegram séparés par des virgules (optionnel, pour les notifications)

**Comment trouver votre ID Telegram?**
- Envoyez `/start` à **@userinfobot**
- Il vous montrera votre ID

---

### 3️⃣ Configurer le Webhook

C'est la partie qui connecte Telegram à votre site. Exécutez cette commande:

```bash
curl -X POST https://api.telegram.org/bot[VOTRE_TOKEN]/setWebhook \
  -d url="https://votre-domaine.com/telegram-webhook.php?token=[VOTRE_WEBHOOK_TOKEN]"
```

**Remplacez:**
- `[VOTRE_TOKEN]` par votre token de BotFather
- `votre-domaine.com` par votre domaine réel
- `[VOTRE_WEBHOOK_TOKEN]` par le token secret du `.env`

**Exemple réel:**
```bash
curl -X POST https://api.telegram.org/bot123456:ABC-DEF1234/setWebhook \
  -d url="https://eduardo-desul-immobilier.fr/telegram-webhook.php?token=random_secret_token_12345678"
```

**Vérifier que c'est ok:**
```bash
curl https://api.telegram.org/bot[VOTRE_TOKEN]/getWebhookInfo
```

---

### 4️⃣ Tester votre Bot

1. Ouvrez Telegram
2. Recherchez votre bot (`@immo_bot` par exemple)
3. Envoyez `/start`
4. Vous recevrez un message d'enregistrement

### 5️⃣ Approuver l'Accès

1. Allez à: `/admin?module=telegram`
2. Vous verrez votre demande d'accès en attente
3. Cliquez sur **"✅ Approuver"**
4. Retournez sur Telegram et envoyez `/menu`

---

## 📱 Commandes du Bot

Une fois approuvé, vous avez accès à:

```
/menu              - Afficher le menu principal
/sequences         - Lister toutes les séquences
/prospects         - Voir les derniers prospects
/stats             - Voir les statistiques
/aide              - Afficher cette aide
```

---

## 🎯 Fonctionnalités Disponibles

### 📧 Gestion des Séquences
- Voir la liste des séquences
- Afficher les détails (objectif, persona, ville, stats)
- Activer/Désactiver une séquence
- Voir les statistiques en temps réel

### 👥 Gestion des Prospects
- Voir les 10 derniers prospects enregistrés
- Email, nom, et date d'ajout

### 📊 Statistiques
- Nombre de séquences actives
- Nombre total de prospects
- Nombre d'emails complétés
- Vue d'ensemble de votre système

### 🔐 Gestion des Clés API
- Consulter le statut des clés API
- Lien direct pour modifier (via le site web)

---

## 🔒 Sécurité

**Vos données sont protégées par:**
- ✅ Token secret du webhook (TELEGRAM_WEBHOOK_TOKEN)
- ✅ Système d'approbation des utilisateurs
- ✅ Logging complet des actions
- ✅ Communication chiffrée avec les serveurs Telegram
- ✅ Les clés ne sont jamais exposées en clair

**Un utilisateur doit être approuvé par un admin avant:**
- D'accéder au bot
- De faire des modifications
- De voir les données sensibles

---

## 📋 Fichiers Créés

### Migration
- `database/migrations/030_telegram_users.sql`
  - Table `telegram_users`: Utilisateurs Telegram enregistrés
  - Table `telegram_commands_log`: Historique des actions

### Services
- `core/services/TelegramBotService.php`: Logique principale du bot

### Modules Admin
- `modules/telegram/accueil.php`: Page de configuration et d'approbation

### Points d'Entrée
- `telegram-webhook.php`: Endpoint pour recevoir les messages de Telegram

---

## 🐛 Dépannage

### "Erreur: page blanche"
- Vérifiez que `TELEGRAM_BOT_TOKEN` est dans `.env`
- Vérifiez que `TelegramBotService.php` est chargé dans `bootstrap.php`

### "Le webhook ne fonctionne pas"
- Vérifiez l'URL du webhook (doit être HTTPS)
- Vérifiez le token secret
- Utilisez `getWebhookInfo` pour voir les erreurs

### "Je ne reçois pas les messages du bot"
- Vérifiez que le webhook est configuré correctement
- Attendez quelques secondes après la configuration
- Essayez `/start` again

### "Pas d'option dans le menu"
- Vérifiez que `EmailSequenceService` est chargé
- Vérifiez que la base de données est OK

---

## ✅ Checklist Finale

Avant d'utiliser:

- [ ] Bot créé avec BotFather
- [ ] Token ajouté dans `.env`
- [ ] `TELEGRAM_WEBHOOK_TOKEN` généré
- [ ] Webhook configuré via curl
- [ ] Test: `/start` fonctionne
- [ ] Accès approuvé dans `/admin?module=telegram`
- [ ] Menu disponible avec `/menu`

---

## 📞 Support

Pour les issues:
1. Vérifiez les logs du serveur
2. Utilisez `getWebhookInfo` pour diagnostiquer
3. Vérifiez que tous les services sont chargés

---

**Généré par:** Claude Code  
**Statut:** Production Ready  
**Version:** 1.0
