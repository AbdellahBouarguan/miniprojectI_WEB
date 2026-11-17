-- ============================================================
-- Migration 002 - Reclamations et paiement
-- Mini Projet 1 - Version finale
-- ============================================================
-- A executer UNE FOIS sur une base existante :
--   mysql -u root < sql/migrations/002_reclamation_paiement.sql
-- Pour une installation neuve, utiliser sql/schema.sql.
-- ============================================================

USE miniproject_version_alpha;

ALTER TABLE facture
  ADD COLUMN IF NOT EXISTS date_paiement DATETIME NULL,
  ADD COLUMN IF NOT EXISTS mode_paiement VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS reference_paiement VARCHAR(100) NULL;

CREATE TABLE IF NOT EXISTS reclamation (
  id_reclamation INT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero VARCHAR(30) NOT NULL,
  id_client INT UNSIGNED NOT NULL,
  id_facture INT UNSIGNED NULL,
  type ENUM('FUITE_EXTERNE','FUITE_INTERNE','FACTURE','AUTRE') NOT NULL,
  description TEXT NOT NULL,
  piece_jointe VARCHAR(255) NULL,
  statut ENUM('OUVERTE','EN_COURS','RESOLUE','REJETEE') NOT NULL DEFAULT 'OUVERTE',
  reponse TEXT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_resolution DATETIME NULL,
  PRIMARY KEY (id_reclamation),
  UNIQUE KEY uk_reclamation_numero (numero),
  KEY idx_reclamation_client (id_client),
  KEY idx_reclamation_facture (id_facture),
  CONSTRAINT fk_reclamation_client FOREIGN KEY (id_client) REFERENCES client (id_client)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reclamation_facture FOREIGN KEY (id_facture) REFERENCES facture (id_facture)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
