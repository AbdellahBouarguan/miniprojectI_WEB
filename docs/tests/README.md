# Tests manuels — Version finale

## Objet

Ce dossier regroupe le plan de tests, les résultats, les anomalies détectées et les captures d’écran de la version finale.

## Environnement

- Date de la campagne : **17/11/2026**
- PHP : 8.4.27
- Base : MariaDB 11.8
- Navigateur principal : Brave headless
- Desktop : 1440 × 900
- Tablette : 1024 × 768
- Mobile : 390 × 844
- Serveur : `http://127.0.0.1:8000`

## Important — mode test de la période

La saisie est normalement ouverte à partir du **18** du mois. Pour tester la soumission des consommations le 17/11, la constante `JOUR_DEBUT` a été temporairement passée à `1`, puis restaurée à `18` après les tests.

Les captures marquées « période ouverte au 1er » proviennent de ce mode test.

## Contenu

| Fichier | Rôle |
|---|---|
| `plan.md` | Plan de tests et scénarios |
| `results.md` | Résultats, statuts et preuves |
| `bugs.md` | Bugs trouvés, corrections et limites connues |
| `screenshots/` | Captures desktop, tablette, mobile et corrections |
| `logs/` | Logs des campagnes Playwright |

## Comptes de test

```text
Fournisseur : fournisseur@example.ma / fournisseur123
Client 1    : client1@example.ma / client123
Client 2    : client2@example.ma / client123
Client 3    : client3@example.ma / client123
Agent       : agent@example.ma  / agent123
```

## État de la base après les tests

La base est remise au seed à la fin de la campagne :

```text
3 clients
3 quartiers
4 contrats
22 consommations
18 factures historiques
4 notifications
1 agent
0 conso_annuelle
0 réclamation
```

## Modules intégrés et testés

Les modules suivants ont été mergés et testés :

- réclamations ;
- paiements ;
- Agent + import annuel ;
- vérification annuelle ;
- dashboard final avec impayés ;
- génération de facture après correction fournisseur.

## Campagne finale

Après intégration des modules Agent, vérification, réclamations et paiements :

- `docs/tests/final/README.md` ;
- `docs/tests/final/results.md` ;
- `docs/tests/final/bugs.md` ;
- `docs/tests/final/screenshots/`.
