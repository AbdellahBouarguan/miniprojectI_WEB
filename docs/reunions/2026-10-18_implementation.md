# Compte rendu - Reunion du 18/10/2026

## Informations

- Date : 18/10/2026
- Horaire : 14:00 - 16:00
- Sujet : lancement de l'implementation de la version Alpha
- Participants : Abdellah Bouarguan, Nafisa El Younoussi, Reda Zakaria, Nizar Ben Yacoub, Aymen Ouggourni

## Decisions

### Perimetre

La version Alpha comprend trois fonctionnalites :

1. Gestion des clients
2. Saisie des consommations
3. Gestion des factures

### Tables retenues

- client
- fournisseur
- tarif
- consommation
- facture
- anomalie
- photo_capteur

Les tables Agent, Conso_Annuelle et Reclamation sont exclues de cette version.

### Flux de saisie

- Les valeurs negatives, non numeriques ou illogiques sont refusees.
- En cas d'anomalie metier, le client choisit entre confirmer et corriger.
- Si le client confirme, la facture est generee automatiquement.
- Si le client corrige, il revient au formulaire de saisie.

### Repartition des taches

| Membre | Tache |
|---|---|
| Reda Zakaria | Gestion des clients |
| Nafisa El Younoussi, Nizar Ben Yacoub, Abdellah Bouarguan | Saisie des consommations et generation de facture |
| Aymen Ouggourni, Abdellah Bouarguan | Gestion et affichage des factures |
| Abdellah Bouarguan | Modele de donnees, SQL, structure du projet, documentation et Git/GitHub |

### Base de donnees

- Nom de la base : `miniproject_version_alpha`
- SGBD : MariaDB
- Schema partage : `sql/schema.sql`
- Donnees de depart : `sql/seed.sql`

## Actions

- [ ] Finaliser le modele de donnees et le fichier SQL partage.
- [ ] Creer la structure du projet et le depot Git.
- [ ] Implementer la gestion des clients.
- [ ] Implementer la saisie des consommations et la generation des factures.
- [ ] Implementer l'affichage des factures.
- [ ] Tester l'ensemble et documenter le travail.
