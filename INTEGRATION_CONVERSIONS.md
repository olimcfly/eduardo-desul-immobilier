# Intégration du Tracking des Conversions

**Date:** 24 avril 2026

## ✅ Intégrations complétées

### 1. Formulaire d'estimation gratuite
**Fichier:** `/public/pages/capture/estimation-gratuite.php` (ligne 32)
- **Type:** `TYPE_ESTIMATION_SIMPLE`
- **Données capturées:** Email, prénom, type bien, surface, pièces, demande RDV
- **Déclencheur:** Après validation et avant redirection `/merci`

### 2. Formulaire de contact
**Fichier:** `/public/pages/core/contact.php` (ligne 39)
- **Type:** `TYPE_CONTACT_FORM`
- **Données capturées:** Email, prénom, sujet, longueur du message
- **Déclencheur:** Après validation et avant redirection `/merci`

### 3. Demande de rendez-vous (estimation affinée)
**Fichier:** `/public/pages/conversion/prendre-rendez-vous.php` (ligne 49)
- **Type:** `TYPE_RDV_DEMANDE`
- **Données capturées:** Email, prénom, type bien, adresse, surface, créneau
- **Déclencheur:** Après LeadService et avant redirection `/merci`

### 4. Téléchargement de guide gratuit
**Fichier:** `/public/pages/capture/guide-offert.php` (ligne 24)
- **Type:** `TYPE_GUIDE_GRATUIT`
- **Données capturées:** Email, prénom, profil (acheteur/vendeur/investisseur)
- **Déclencheur:** Après LeadService et avant redirection `/merci`

### 5. Téléchargement de rapport (API AJAX)
**Fichier:** `/public/api/estimation/convert.php` (ligne 106)
- **Type:** `TYPE_RAPPORT_DOWNLOAD`
- **Données capturées:** Email, prénom, estimations (low/med/high), type bien
- **Déclencheur:** Quand action_type = `email_report`

---

## 📋 Tableau de suivi

| Type | Formulaire | Intégration | Statut |
|------|-----------|------------|--------|
| Estimation simple | /estimation-gratuite | LeadService + ConversionTracking | ✅ Complet |
| Contact formulaire | /contact | LeadService + ConversionTracking | ✅ Complet |
| RDV demande | /prendre-rendez-vous | LeadService + ConversionTracking | ✅ Complet |
| Guide gratuit | /guide-offert | LeadService + ConversionTracking | ✅ Complet |
| Rapport DL | /api/estimation/convert | LeadService + ConversionTracking | ✅ Complet |
| Guide payant (7€) | À implémenter | — | ⏳ À faire |

---

## ⏳ À implémenter : Guides payants

### Intégration Stripe
Les guides payants (7€) devront être intégrés via webhook Stripe:

```php
// Dans un webhooks handler Stripe futur
if ($event->type === 'payment_intent.succeeded') {
    ConversionTrackingService::track(
        ConversionTrackingService::TYPE_GUIDE_PAYANT,
        email: $customer->email,
        firstName: $customer->name,
        metadata: [
            'guide_id' => $guide_id,
            'guide_name' => $guide_name,
            'stripe_payment_id' => $payment_intent_id,
            'amount' => $amount,
        ]
    );
}
```

---

## 🧪 Test de validation

Pour vérifier que le système fonctionne:

1. **Allez sur** `/estimation-gratuite`
2. **Remplissez le formulaire** d'estimation
3. **Vérifiez dans l'admin** `/admin?module=crm-hub&action=conversions`
4. Vous devriez voir une nouvelle ligne dans le tableau des conversions récentes

---

## 📊 Visualisation

Les conversions sont visibles à deux endroits:

1. **Dashboard rapide:** `/admin?module=crm-hub`
   - Cartes de synthèse par type
   - Total général en temps réel

2. **Dashboard détaillé:** `/admin?module=crm-hub&action=conversions`
   - Filtrage par type
   - Tableau des conversions récentes
   - Détails des leads (email, téléphone, date)

---

## 🔧 Modification futur

Pour ajouter un nouveau type de conversion:

1. Ajouter une constante dans `ConversionTrackingService`:
   ```php
   public const TYPE_MON_CONVERSION = 'mon_type_conversion';
   ```

2. Appeler le tracking dans votre formulaire:
   ```php
   ConversionTrackingService::track(
       ConversionTrackingService::TYPE_MON_CONVERSION,
       email: $email,
       firstName: $firstName,
       metadata: ['custom_data' => $value]
   );
   ```

3. Ajouter un label dans `/modules/crm-hub/conversions.php`:
   ```php
   'mon_type_conversion' => [
       'label' => 'Mon Conversion',
       'icon' => '📌',
       'color' => '#...',
       'desc' => 'Description'
   ],
   ```
