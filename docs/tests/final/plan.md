# Plan de la campagne finale

## Objectif

Valider la version finale après fusion des branches :

- `nizar_travail` ;
- `aymen_anomalies` ;
- `Reda-b` ;
- `nafisa/travail`.

## Scénario Agent

- [x] Connexion `agent@example.ma`.
- [x] Import de `Consommation_Annuelle.txt`.
- [x] Rapport d’import.
- [x] Import du fichier d’erreurs.
- [x] Relevés importés visibles.

## Scénario Client

- [x] Connexion.
- [x] Dépôt d’une réclamation.
- [x] Suivi de la réclamation.
- [x] Notifications.
- [x] Dashboard.
- [x] Consultation des factures.
- [x] Accès aux modules interdits refusé.

## Scénario Fournisseur

- [x] Dashboard : impayés, encaissé, évolution.
- [x] Liste des réclamations.
- [x] Traitement d’une réclamation.
- [x] Vérification annuelle.
- [x] Validation d’une consommation annuelle.
- [x] Liste des factures.
- [x] Paiement total d’une facture.
- [x] Notification client.
- [x] Double paiement refusé.
- [x] Notifications.

## Scénario Responsive

Pages capturées en 1440, 1024 et 390 :

- Agent / import ;
- dashboard fournisseur ;
- vérification annuelle ;
- réclamations fournisseur ;
- liste des factures ;
- dashboard client ;
- réclamations client ;
- ajout de réclamation ;
- notifications.

## Critères

- Aucun 500.
- Aucun accès non autorisé.
- Import idempotent.
- Vérification multi-contrats correcte.
- Réclamation traitée et notifiée.
- Paiement unique.
- Liens du header actifs.
- Captures nommées et classées.
