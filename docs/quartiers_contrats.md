# Quartiers et contrats

## Objectif

Un client peut posséder plusieurs contrats dans des quartiers différents.
Chaque quartier est associé à un type d’activité :

```text
RESIDENTIEL
COMMERCIAL
INDUSTRIEL
```

Une consommation est rattachée à un contrat, donc indirectement à un quartier.

## Modèle

```text
quartier
  id_quartier
  nom
  type_activite

contrat
  id_contrat
  id_client
  id_quartier
  numero_contrat
  date_debut
  date_fin
  statut

consommation
  id_contrat
  UNIQUE(id_contrat, periode_mois, periode_annee)
```

La facture n’a pas de `id_quartier` : elle hérite du quartier via
`facture → consommation → contrat → quartier`.

## Migration

Base existante :

```bash
mysql -u root < sql/migrations/003_quartier_contrat.sql
```

Installation neuve :

```bash
mysql -u root < sql/schema.sql
mysql -u root < sql/seed.sql
```

## Routes fournisseur

```text
GET  /Quartier/all
POST /Quartier/add

GET  /Contrat/all
POST /Contrat/add/{idClient}
POST /Contrat/resilier/{id}
```

Les contrats sont aussi visibles et créés depuis la fiche d’un client.

## Côté client

Lors de la saisie d’une consommation :

- si le client a un seul contrat, il est sélectionné automatiquement ;
- si le client a plusieurs contrats, il choisit le contrat ;
- le relevé précédent est calculé par contrat ;
- deux consommations du même mois sont autorisées pour deux contrats différents.

## Seed de démonstration

- `Centre-ville` — `COMMERCIAL`
- `Résidentiel Nord` — `RESIDENTIEL`
- `Zone Industrielle` — `INDUSTRIEL`

Le client 1 possède deux contrats :

```text
CTR-1-CENTRE  → Centre-ville
CTR-1-NORD    → Résidentiel Nord
```

## Tests

- création d’un quartier ;
- création d’un contrat ;
- unicité du numéro de contrat ;
- deux contrats pour un même client ;
- rattachement de la consommation ;
- contrôle d’appartenance du contrat au client ;
- affichage du quartier sur la facture.
