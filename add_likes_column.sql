-- Ajouter la colonne likes à la table publications
ALTER TABLE publications ADD COLUMN likes INT DEFAULT 0;