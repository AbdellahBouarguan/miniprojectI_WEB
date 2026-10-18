# Modele de donnees - Version Alpha

## Tables retenues

- `fournisseur`
- `client`
- `tarif`
- `consommation`
- `photo_capteur`
- `anomalie`
- `facture`

Les tables `agent`, `conso_annuelle` et `reclamation` sont exclues de la version Alpha.

## Cles et relations

| Relation | Cardinalite | Cle etrangere |
|---|---|---|
| Fournisseur - Client | 1..1 - 0..n | `client.id_fournisseur` |
| Fournisseur - Tarif | 1..1 - 0..n | `tarif.id_fournisseur` |
| Fournisseur - Anomalie | 1..1 - 0..n | `anomalie.id_fournisseur` |
| Client - Consommation | 1..1 - 0..n | `consommation.id_client` |
| Client - Facture | 1..1 - 0..n | `facture.id_client` |
| Consommation - Anomalie | 1..1 - 0..n | `anomalie.id_conso` |
| Consommation - Facture | 1..1 - 0..1 | `facture.id_conso` UNIQUE |
| Consommation - Photo_Capteur | 1..1 - 2..2 | `photo_capteur.id_conso` + UNIQUE(`id_conso`, `type`) |

## Choix de modelisation

- Chaque table possede une cle primaire technique `id_*`.
- Le CIN du client est conserve comme attribut UNIQUE, mais n'est pas utilise comme cle primaire.
- Un client appartient a un fournisseur.
- Un tarif est defini par un fournisseur.
- Une consommation appartient a un client.
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
