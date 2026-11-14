-- ============================================================
-- Verification annuelle - nouvelle table
-- A executer UNE FOIS dans phpMyAdmin (onglet SQL) ou en ligne de commande :
--   mysql -u root < sql/verification_annuelle.sql
-- Ne modifie aucune table existante.
-- ============================================================

USE miniproject_version_alpha;

CREATE TABLE IF NOT EXISTS consommation_annuelle (
    id_annuelle         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_client           INT UNSIGNED NOT NULL,
    id_agent            INT UNSIGNED NULL,
    annee               SMALLINT UNSIGNED NOT NULL,
    conso_eau           DECIMAL(12,2) NOT NULL,
    conso_elec          DECIMAL(12,2) NOT NULL,
    date_saisie         DATETIME NOT NULL,
    statut_verification ENUM('A_VERIFIER', 'VALIDEE', 'REJETEE') NOT NULL DEFAULT 'A_VERIFIER',
    date_verification   DATETIME NULL,
    PRIMARY KEY (id_annuelle),
    UNIQUE KEY uq_annuelle_client_annee (id_client, annee),
    CONSTRAINT fk_annuelle_client
        FOREIGN KEY (id_client) REFERENCES client (id_client)
        ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;