-- ============================================================
-- Migration 001 - Rôle Agent et consommations annuelles
-- Mini Projet 1 - Version finale
-- Module : Agent (import annuel)
-- ============================================================
-- L'agent effectue une tournée annuelle et dépose un fichier
-- Consommation_Annuelle.txt. Les relevés sont stockés dans
-- conso_annuelle, une seule ligne par client et par année.
-- ============================================================

USE miniproject_version_alpha;

-- ------------------------------------------------------------
-- AGENT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agent (
  id_agent INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  telephone VARCHAR(30) NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_agent),
  UNIQUE KEY uk_agent_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- CONSO_ANNUELLE
-- Relevé de la tournée annuelle de l'agent.
-- UNIQUE(id_client, annee) : un seul relevé annuel par client,
-- ce qui sert aussi de détection des doublons à l'import.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS conso_annuelle (
  id_conso_annuelle INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_client INT UNSIGNED NOT NULL,
  id_agent INT UNSIGNED NOT NULL,
  conso_eau DECIMAL(12,2) NOT NULL,
  conso_elec DECIMAL(12,2) NOT NULL,
  annee SMALLINT UNSIGNED NOT NULL,
  date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_conso_annuelle),
  UNIQUE KEY uk_conso_annuelle_client_annee (id_client, annee),
  KEY idx_conso_annuelle_agent (id_agent),
  CONSTRAINT fk_conso_annuelle_client
    FOREIGN KEY (id_client) REFERENCES client (id_client)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_conso_annuelle_agent
    FOREIGN KEY (id_agent) REFERENCES agent (id_agent)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_conso_annuelle_valeurs
    CHECK (conso_eau >= 0 AND conso_elec >= 0),
  CONSTRAINT chk_conso_annuelle_annee
    CHECK (annee BETWEEN 2000 AND 2100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
