-- Migration 002 : Ajout du champ niveau (N1-N5) et ordre sur social_posts
-- Compatible MySQL 8+

ALTER TABLE social_posts
    ADD COLUMN IF NOT EXISTS niveau ENUM('n1','n2','n3','n4','n5') DEFAULT NULL AFTER statut,
    ADD COLUMN IF NOT EXISTS ordre_sequence SMALLINT UNSIGNED DEFAULT NULL AFTER niveau;
