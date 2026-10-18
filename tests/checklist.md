# Checklist de tests - Version Alpha

## Base de donnees

- [ ] Importer `sql/schema.sql` sans erreur.
- [ ] Importer `sql/seed.sql` sans erreur.
- [ ] Verifier les 7 tables.
- [ ] Verifier les cles etrangeres.
- [ ] Verifier les contraintes UNIQUE.
- [ ] Verifier le calcul des tranches.

## Authentification

- [ ] Connexion fournisseur.
- [ ] Connexion client.
- [ ] Mauvais mot de passe refuse.
- [ ] Acces aux pages protegees refuse sans session.
- [ ] Deconnexion.

## Clients

- [ ] Liste des clients.
- [ ] Ajout d'un client valide.
- [ ] CIN en doublon refuse.
- [ ] Email en doublon refuse.
- [ ] Modification d'un client.
- [ ] Recherche d'un client.

## Consommations

- [ ] Formulaire de saisie accessible au client.
- [ ] Saisie avant le 18 refusee.
- [ ] Valeur negative refusee.
- [ ] Valeur non numerique refusee.
- [ ] Index inferieur au mois precedent detecte comme anomalie.
- [ ] Index superieur au seuil detecte comme anomalie.
- [ ] Confirmation d'anomalie.
- [ ] Correction d'anomalie.
- [ ] Doublon de periode refuse.
- [ ] Photos EAU et ELEC enregistrees.
- [ ] Photo invalide refusee.

## Factures

- [ ] Generation automatique apres saisie valide.
- [ ] Generation apres confirmation d'anomalie.
- [ ] Montants HT, TVA et TTC corrects.
- [ ] TVA a 18 %.
- [ ] Affichage detaille de la facture.
- [ ] Photos visibles.
- [ ] Client voit uniquement ses factures.
- [ ] Fournisseur voit les factures de ses clients.

## Securite

- [ ] Requetes SQL preparees.
- [ ] Affichage echappe avec `htmlspecialchars`.
- [ ] Aucun mot de passe en clair.
- [ ] Upload limite aux images.
- [ ] Execution PHP bloquee dans `uploads/`.
