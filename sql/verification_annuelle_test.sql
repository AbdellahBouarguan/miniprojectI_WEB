-- ============================================================
-- Verification annuelle - DONNEES DE TEST (local uniquement)
-- Ajoute pour le client test@gmail.com : decembre 2024 (index de depart)
-- puis les 12 mois de 2025, tous VALIDEE.
--   Consommation 2025 attendue : eau = 120 m3, electricite = 1200 kWh
-- Sans danger si execute deux fois (INSERT IGNORE).
-- ============================================================

USE miniproject_version_alpha;

SET @c = (SELECT id_client FROM client WHERE email = 'test@gmail.com' LIMIT 1);

INSERT IGNORE INTO consommation
    (id_client, periode_mois, periode_annee, index_eau, index_elec, statut)
VALUES
    (@c, 12, 2024, 100, 1000, 'VALIDEE'),
    (@c,  1, 2025, 110, 1100, 'VALIDEE'),
    (@c,  2, 2025, 120, 1200, 'VALIDEE'),
    (@c,  3, 2025, 130, 1300, 'VALIDEE'),
    (@c,  4, 2025, 140, 1400, 'VALIDEE'),
    (@c,  5, 2025, 150, 1500, 'VALIDEE'),
    (@c,  6, 2025, 160, 1600, 'VALIDEE'),
    (@c,  7, 2025, 170, 1700, 'VALIDEE'),
    (@c,  8, 2025, 180, 1800, 'VALIDEE'),
    (@c,  9, 2025, 190, 1900, 'VALIDEE'),
    (@c, 10, 2025, 200, 2000, 'VALIDEE'),
    (@c, 11, 2025, 210, 2100, 'VALIDEE'),
    (@c, 12, 2025, 220, 2200, 'VALIDEE');