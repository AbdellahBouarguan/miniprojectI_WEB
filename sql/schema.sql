-- ============================================================
-- Mini Projet 1 - Version Alpha
-- Base de donnees : miniproject_version_alpha
-- SGBD cible : MariaDB / MySQL (InnoDB, utf8mb4)
-- ============================================================

CREATE DATABASE IF NOT EXISTS miniproject_version_alpha
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE miniproject_version_alpha;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS facture;
DROP TABLE IF EXISTS anomalie;
DROP TABLE IF EXISTS photo_capteur;
DROP TABLE IF EXISTS consommation;
DROP TABLE IF EXISTS tarif;
DROP TABLE IF EXISTS client;
DROP TABLE IF EXISTS fournisseur;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- FOURNISSEUR
-- ------------------------------------------------------------
CREATE TABLE fournisseur (
  id_fournisseur INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  seuil_eau DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  seuil_elec DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_fournisseur),
  UNIQUE KEY uk_fournisseur_email (email),
  CONSTRAINT chk_fournisseur_seuils CHECK (seuil_eau >= 0 AND seuil_elec >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- CLIENT
-- ------------------------------------------------------------
CREATE TABLE client (
  id_client INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_fournisseur INT UNSIGNED NOT NULL,
  cin VARCHAR(20) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  adresse VARCHAR(255) NOT NULL,
  email VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  telephone VARCHAR(30) NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_client),
  UNIQUE KEY uk_client_cin (cin),
  UNIQUE KEY uk_client_email (email),
  KEY idx_client_fournisseur (id_fournisseur),
  CONSTRAINT fk_client_fournisseur
    FOREIGN KEY (id_fournisseur) REFERENCES fournisseur (id_fournisseur)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TARIF
-- borne_max NULL = derniere tranche
-- Les bornes sont continues : [borne_min, borne_max[
-- Exemple eau : 0-6, 6-20, 20-35, 35-infini
-- ------------------------------------------------------------
CREATE TABLE tarif (
  id_tarif INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_fournisseur INT UNSIGNED NOT NULL,
  type ENUM('EAU','ELEC') NOT NULL,
  borne_min DECIMAL(10,2) NOT NULL,
  borne_max DECIMAL(10,2) NULL,
  prix_unitaire DECIMAL(10,4) NOT NULL,
  PRIMARY KEY (id_tarif),
  UNIQUE KEY uk_tarif_fournisseur_type_borne (id_fournisseur, type, borne_min),
  KEY idx_tarif_fournisseur (id_fournisseur),
  CONSTRAINT fk_tarif_fournisseur
    FOREIGN KEY (id_fournisseur) REFERENCES fournisseur (id_fournisseur)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_tarif_bornes
    CHECK (borne_min >= 0 AND (borne_max IS NULL OR borne_max > borne_min)),
  CONSTRAINT chk_tarif_prix CHECK (prix_unitaire >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- CONSOMMATION
-- ------------------------------------------------------------
CREATE TABLE consommation (
  id_conso INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_client INT UNSIGNED NOT NULL,
  periode_mois TINYINT UNSIGNED NOT NULL,
  periode_annee SMALLINT UNSIGNED NOT NULL,
  index_eau DECIMAL(12,2) NOT NULL,
  index_elec DECIMAL(12,2) NOT NULL,
  date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  statut ENUM('EN_ATTENTE','ANOMALIE','VALIDEE','FACTUREE') NOT NULL DEFAULT 'EN_ATTENTE',
  PRIMARY KEY (id_conso),
  UNIQUE KEY uk_conso_client_periode (id_client, periode_mois, periode_annee),
  KEY idx_conso_client (id_client),
  CONSTRAINT fk_conso_client
    FOREIGN KEY (id_client) REFERENCES client (id_client)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_conso_mois CHECK (periode_mois BETWEEN 1 AND 12),
  CONSTRAINT chk_conso_index CHECK (index_eau >= 0 AND index_elec >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PHOTO_CAPTEUR
-- Exactement 2 photos par consommation : EAU + ELEC.
-- Le UNIQUE garantit au plus une photo par type.
-- ------------------------------------------------------------
CREATE TABLE photo_capteur (
  id_photo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_conso INT UNSIGNED NOT NULL,
  type ENUM('EAU','ELEC') NOT NULL,
  chemin VARCHAR(255) NOT NULL,
  date_televersement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_photo),
  UNIQUE KEY uk_photo_conso_type (id_conso, type),
  KEY idx_photo_conso (id_conso),
  CONSTRAINT fk_photo_conso
    FOREIGN KEY (id_conso) REFERENCES consommation (id_conso)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- ANOMALIE
-- type_anomalie : index inferieur au mois precedent / seuil depasse
-- champ : EAU ou ELEC
-- statut : EN_ATTENTE (choix client), CONFIRMEE, CORRIGEE
-- ------------------------------------------------------------
CREATE TABLE anomalie (
  id_anomalie INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_conso INT UNSIGNED NOT NULL,
  id_fournisseur INT UNSIGNED NOT NULL,
  type_anomalie ENUM('INDEX_INFERIEUR','SEUIL_DEPASSE') NOT NULL,
  champ ENUM('EAU','ELEC') NOT NULL,
  valeur_saisie DECIMAL(12,2) NOT NULL,
  valeur_reference DECIMAL(12,2) NOT NULL,
  statut ENUM('EN_ATTENTE','CONFIRMEE','CORRIGEE') NOT NULL DEFAULT 'EN_ATTENTE',
  valeur_corrigee DECIMAL(12,2) NULL,
  date_detection DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_resolution DATETIME NULL,
  PRIMARY KEY (id_anomalie),
  KEY idx_anomalie_conso (id_conso),
  KEY idx_anomalie_fournisseur (id_fournisseur),
  CONSTRAINT fk_anomalie_conso
    FOREIGN KEY (id_conso) REFERENCES consommation (id_conso)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_anomalie_fournisseur
    FOREIGN KEY (id_fournisseur) REFERENCES fournisseur (id_fournisseur)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_anomalie_valeurs
    CHECK (valeur_saisie >= 0 AND valeur_reference >= 0
           AND (valeur_corrigee IS NULL OR valeur_corrigee >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- FACTURE
-- Une facture par consommation : id_conso UNIQUE.
-- Les montants sont stockes pour conserver l'historique.
-- ------------------------------------------------------------
CREATE TABLE facture (
  id_facture INT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero VARCHAR(50) NOT NULL,
  id_client INT UNSIGNED NOT NULL,
  id_conso INT UNSIGNED NOT NULL,
  date_facture DATE NOT NULL,
  periode_mois TINYINT UNSIGNED NOT NULL,
  periode_annee SMALLINT UNSIGNED NOT NULL,
  conso_eau DECIMAL(10,2) NOT NULL,
  conso_elec DECIMAL(10,2) NOT NULL,
  montant_ht DECIMAL(12,2) NOT NULL,
  taux_tva DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  montant_tva DECIMAL(12,2) NOT NULL,
  montant_ttc DECIMAL(12,2) NOT NULL,
  etat ENUM('IMPAYEE','PAYEE') NOT NULL DEFAULT 'IMPAYEE',
  PRIMARY KEY (id_facture),
  UNIQUE KEY uk_facture_numero (numero),
  UNIQUE KEY uk_facture_conso (id_conso),
  KEY idx_facture_client (id_client),
  CONSTRAINT fk_facture_client
    FOREIGN KEY (id_client) REFERENCES client (id_client)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_facture_conso
    FOREIGN KEY (id_conso) REFERENCES consommation (id_conso)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_facture_mois CHECK (periode_mois BETWEEN 1 AND 12),
  CONSTRAINT chk_facture_montants
    CHECK (conso_eau >= 0 AND conso_elec >= 0
           AND montant_ht >= 0 AND montant_tva >= 0 AND montant_ttc >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
