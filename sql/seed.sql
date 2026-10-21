-- ============================================================
-- Mini Projet 1 - Version Alpha
-- Donnees de depart : fournisseur, clients, tarifs
-- Mots de passe :
--   fournisseur@example.ma / fournisseur123
--   client1@example.ma     / client123
--   client2@example.ma     / client123
--   client3@example.ma     / client123
-- ============================================================

USE miniproject_version_alpha;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM facture;
DELETE FROM anomalie;
DELETE FROM photo_capteur;
DELETE FROM consommation;
DELETE FROM tarif;
DELETE FROM client;
DELETE FROM fournisseur;

ALTER TABLE facture AUTO_INCREMENT = 1;
ALTER TABLE anomalie AUTO_INCREMENT = 1;
ALTER TABLE photo_capteur AUTO_INCREMENT = 1;
ALTER TABLE consommation AUTO_INCREMENT = 1;
ALTER TABLE tarif AUTO_INCREMENT = 1;
ALTER TABLE client AUTO_INCREMENT = 1;
ALTER TABLE fournisseur AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- FOURNISSEUR
-- ------------------------------------------------------------
INSERT INTO fournisseur
  (id_fournisseur, nom, email, mot_de_passe, seuil_eau, seuil_elec)
VALUES
  (1,
   'Regie Eau et Electricite',
   'fournisseur@example.ma',
   '$2y$12$ddK9Wz7XYDG06PasI8DiweTxv2arw.fItvr0dlYJAfP1CP4cJSqni',
   100.00,
   1000.00);

-- ------------------------------------------------------------
-- CLIENTS
-- Mot de passe : client123
-- ------------------------------------------------------------
INSERT INTO client
  (id_client, id_fournisseur, cin, nom, prenom, adresse, email, mot_de_passe, telephone)
VALUES
  (1, 1, 'AB123456', 'El Amrani', 'Ahmed', '12 Rue de Tetouan, Tetouan',
   'client1@example.ma',
   '$2y$12$ZXH5ePnoMjQU8D96pUYpFOH83RdBKgBskLtgbsGm.IK9w1zYE6Ppu',
   '0600000001'),
  (2, 1, 'CD789012', 'Bennis', 'Sara', '45 Avenue Mohammed V, Tetouan',
   'client2@example.ma',
   '$2y$12$ZXH5ePnoMjQU8D96pUYpFOH83RdBKgBskLtgbsGm.IK9w1zYE6Ppu',
   '0600000002'),
  (3, 1, 'EF345678', 'El Idrissi', 'Youssef', '8 Rue de Martil, Martil',
   'client3@example.ma',
   '$2y$12$ZXH5ePnoMjQU8D96pUYpFOH83RdBKgBskLtgbsGm.IK9w1zYE6Ppu',
   '0600000003');

-- ------------------------------------------------------------
-- CONSOMMATIONS DE DEMONSTRATION (mois precedent)
-- ------------------------------------------------------------
INSERT INTO consommation
  (id_client, periode_mois, periode_annee, index_eau, index_elec, statut)
VALUES
  (1, 9, 2026, 100.00, 300.00, 'VALIDEE'),
  (2, 9, 2026,  80.00, 250.00, 'VALIDEE'),
  (3, 9, 2026, 120.00, 400.00, 'VALIDEE');

-- ------------------------------------------------------------
-- TARIFS
-- Bornes continues : [borne_min, borne_max[
-- Derniere tranche : borne_max = NULL
-- ------------------------------------------------------------
INSERT INTO tarif
  (id_fournisseur, type, borne_min, borne_max, prix_unitaire)
VALUES
  -- Eau (DH/m3)
  (1, 'EAU',   0.00,   6.00,  2.3700),
  (1, 'EAU',   6.00,  20.00,  7.3900),
  (1, 'EAU',  20.00,  35.00, 10.9800),
  (1, 'EAU',  35.00,   NULL, 11.0300),

  -- Electricite (DH/kWh)
  (1, 'ELEC',   0.00, 100.00,  0.8200),
  (1, 'ELEC', 100.00, 150.00,  0.9200),
  (1, 'ELEC', 150.00,   NULL,  1.1000);
