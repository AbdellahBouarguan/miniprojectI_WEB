# Modele de donnees - Version finale

## Tables retenues

- `fournisseur`
- `client`
- `quartier`
- `contrat`
- `tarif`
- `consommation`
- `photo_capteur`
- `anomalie`
- `facture`
- `notification`
- `agent`
- `conso_annuelle`
- `reclamation`

Toutes les tables sont presentes dans la version finale.

## Cles et relations

| Relation | Cardinalite | Cle etrangere |
|---|---|---|
| Fournisseur - Client | 1..1 - 0..n | `client.id_fournisseur` |
| Fournisseur - Tarif | 1..1 - 0..n | `tarif.id_fournisseur` |
| Fournisseur - Anomalie | 1..1 - 0..n | `anomalie.id_fournisseur` |
| Client - Contrat | 1..1 - 0..n | `contrat.id_client` |
| Quartier - Contrat | 1..1 - 0..n | `contrat.id_quartier` |
| Contrat - Consommation | 1..1 - 0..n | `consommation.id_contrat` |
| Client - Facture | 1..1 - 0..n | `facture.id_client` |
| Consommation - Anomalie | 1..1 - 0..n | `anomalie.id_conso` |
| Consommation - Facture | 1..1 - 0..1 | `facture.id_conso` UNIQUE |
| Consommation - Photo_Capteur | 1..1 - 2..2 | `photo_capteur.id_conso` + UNIQUE(`id_conso`, `type`) |
| Agent - Conso_Annuelle | 1..1 - 0..n | `conso_annuelle.id_agent` |
| Client - Conso_Annuelle | 1..1 - 0..n | `conso_annuelle.id_client` |
| Client - Reclamation | 1..1 - 0..n | `reclamation.id_client` |
| Facture - Reclamation | 0..1 - 0..n | `reclamation.id_facture` |

## Choix de modelisation

- Chaque table possede une cle primaire technique `id_*`.
- Le CIN du client est conserve comme attribut UNIQUE, mais n'est pas utilise comme cle primaire.
- Un client appartient a un fournisseur.
- Un client peut avoir plusieurs contrats dans des quartiers differents.
- Un quartier est associe a un type d'activite.
- Un tarif est defini par un fournisseur.
- Une consommation appartient a un contrat, donc a un client et a un quartier.
- Une consommation peut avoir plusieurs anomalies.
- Une consommation genere au plus une facture.
- Une consommation possede exactement deux photos : une pour l'eau et une pour l'electricite.
- Une facture est liee au client et a la consommation.
- Les montants de la facture sont stockes pour conserver l'historique.

## Flux des anomalies

1. Le client saisit ses index.
2. Les valeurs negatives, non numeriques ou invalides sont refusees.
3. Si une anomalie metier est detectee, le client peut confirmer ou corriger.
4. Si le client confirme, la consommation est validee et la facture est generee automatiquement.
5. Si le client corrige, il revient au formulaire.

Le fournisseur consulte les anomalies. La correction par le fournisseur est reservee a une version ulterieure.

## Regeneration des images

```bash
curl -L -o plantuml.jar https://github.com/plantuml/plantuml/releases/latest/download/plantuml.jar
java -jar plantuml.jar -tpng docs/conception/msd/msd.puml
java -jar plantuml.jar -tsvg docs/conception/msd/msd.puml
```

## Fichiers

- `msd.puml` : source PlantUML
- `msd.png` : image generee
- `msd.svg` : version vectorielle
- `msd_avant.png` : ancienne version, conservee pour comparaison
