-- ============================================================
-- Notifications internes
-- Branche : feat/notifications-internes
-- ============================================================

USE miniproject_version_alpha;

CREATE TABLE IF NOT EXISTS notification (
  id_notification INT UNSIGNED NOT NULL AUTO_INCREMENT,
  destinataire_role ENUM('client','fournisseur','agent') NOT NULL,
  id_destinataire INT UNSIGNED NOT NULL,
  type ENUM('FACTURE','ANOMALIE','RECLAMATION','PAIEMENT','VERIFICATION','SYSTEME') NOT NULL DEFAULT 'SYSTEME',
  titre VARCHAR(150) NOT NULL,
  message VARCHAR(500) NOT NULL,
  lien VARCHAR(255) NULL,
  lu TINYINT(1) NOT NULL DEFAULT 0,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_lecture DATETIME NULL,
  PRIMARY KEY (id_notification),
  KEY idx_notification_destinataire (destinataire_role, id_destinataire, lu),
  KEY idx_notification_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
