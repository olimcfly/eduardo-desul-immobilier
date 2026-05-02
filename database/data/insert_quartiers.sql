-- Insertion des quartiers de Bordeaux
INSERT INTO quartiers (nom, slug, ville_id, description, ordre, actif) VALUES
('Chartrons', 'chartrons', (SELECT id FROM villes WHERE slug='bordeaux'), 'Quartier historique et branché, galeries d\'art et restaurants.', 1, 1),
('Cauderan', 'cauderan', (SELECT id FROM villes WHERE slug='bordeaux'), 'Zone résidentielle calme et verdoyante, familles et couples.', 2, 1),
('Saint-Augustin', 'saint-augustin', (SELECT id FROM villes WHERE slug='bordeaux'), 'Cité ancienne avec charme, architecture haussmannienne.', 3, 1),
('Belcier', 'belcier', (SELECT id FROM villes WHERE slug='bordeaux'), 'Modernité et dynamisme, proximité centre et transports.', 4, 1),
('Bacalan', 'bacalan', (SELECT id FROM villes WHERE slug='bordeaux'), 'Renaissance urbaine, lofts et immeubles récents de standing.', 5, 1),
('Capucins', 'capucins', (SELECT id FROM villes WHERE slug='bordeaux'), 'Quartier bohème et culturel, vie de quartier authentique.', 6, 1),
('Bordeaux Maritime', 'bordeaux-maritime', (SELECT id FROM villes WHERE slug='bordeaux'), 'Secteur majeur de Bordeaux, cadre de vie prisé.', 7, 1)
ON DUPLICATE KEY UPDATE description=VALUES(description);
