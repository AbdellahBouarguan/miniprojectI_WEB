-- ============================================================
-- Migration 003 - Quartiers et contrats
-- Mini Projet 1 - Version finale
-- ============================================================
-- A executer UNE FOIS sur une base existante :
--   mysql -u root < sql/migrations/003_quartier_contrat.sql
-- Pour une installation neuve, utiliser sql/schema.sql.
-- ============================================================

USE miniproject_version_alpha;

-- ------------------------------------------------------------
-- QUARTIER
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quartier (
  id_quartier INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  type_activite ENUM('RESIDENTIEL','COMMERCIAL','INDUSTRIEL') NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_quartier),
  UNIQUE KEY uk_quartier_nom_activite (nom, type_activite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table creee par une ancienne version de la branche quartier :
-- on complete les colonnes manquantes si necessaire.
ALTER TABLE quartier
  ADD COLUMN IF NOT EXISTS date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE quartier
  ADD UNIQUE KEY IF NOT EXISTS uk_quartier_nom_activite (nom, type_activite);

-- ------------------------------------------------------------
-- CONTRAT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contrat (
  id_contrat INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_client INT UNSIGNED NOT NULL,
  id_quartier INT UNSIGNED NOT NULL,
  numero_contrat VARCHAR(50) NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NULL,
  statut ENUM('ACTIF','RESILIE') NOT NULL DEFAULT 'ACTIF',
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_contrat),
  UNIQUE KEY uk_contrat_numero (numero_contrat),
  KEY idx_contrat_client (id_client),
  KEY idx_contrat_quartier (id_quartier),
  CONSTRAINT fk_contrat_client
    FOREIGN KEY (id_client) REFERENCES client (id_client)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_contrat_quartier
    FOREIGN KEY (id_quartier) REFERENCES quartier (id_quartier)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_contrat_dates
    CHECK (date_fin IS NULL OR date_fin >= date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- CONSOMMATION : rattachement au contrat
-- ------------------------------------------------------------
ALTER TABLE consommation
  ADD COLUMN IF NOT EXISTS id_contrat INT UNSIGNED NULL AFTER id_client;

-- Un quartier et un contrat par defaut pour les donnees existantes.
INSERT INTO quartier (nom, type_activite)
SELECT 'Non renseigné', 'RESIDENTIEL'
WHERE NOT EXISTS (
  SELECT 1 FROM quartier WHERE nom = 'Non renseigné' AND type_activite = 'RESIDENTIEL'
);

SET @id_quartier_defaut = (
  SELECT id_quartier
  FROM quartier
  WHERE nom = 'Non renseigné' AND type_activite = 'RESIDENTIEL'
  LIMIT 1
);

INSERT INTO contrat (id_client, id_quartier, numero_contrat, date_debut)
SELECT c.id_client, @id_quartier_defaut, CONCAT('CTR-', c.id_client), '2026-01-01'
FROM client c
WHERE NOT EXISTS (
  SELECT 1 FROM contrat ct WHERE ct.id_client = c.id_client
);

UPDATE consommation c
JOIN contrat ct ON ct.id_client = c.id_client
SET c.id_contrat = ct.id_contrat
WHERE c.id_contrat IS NULL;

ALTER TABLE consommation
  MODIFY id_contrat INT UNSIGNED NOT NULL;

ALTER TABLE consommation
  DROP INDEX uk_conso_client_periode;

ALTER TABLE consommation
  ADD UNIQUE KEY uk_conso_contrat_periode (id_contrat, periode_mois, periode_annee);

ALTER TABLE consommation
  ADD CONSTRAINT fk_conso_contrat
    FOREIGN KEY (id_contrat) REFERENCES contrat (id_contrat)
    ON UPDATE CASCADE ON DELETE RESTRICT;
