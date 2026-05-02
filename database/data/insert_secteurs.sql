-- Insertion des zones
INSERT INTO zones (nom, slug, description, ordre, actif) VALUES
('Bordeaux Métropole', 'bordeaux-metropole', 'Communes principales de la Métropole bordelaise', 1, 1),
('Communes proches', 'communes-proches', 'Communes périphériques pertinentes pour la recherche immobilière', 2, 1),
('Bordeaux Centre', 'bordeaux-centre', 'Quartiers de Bordeaux intra-muros', 3, 1);

-- Insertion des villes couvertes
INSERT INTO villes (nom, slug, code_postal, type, description, ordre, actif) VALUES
('Bordeaux', 'bordeaux', '33000', 'ville_couverte', 'Zone principale d\'intervention — quartiers historiques, résidentiels et périphérie.', 1, 1),
('Mérignac', 'merignac', '33700', 'ville_couverte', 'Deuxième ville de la Métropole, proche de l\'aéroport.', 2, 1),
('Pessac', 'pessac', '33600', 'ville_couverte', 'Ville universitaire dynamique avec de nombreuses maisons.', 3, 1),
('Talence', 'talence', '33400', 'ville_couverte', 'Secteur résidentiel prisé, proche des universités.', 4, 1),
('Floirac', 'floirac', '33270', 'ville_couverte', 'Village perché avec vues panoramiques, propriétés spacieuses et environnement verdoyant.', 5, 1),
('Lormont', 'lormont', NULL, 'ville_couverte', 'Commune verdoyante surplombant Bordeaux, cadre naturel préservé et proximité centre-ville.', 6, 1),
('Eysines', 'eysines', '33320', 'ville_couverte', 'Ville dynamique avec services et commerces, prix attractifs et bonne desserte routière.', 7, 1),
('Saint-Médard-en-Jalles', 'saint-medard', '33160', 'ville_couverte', 'Bourg résidentiel dynamique, maisons individuelles et commerces de proximité.', 8, 1),
('Villenave-d\'Ornon', 'villenave-dornon', '33140', 'ville_couverte', 'Secteur calme au sud de Bordeaux, très demandé par les familles.', 9, 1),
('Bouliac', 'bouliac', NULL, 'ville_couverte', 'Village pittoresque aux portes de Bordeaux, propriétés spacieuses et cadre naturel.', 10, 1),
('Carbon-Blanc', 'carbon-blanc', NULL, 'ville_couverte', 'Commune entre Bordeaux et Libourne, propriétés accueillantes et ambiance villageoise.', 11, 1),
('Blanquefort', 'blanquefort', NULL, 'ville_couverte', 'Village médiéval au nord, vues d\'exception et biens de prestige dans la Métropole.', 12, 1),
-- Communes proches
('Bègles', 'begles', '33130', 'commune_proche', 'Commune en plein essor, bord de Garonne.', 13, 1),
('Bruges', 'bruges', '33520', 'commune_proche', 'Commune résidentielle au nord-ouest, cadre verdoyant.', 14, 1),
('Le Bouscat', 'le-bouscat', '33110', 'commune_proche', 'Très recherché pour ses maisons et sa qualité de vie.', 15, 1),
('Ambès', 'ambes', '33810', 'commune_proche', 'Secteur pertinent pour une recherche au nord de la Métropole.', 16, 1),
('Léognan', 'leognan', '33850', 'commune_proche', 'Village viticole recherché au sud de la Métropole.', 17, 1);

-- Insertion des quartiers de Bordeaux
INSERT INTO quartiers (nom, slug, ville_id, description, ordre, actif) VALUES
('Chartrons', 'chartrons', (SELECT id FROM villes WHERE slug='bordeaux'), 'Quartier historique et branché, galeries d\'art et restaurants.', 1, 1),
('Cauderan', 'cauderan', (SELECT id FROM villes WHERE slug='bordeaux'), 'Zone résidentielle calme et verdoyante, familles et couples.', 2, 1),
('Saint-Augustin', 'saint-augustin', (SELECT id FROM villes WHERE slug='bordeaux'), 'Cité ancienne avec charme, architecture haussmannienne.', 3, 1),
('Belcier', 'belcier', (SELECT id FROM villes WHERE slug='bordeaux'), 'Modernité et dynamisme, proximité centre et transports.', 4, 1),
('Bacalan', 'bacalan', (SELECT id FROM villes WHERE slug='bordeaux'), 'Renaissance urbaine, lofts et immeubles récents de standing.', 5, 1),
('Capucins', 'capucins', (SELECT id FROM villes WHERE slug='bordeaux'), 'Quartier bohème et culturel, vie de quartier authentique.', 6, 1),
('Bordeaux Maritime', 'bordeaux-maritime', (SELECT id FROM villes WHERE slug='bordeaux'), 'Secteur majeur de Bordeaux, cadre de vie prisé.', 7, 1);

-- Insertion des relations villes-zones
INSERT INTO villes_zones (ville_id, zone_id) SELECT v.id, z.id FROM villes v, zones z
WHERE (v.type='ville_couverte' AND z.slug='bordeaux-metropole')
   OR (v.type='commune_proche' AND z.slug='communes-proches')
   OR (v.slug='bordeaux' AND z.slug='bordeaux-centre');
