# Bugs et corrections — campagne finale

## BUG-F01 — Import annuel en erreur 500 sans mbstring

- **Sévérité** : P1
- **Module** : Import Agent
- **Cause** : `mb_check_encoding()` et `mb_convert_encoding()` appelées sans vérifier l’extension.
- **Symptôme** : erreur fatale à l’import du fichier.
- **Correction** : détection UTF-8 avec `preg_match('//u')` et fallback `iconv`.
- **Statut** : corrigé et retesté.

## BUG-F02 — Vérification annuelle fausse avec plusieurs contrats

- **Sévérité** : P1
- **Module** : Vérification annuelle
- **Cause** : la requête prenait le dernier index d’une seule consommation, pas la somme par contrat.
- **Symptôme** : client 1 affiché à 30 m³ au lieu de 130 m³.
- **Correction** : agrégation par contrat dans `VerificationBD::saisieAnnuelleClient()`.
- **Statut** : corrigé ; 130 m³ / 120,5 m³ vérifiés.

## BUG-F03 — Paiement réservé au client

- **Sévérité** : P1
- **Module** : Paiement
- **Cause** : le code initial de `Reda-b` utilisait le rôle client.
- **Symptôme** : le fournisseur ne pouvait pas enregistrer le paiement.
- **Correction** : `payerParFournisseur()`, contrôle de la facture via le client du fournisseur, formulaire fournisseur.
- **Statut** : corrigé ; paiement et double paiement testés.

## BUG-F04 — Module réclamation incomplet

- **Sévérité** : P1
- **Module** : Réclamations
- **Cause** : la branche initiale ajoutait une table et un lien, sans contrôleur ni vues.
- **Symptôme** : lien vers une 404.
- **Correction** : ajout de `BD/Reclamation.php`, `Traitement/Reclamation.php`, vues client/fournisseur, CSRF, notifications.
- **Statut** : corrigé ; dépôt et traitement testés.

## BUG-F05 — Liens non activés

- **Sévérité** : P2
- **Module** : Navigation
- **Cause** : modules non encore mergés.
- **Symptôme** : Réclamations / Paiements / Vérification affichés comme « Bientôt disponible ».
- **Correction** : activation après merge des modules.
- **Statut** : corrigé ; aucun lien `disabled` restant.

## Limites acceptées

- Pas de pièce jointe de réclamation.
- Seed sans photos de compteur.
- Seed sans octobre 2026.
- Défilement horizontal des tableaux larges sur mobile.
- Pas de pagination sur les listes de 18 factures.
- Rapport LaTeX final encore à mettre à jour.

## Bilan

Aucun bug P0 restant sur le périmètre final.  
Tous les bugs P1 détectés pendant l’intégration ont été corrigés.
