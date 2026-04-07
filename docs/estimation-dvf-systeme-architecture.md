# Système d’estimation immobilière (DVF) — Proposition d’architecture

## 1) Avis sur le concept

Le concept est excellent pour une stratégie **lead generation + qualification commerciale** :
- La page “estimation instantanée” réduit la friction et capte un lead tôt.
- La page “prendre rendez-vous” convertit les leads chauds vers un échange humain (plus de closing).
- L’adossement à DVF rend l’argument commercial crédible (“basé sur des ventes réelles”).

Point clé : une estimation instantanée doit rester **probabiliste** et explicitement présentée comme indicative (ce que vous prévoyez déjà).

---

## 2) Architecture recommandée

## 2.1 Vue d’ensemble

- **Front public**
  - `/estimation-instantanee` (formulaire court + résultat)
  - `/prendre-rendez-vous` (formulaire complet)
- **API back**
  - `POST /api/estimate/instant`
  - `POST /api/estimate/request`
  - `GET /api/places/autocomplete` (proxy Google, optionnel)
- **Back-office admin**
  - Import DVF
  - Historique imports
  - Demandes estimation
  - Stats & carte Google Maps

## 2.2 Couches techniques

1. **Présentation** : pages + JS (autocomplete, validation, affichage résultat).
2. **Service métier d’estimation** : moteur de recherche comparables + règles de fiabilité.
3. **Data access** : tables DVF normalisées + index géo/logiques.
4. **Pipeline import** : ingestion par lots, enrichissement, quality checks, historisation.

## 2.3 Découpage modulaire conseillé

- `GeoService` : normalisation adresse + géocodage (Google Places/Geocoding).
- `DvfImportService` : parsing CSV DVF, nettoyage, upsert.
- `ComparableSearchService` : filtrage comparables + élargissement progressif.
- `ValuationService` : calcul basse/médiane/haute + score de confiance.
- `EstimationLeadService` : enregistrement demande + statut CRM.

---

## 3) Schéma de données (proposé)

## 3.1 `dvf_mutations`
- `id` (PK)
- `source` (dvf)
- `mutation_id` (identifiant DVF)
- `mutation_date` (DATE)
- `nature_mutation`
- `valeur_fonciere` (DECIMAL)
- `created_at`

## 3.2 `dvf_biens`
- `id` (PK)
- `mutation_id` (FK -> dvf_mutations.id)
- `type_local` (Appartement/Maison/Local/…)
- `surface_reelle_bati`
- `nombre_pieces_principales`
- `surface_terrain`
- `code_postal`
- `code_commune`
- `commune`
- `adresse_norm` (texte normalisé)
- `latitude`, `longitude`
- `prix_m2_calcule` (généré)
- `is_outlier` (bool)
- `quality_score` (0-100)
- `created_at`

## 3.3 `dvf_import_runs`
- `id` (PK)
- `source_file`
- `started_at`, `finished_at`
- `status` (running/success/failed/partial)
- `rows_read`, `rows_inserted`, `rows_updated`, `rows_rejected`
- `error_log` (JSON)
- `checksum`

## 3.4 `estimation_requests`
- `id` (PK)
- `request_type` (instant / rdv)
- `full_name`, `email`, `phone`
- `property_type`, `surface`, `rooms`
- `address_raw`, `address_norm`, `lat`, `lng`
- `estimated_low`, `estimated_median`, `estimated_high`
- `comparables_count`
- `confidence_score`, `confidence_level`
- `status` (new/contacted/qualified/closed)
- `advisor_id` (nullable)
- `created_at`, `updated_at`

## 3.5 `estimation_calculation_logs`
- `id` (PK)
- `request_id` (FK)
- `step_name`
- `filters_json`
- `comparables_found`
- `decision_json`
- `created_at`

---

## 4) Logique d’import DVF

## 4.1 Pipeline recommandé
1. Upload fichier(s) DVF (admin).
2. Création d’un `dvf_import_runs` (status=running).
3. Parsing streaming (éviter chargement mémoire complet).
4. Nettoyage + mapping colonnes + typage.
5. Déduplication (clé métier mutation + bien).
6. Calcul champs dérivés (`prix_m2_calcule`).
7. Contrôles qualité (prix/surface/date/cohérence).
8. Upsert batch en base.
9. Marquage outliers préliminaires.
10. Mise à jour status import + stats.

## 4.2 Règles de nettoyage minimales
- ignorer `surface_reelle_bati <= 0` pour biens bâtis.
- ignorer `valeur_fonciere <= 0`.
- normaliser `type_local` (table de mapping).
- harmoniser commune/CP et conserver codes INSEE.
- gérer accents/casse pour champs textuels.

## 4.3 Performance
- index sur `(code_commune, type_local, mutation_date)`.
- index sur `(latitude, longitude)` ou index spatial si possible.
- import par chunks (ex: 5k lignes).
- job asynchrone + barre de progression côté admin.

---

## 5) Logique de calcul d’estimation

## 5.1 Entrées minimales
- lieu normalisé + coordonnées
- type de bien
- surface

## 5.2 Algorithme (version robuste)

1. **Univers initial** : ventes des 24 derniers mois.
2. **Filtre type** : même `type_local`.
3. **Filtre géo initial** : rayon 800m (urbain) / 2km (périurbain).
4. **Filtre surface** : ±15% autour de la surface cible.
5. **Nettoyage outliers** : IQR ou winsorization sur `prix_m2`.
6. **Seuil comparables** : min 12.
7. Si insuffisant -> **élargissement progressif** (rayon, surface, période).
8. Calcul du `prix_m2_median` (pivot robuste).
9. Ajustement simple (pièces, terrain, ancienneté si dispo).
10. Projection valeur = `prix_m2 * surface` -> basse / médiane / haute.

## 5.3 Fourchette proposée
- médiane = `P50`
- basse = `P35` (ou `P50 - 0.6*IQR`)
- haute = `P65` (ou `P50 + 0.6*IQR`)

---

## 6) Garde-fous métier

- **Min comparables absolu**: < 8 => blocage estimation instantanée.
- **Fiabilité faible** (8–11 comparables) => résultat masqué ou fortement averti.
- **Dispersion trop forte** (`IQR/P50 > 35%`) => estimation non fiable.
- **Surface hors plage métier** (ex: < 9m² ou > 500m²) => redirection RDV.
- **Type non couvert** (immeuble, bien atypique, mixte) => RDV obligatoire.
- **Adresse incertaine** (géocodage faible confiance) => pas d’instantané.
- **Date trop ancienne** (aucun comparable < 36 mois) => blocage.

Message utilisateur recommandé :
> “Nous n’avons pas assez de ventes comparables récentes pour produire une estimation fiable. Un conseiller peut vous fournir une estimation affinée gratuitement.”

---

## 7) Structure UI (front)

## 7.1 Page “Estimation instantanée”

### Bloc formulaire
- Champ lieu (Google Places Autocomplete)
- Type de bien (chips)
- Surface (input numérique)
- CTA “Estimer maintenant”

### Bloc résultat
- Estimation basse / médiane / haute
- Nombre de comparables
- Badge fiabilité (élevée / moyenne / faible)
- Mention légale “estimation indicative”
- CTA primaire “Prendre rendez-vous avec un conseiller”

### UX conseillée
- loader court + étapes (“analyse des comparables…”)
- possibilité d’éditer les entrées rapidement
- tracking analytics (submit, résultat affiché, clic CTA RDV)

## 7.2 Page “Prendre rendez-vous”

Champs enrichis :
- identité + contacts
- adresse complète
- type, surface, pièces, état, étage, année
- horizon de vente, motivation, disponibilité
- consentements RGPD

---

## 8) Structure admin

## 8.1 Menu
1. **DVF Imports**
2. **Historique imports**
3. **Demandes d’estimation**
4. **Statistiques**
5. **Carte des demandes**

## 8.2 Écrans

### DVF Imports
- upload fichier
- lancer import
- suivi en temps réel
- rapport fin d’import

### Historique imports
- tableau des runs
- statut, durée, volume, erreurs
- téléchargement log erreur

### Demandes estimation
- table filtrable (ville/date/type/statut)
- vue détail demande
- actions statut (nouveau → contacté → converti)

### Statistiques
- volume demandes/jour
- taux de passage instantané -> RDV
- taux de conversion commercial
- distribution géographique et par type

### Carte (Google Maps)
- points agrégés par zone (cluster)
- filtres ville/date/type/statut
- clic point => fiche demande

---

## 9) Meilleure intégration Google API

## 9.1 APIs à activer
- Places API (Autocomplete + Place Details)
- Geocoding API (fallback)
- Maps JavaScript API (admin map)

## 9.2 Bonnes pratiques
- restreindre clé par domaine + API + quota.
- utiliser **session token** Places pour coût maîtrisé.
- stocker `place_id`, adresse formatée, lat/lng, composants (ville, CP, INSEE si possible via mapping).
- passer par proxy backend pour masquer la clé serveur et centraliser logs.
- fallback si quota atteint : champ texte + validation manuelle.

## 9.3 Données à conserver après sélection
- `place_id`
- `formatted_address`
- `lat`, `lng`
- `city`, `postal_code`, `country`
- `geometry_confidence` (interne)

---

## 10) Risques et optimisations

## 10.1 Risques
- **Juridique/commercial** : confusion entre estimation indicative et expertise.
- **Qualité donnée DVF** : adresses incomplètes, délais de mise à jour.
- **Biais géographiques** : zones peu liquides avec peu de comparables.
- **Coûts API Google** : si trafic élevé sans quotas.

## 10.2 Optimisations
- cache résultats par cellule géo + type + tranche surface (TTL court).
- pré-agrégations `prix_m2` par micro-zone mensuelle.
- scoring de confiance exposé en interne.
- A/B test du wording CTA RDV.
- monitoring : temps de réponse, taux de blocage, taux de conversion RDV.

## 10.3 KPI de pilotage
- taux d’obtention d’estimation instantanée
- part des estimations bloquées (non fiables)
- conversion instantanée -> RDV
- conversion RDV -> mandat
- précision ex post (écart estimation vs prix de vente final)
