# 📊 Guide d'Intégration - Système de Tracking des Conversions

**Date:** 24 avril 2026  
**Version:** 1.0

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Types de conversions](#types-de-conversions)
3. [API du service](#api-du-service)
4. [Exemples d'intégration](#exemples-dintégration)
5. [Tableau de bord](#tableau-de-bord)
6. [FAQ](#faq)

---

## Vue d'ensemble

Le système `ConversionTrackingService` permet de tracker **tous les types de conversions** sans besoin de données complètes (email, téléphone, nom). C'est idéal pour:

- ✅ Estimation gratuite simple (juste clic sur bouton)
- ✅ Téléchargements (rapports, guides)
- ✅ Demandes de RDV
- ✅ Formulaires de contact
- ✅ Guides gratuits et payants

---

## Types de conversions

| Type | Code Constante | Description | Exemple |
|------|----------------|-------------|---------|
| **Estimation simple** | `TYPE_ESTIMATION_SIMPLE` | Demande d'estimation rapide | Clic sur "Estimer gratuitement" |
| **Téléchargement rapport** | `TYPE_RAPPORT_DOWNLOAD` | DL de rapport immobilier | PDF téléchargé |
| **Demande RDV** | `TYPE_RDV_DEMANDE` | Demande de rendez-vous | "Je veux un RDV" |
| **Contact formulaire** | `TYPE_CONTACT_FORM` | Message de contact | Formulaire "Nous contacter" |
| **Guide gratuit** | `TYPE_GUIDE_GRATUIT` | Guide gratuit téléchargé | Guide local, guide sectoriel |
| **Guide payant** | `TYPE_GUIDE_PAYANT` | Guide payant (7€) | Guide spécialisé acheteur/vendeur |

---

## API du service

### Enregistrer une conversion

```php
ConversionTrackingService::track(
    string $conversionType,      // Type de conversion (voir constantes ci-dessus)
    ?string $email = null,       // Email (optionnel)
    ?string $phone = null,       // Téléphone (optionnel)
    ?string $firstName = null,   // Prénom (optionnel)
    ?array $metadata = null,     // Données additionnelles (optionnel)
    ?string $description = null  // Description libre (optionnel)
): int;
```

**Retour:** ID de la conversion enregistrée

### Exemples de conversion

```php
// Estimation simple (juste un clic, pas de contact)
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_ESTIMATION_SIMPLE
);

// Téléchargement de rapport avec email
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_RAPPORT_DOWNLOAD,
    email: 'user@example.com',
    metadata: ['rapport_type' => 'marche', 'commune' => 'Bordeaux']
);

// Contact complet
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_CONTACT_FORM,
    email: 'client@example.com',
    phone: '06 12 34 56 78',
    firstName: 'Marie',
    metadata: ['message' => 'Demande de visite', 'bien_id' => 123]
);

// Guide payant téléchargé
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_GUIDE_PAYANT,
    email: 'buyer@example.com',
    firstName: 'Jean',
    metadata: ['guide_nom' => 'Guide Acheteur', 'price' => '7.00 EUR']
);
```

### Récupérer les statistiques

```php
// Tous les types de conversions
$stats = ConversionTrackingService::getTotalsByType();
// Retour:
// [
//    ['conversion_type' => 'estimation_gratuite_simple', 'total_count' => 42, ...],
//    ['conversion_type' => 'guide_payant_telechargement', 'total_count' => 15, ...],
// ]

// Conversions d'un type spécifique
$estimations = ConversionTrackingService::getStats(
    conversionType: 'estimation_gratuite_simple'
);

// Conversions sur une période
$weekStats = ConversionTrackingService::getStats(
    startDate: '2026-04-17',
    endDate: '2026-04-24'
);

// Conversions récentes
$recent = ConversionTrackingService::getRecent(
    conversionType: 'guide_payant_telechargement',
    limit: 50
);
```

---

## Exemples d'intégration

### 1. Bouton "Estimer gratuitement" (JavaScript)

```html
<button class="btn btn--primary" onclick="trackEstimation()">
    Estimer gratuitement
</button>

<script>
function trackEstimation() {
    // Envoyer le tracking
    fetch('/api/track-conversion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'estimation_gratuite_simple'
        })
    });
    
    // Rediriger vers le formulaire complet
    window.location.href = '/estimation-gratuite';
}
</script>
```

### 2. Formulaire d'estimation (PHP)

```php
<?php
// Après validation du formulaire
LeadService::capture([
    'source_type' => 'estimation',
    'email' => $email,
    'first_name' => $prenom,
    // ... autres données
]);

// Tracker aussi la conversion
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_ESTIMATION_SIMPLE,
    email: $email,
    firstName: $prenom,
    metadata: [
        'type_bien' => $_POST['type_bien'],
        'surface' => $_POST['surface']
    ]
);
```

### 3. Téléchargement de rapport (PHP)

```php
<?php
// Dans la route de téléchargement
if ($user_email) {
    ConversionTrackingService::track(
        ConversionTrackingService::TYPE_RAPPORT_DOWNLOAD,
        email: $user_email,
        firstName: $user_name,
        metadata: [
            'rapport_type' => $rapport_type,
            'commune' => 'Bordeaux'
        ]
    );
}

// Servir le fichier
header('Content-Type: application/pdf');
readfile($file_path);
exit;
```

### 4. Guide payant (Stripe webhook)

```php
<?php
// Après paiement Stripe réussi
ConversionTrackingService::track(
    ConversionTrackingService::TYPE_GUIDE_PAYANT,
    email: $customer_email,
    firstName: $customer_name,
    metadata: [
        'guide_id' => $guide_id,
        'guide_nom' => $guide_name,
        'stripe_payment_id' => $payment_intent_id,
        'amount' => $amount
    ]
);
```

### 5. Formulaire de contact (PHP)

```php
<?php
// Dans /public/pages/contact.php ou formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valider...
    
    // Envoyer l'email...
    
    // Tracker la conversion
    ConversionTrackingService::track(
        ConversionTrackingService::TYPE_CONTACT_FORM,
        email: $_POST['email'],
        phone: $_POST['telephone'],
        firstName: $_POST['nom'],
        metadata: [
            'sujet' => $_POST['sujet'],
            'message_length' => strlen($_POST['message'])
        ]
    );
}
```

---

## Tableau de bord

### 📍 Accès

**URL:** `/admin?module=crm-hub`

### Fonctionnalités

1. **Vue d'ensemble** - Statistiques en temps réel de tous les types
2. **Page détaillée** - `/admin?module=crm-hub&action=conversions`
3. **Filtrage par type** - Cliquez sur une carte pour filtrer
4. **Tableau des conversions récentes** - Emails, téléphones, dates
5. **Export possible** - (À implémenter)

### Tableau des conversions

Affiche:
- Type de conversion (avec icône)
- Nom de la personne (si fourni)
- Email (cliquable pour contacter)
- Téléphone (cliquable pour appeler)
- Page source
- Date/heure

---

## SQL - Requêtes utiles

### Nombre total de conversions par type

```sql
SELECT 
    conversion_type,
    COUNT(*) as total,
    COUNT(CASE WHEN email IS NOT NULL THEN 1 END) as with_contact
FROM conversion_tracking
GROUP BY conversion_type
ORDER BY total DESC;
```

### Conversions d'aujourd'hui

```sql
SELECT * FROM conversion_tracking
WHERE DATE(created_at) = CURDATE()
ORDER BY created_at DESC;
```

### Types avec plus de conversions

```sql
SELECT 
    conversion_type,
    COUNT(*) as count,
    MAX(created_at) as last_one
FROM conversion_tracking
GROUP BY conversion_type
HAVING count > 5
ORDER BY count DESC;
```

### Conversions avec email uniquement

```sql
SELECT * FROM conversion_tracking
WHERE email IS NOT NULL
ORDER BY created_at DESC
LIMIT 50;
```

---

## FAQ

### Q: Dois-je tracker les leads et conversions?
**R:** Non! Il y a deux systèmes:
- `LeadService` = pour les leads complètes (email + données)
- `ConversionTrackingService` = pour les conversions simples (juste le nombre)

### Q: Peut-on tracker sans email?
**R:** OUI! C'est l'intérêt du système. Email/téléphone/nom sont optionnels.

### Q: Où voir les statistiques?
**R:** Allez à `/admin?module=crm-hub` → "Suivi des conversions"

### Q: Les métadonnées JSON?
**R:** Stockez ce que vous voulez: type de bien, commune, prix, etc.

### Q: Comment exporter les données?
**R:** Pour maintenant, utilisez une requête SQL ou phpmyadmin. Export CSV à venir.

### Q: Quels types de conversions puis-je tracker?
**R:** 6 types par défaut. Vous pouvez ajouter les vôtres dans `ConversionTrackingService`.

---

## 🚀 Prochaines étapes

- [ ] Créer un endpoint API `/api/track-conversion`
- [ ] Ajouter export CSV des conversions
- [ ] Tableau de bord graphique (Chart.js)
- [ ] Alertes email pour conversions payantes
- [ ] Intégration email automatique pour conversions avec contact

---

**Questions?** Consultez le code source dans:
- `/core/services/ConversionTrackingService.php`
- `/modules/crm-hub/conversions.php`
