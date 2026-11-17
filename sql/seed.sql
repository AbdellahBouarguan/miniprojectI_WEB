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

DELETE FROM notification;
DELETE FROM facture;
DELETE FROM anomalie;
DELETE FROM photo_capteur;
DELETE FROM conso_annuelle;
DELETE FROM consommation;
DELETE FROM contrat;
DELETE FROM quartier;
DELETE FROM tarif;
DELETE FROM client;
DELETE FROM agent;
DELETE FROM fournisseur;

ALTER TABLE notification AUTO_INCREMENT = 1;
ALTER TABLE facture AUTO_INCREMENT = 1;
ALTER TABLE anomalie AUTO_INCREMENT = 1;
ALTER TABLE photo_capteur AUTO_INCREMENT = 1;
ALTER TABLE conso_annuelle AUTO_INCREMENT = 1;
ALTER TABLE consommation AUTO_INCREMENT = 1;
ALTER TABLE contrat AUTO_INCREMENT = 1;
ALTER TABLE quartier AUTO_INCREMENT = 1;
ALTER TABLE tarif AUTO_INCREMENT = 1;
ALTER TABLE client AUTO_INCREMENT = 1;
ALTER TABLE agent AUTO_INCREMENT = 1;
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
-- AGENT
-- Mot de passe : agent123
-- ------------------------------------------------------------
INSERT INTO agent
  (id_agent, nom, prenom, email, mot_de_passe, telephone)
VALUES
  (1,
   'Bennani',
   'Karim',
   'agent@example.ma',
   '$2y$10$hA50D1Ifc9Z7Zmwvb0jLzO25F4XWfnZZ70BdQcK4Ak9Dy1RgZQ4em',
   '0612345678');

-- ------------------------------------------------------------
-- QUARTIERS
-- ------------------------------------------------------------
INSERT INTO quartier
  (id_quartier, nom, type_activite)
VALUES
  (1, 'Centre-ville', 'COMMERCIAL'),
  (2, 'Résidentiel Nord', 'RESIDENTIEL'),
  (3, 'Zone Industrielle', 'INDUSTRIEL');

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
-- CONTRATS
-- Client 1 possede deux contrats dans deux quartiers differents.
-- ------------------------------------------------------------
INSERT INTO contrat
  (id_contrat, id_client, id_quartier, numero_contrat, date_debut, date_fin, statut)
VALUES
  (1, 1, 1, 'CTR-1-CENTRE', '2026-01-01', NULL, 'ACTIF'),
  (2, 1, 2, 'CTR-1-NORD',   '2026-01-01', NULL, 'ACTIF'),
  (3, 2, 2, 'CTR-2-NORD',   '2026-01-01', NULL, 'ACTIF'),
  (4, 3, 3, 'CTR-3-ZONE',   '2026-01-01', NULL, 'ACTIF');

-- ------------------------------------------------------------
-- CONSOMMATIONS DE DEMONSTRATION (mois precedent)
-- ------------------------------------------------------------
INSERT INTO consommation
  (id_client, id_contrat, periode_mois, periode_annee, index_eau, index_elec, statut)
VALUES
  (1, 1, 9, 2026, 100.00, 300.00, 'VALIDEE'),
  (1, 2, 9, 2026,  30.00, 100.00, 'VALIDEE'),
  (2, 3, 9, 2026,  80.00, 250.00, 'VALIDEE'),
  (3, 4, 9, 2026, 120.00, 400.00, 'VALIDEE');

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

-- ------------------------------------------------------------
-- NOTIFICATIONS DE DEMONSTRATION
-- ------------------------------------------------------------
INSERT INTO notification
  (destinataire_role, id_destinataire, type, titre, message, lien, lu)
VALUES
  ('client', 1, 'FACTURE',
   'Bienvenue sur AquaVolt',
   'Votre espace client est prêt. Consultez vos factures et saisissez vos index.',
   'Facture/all', 0),
  ('client', 1, 'SYSTEME',
   'Pensez à saisir votre consommation',
   'La saisie est ouverte du 18 du mois jusqu’à la fin du mois.',
   'Consommation/add', 0),
  ('fournisseur', 1, 'ANOMALIE',
   'Anomalies à vérifier',
   'Consultez les relevés signalés avant la génération des factures.',
   'Anomalie/index', 0),
  ('fournisseur', 1, 'SYSTEME',
   'Bienvenue dans votre espace fournisseur',
   'Gérez vos clients, vos anomalies et vos factures depuis le tableau de bord.',
   'Client/all', 1);

-- ------------------------------------------------------------
-- HISTORIQUE DE DEMONSTRATION (mars a aout 2026)
-- Sert au tableau de bord du fournisseur : evolution mensuelle
-- des consommations et montant total des factures impayees.
-- Les factures sont volontairement toutes IMPAYEE : le module
-- Paiement permet ensuite de les passer a PAYEE en demo.
-- Le releve 09/2026 (ci-dessus) reste VALIDEE pour la demo de
-- facturation et d'anomalie.
-- ------------------------------------------------------------
INSERT INTO consommation
  (id_conso, id_client, id_contrat, periode_mois, periode_annee, index_eau, index_elec, statut)
VALUES
  (101, 1, 1, 3, 2026,  10.00,  40.00, 'FACTUREE'),
  (102, 1, 1, 4, 2026,  28.00,  70.00, 'FACTUREE'),
  (103, 1, 1, 5, 2026,  45.00, 110.00, 'FACTUREE'),
  (104, 1, 1, 6, 2026,  62.00, 150.00, 'FACTUREE'),
  (105, 1, 1, 7, 2026,  78.00, 200.00, 'FACTUREE'),
  (106, 1, 1, 8, 2026,  92.00, 270.00, 'FACTUREE'),
  (107, 2, 3, 3, 2026,   8.00,  32.00, 'FACTUREE'),
  (108, 2, 3, 4, 2026,  22.00,  65.00, 'FACTUREE'),
  (109, 2, 3, 5, 2026,  36.00, 100.00, 'FACTUREE'),
  (110, 2, 3, 6, 2026,  50.00, 140.00, 'FACTUREE'),
  (111, 2, 3, 7, 2026,  62.00, 190.00, 'FACTUREE'),
  (112, 2, 3, 8, 2026,  74.00, 225.00, 'FACTUREE'),
  (113, 3, 4, 3, 2026,  12.00,  48.00, 'FACTUREE'),
  (114, 3, 4, 4, 2026,  32.00,  95.00, 'FACTUREE'),
  (115, 3, 4, 5, 2026,  52.00, 140.00, 'FACTUREE'),
  (116, 3, 4, 6, 2026,  72.00, 190.00, 'FACTUREE'),
  (117, 3, 4, 7, 2026,  92.00, 270.00, 'FACTUREE'),
  (118, 3, 4, 8, 2026, 108.00, 340.00, 'FACTUREE');

INSERT INTO facture
  (numero, id_client, id_conso, date_facture, periode_mois, periode_annee,
   conso_eau, conso_elec, montant_ht, taux_tva, montant_tva, montant_ttc, etat)
VALUES
  ('FAC-202603-101', 1, 101, '2026-04-05', 3, 2026, 10.00, 40.00,  76.58, 18.00, 13.78,  90.36, 'IMPAYEE'),
  ('FAC-202604-102', 1, 102, '2026-05-05', 4, 2026, 18.00, 30.00, 127.50, 18.00, 22.95, 150.45, 'IMPAYEE'),
  ('FAC-202605-103', 1, 103, '2026-06-05', 5, 2026, 17.00, 40.00, 128.31, 18.00, 23.10, 151.41, 'IMPAYEE'),
  ('FAC-202606-104', 1, 104, '2026-07-05', 6, 2026, 17.00, 40.00, 128.31, 18.00, 23.10, 151.41, 'IMPAYEE'),
  ('FAC-202607-105', 1, 105, '2026-08-05', 7, 2026, 16.00, 50.00, 129.12, 18.00, 23.24, 152.36, 'IMPAYEE'),
  ('FAC-202608-106', 1, 106, '2026-09-05', 8, 2026, 14.00, 70.00, 130.74, 18.00, 23.53, 154.27, 'IMPAYEE'),
  ('FAC-202603-107', 2, 107, '2026-04-05', 3, 2026,  8.00, 32.00,  55.24, 18.00,  9.94,  65.18, 'IMPAYEE'),
  ('FAC-202604-108', 2, 108, '2026-05-05', 4, 2026, 14.00, 33.00, 100.40, 18.00, 18.07, 118.47, 'IMPAYEE'),
  ('FAC-202605-109', 2, 109, '2026-06-05', 5, 2026, 14.00, 35.00, 102.04, 18.00, 18.37, 120.41, 'IMPAYEE'),
  ('FAC-202606-110', 2, 110, '2026-07-05', 6, 2026, 14.00, 40.00, 106.14, 18.00, 19.11, 125.25, 'IMPAYEE'),
  ('FAC-202607-111', 2, 111, '2026-08-05', 7, 2026, 12.00, 50.00,  99.56, 18.00, 17.92, 117.48, 'IMPAYEE'),
  ('FAC-202608-112', 2, 112, '2026-09-05', 8, 2026, 12.00, 35.00,  87.26, 18.00, 15.71, 102.97, 'IMPAYEE'),
  ('FAC-202603-113', 3, 113, '2026-04-05', 3, 2026, 12.00, 48.00,  97.92, 18.00, 17.63, 115.55, 'IMPAYEE'),
  ('FAC-202604-114', 3, 114, '2026-05-05', 4, 2026, 20.00, 47.00, 156.22, 18.00, 28.12, 184.34, 'IMPAYEE'),
  ('FAC-202605-115', 3, 115, '2026-06-05', 5, 2026, 20.00, 45.00, 154.58, 18.00, 27.82, 182.40, 'IMPAYEE'),
  ('FAC-202606-116', 3, 116, '2026-07-05', 6, 2026, 20.00, 50.00, 158.68, 18.00, 28.56, 187.24, 'IMPAYEE'),
  ('FAC-202607-117', 3, 117, '2026-08-05', 7, 2026, 20.00, 80.00, 183.28, 18.00, 32.99, 216.27, 'IMPAYEE'),
  ('FAC-202608-118', 3, 118, '2026-09-05', 8, 2026, 16.00, 70.00, 145.52, 18.00, 26.19, 171.71, 'IMPAYEE');
