-- 1. Copier les villes existantes de zones vers villes
INSERT INTO villes (nom, slug, code_postal, type, description, ordre, actif)
SELECT name, slug, postal_code, 'ville_couverte', name, priority,
  CASE WHEN status='active' THEN 1 ELSE 0 END
FROM zones WHERE type='city' AND status='active';

-- 2. Ajouter les villes manquantes
INSERT INTO villes (nom, slug, code_postal, type, ordre, actif) VALUES
('Saint-Médard-en-Jalles', 'saint-medard-en-jalles', '33160', 'commune_proche', 13, 1),
('Bouliac', 'bouliac', NULL, 'ville_couverte', 14, 1),
('Carbon-Blanc', 'carbon-blanc', NULL, 'ville_couverte', 15, 1),
('Blanquefort', 'blanquefort', NULL, 'ville_couverte', 16, 1),
('Ambès', 'ambes', '33810', 'commune_proche', 17, 1),
('Léognan', 'leognan', '33850', 'commune_proche', 18, 1);

-- 3. Insertion des quartiers dans quartiers
INSERT INTO quartiers (nom, slug, ville_id, description, ordre, actif)
SELECT 'Chartrons', 'chartrons', id, 'Quartier historique et branché, galeries d\'art et restaurants.', 1, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Cauderan', 'cauderan', id, 'Zone résidentielle calme et verdoyante, familles et couples.', 2, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Saint-Augustin', 'saint-augustin', id, 'Cité ancienne avec charme, architecture haussmannienne.', 3, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Belcier', 'belcier', id, 'Modernité et dynamisme, proximité centre et transports.', 4, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Bacalan', 'bacalan', id, 'Renaissance urbaine, lofts et immeubles récents de standing.', 5, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Capucins', 'capucins', id, 'Quartier bohème et culturel, vie de quartier authentique.', 6, 1
FROM villes WHERE slug='bordeaux'
UNION ALL
SELECT 'Bordeaux Maritime', 'bordeaux-maritime', id, 'Secteur majeur de Bordeaux, cadre de vie prisé.', 7, 1
FROM villes WHERE slug='bordeaux';
