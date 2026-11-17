# Résultats des tests

Campagne du **17/11/2026**.

## Synthèse

| Phase | Résultat | Preuves |
|---|---|---|
| 1 — Visiteur / authentification | PASS | 19 captures desktop |
| 2 — Quartiers | PASS après correction | captures + test doublon |
| 3 — Clients / contrats | PASS | captures ajout / doublon / résiliation |
| 4 — Consommation | PASS en mode test | deux contrats, doublon, photos |
| 5 — Anomalies | PASS | index inférieur + seuil |
| 6 — Factures | PASS | client + fournisseur, 404 croisé |
| 7 — Notifications | PASS | compteur, lecture, tout lire |
| 8 — Responsive | PASS | 27 captures desktop/tablette/mobile |
| 9 — Sécurité | PARTIEL | accès, CSRF, upload, 404 |
| 10 — Modules en attente | N/A | branches non mergées |

## Nombre de captures

```text
Phase A — public + fournisseur : 19
Phase B — client + factures     : 19
Responsive                     : 27
Corrections ciblées            : 6
Total des captures d'action    : 71
Planches contact               : 4
```

Les captures sont dans `docs/tests/screenshots/`.

## Tests fonctionnels clés

### Authentification

- Connexion client : OK.
- Connexion fournisseur : OK.
- Mauvais mot de passe : message d’erreur.
- Accès client à une page fournisseur : refusé.
- Accès notifications sans session : redirection.

### Quartiers

- Trois quartiers initiaux : OK.
- Ajout d’un quartier : OK.
- Doublon `nom + type` : refusé.
- Après correction, le formulaire conserve le nom et le type saisis.

### Contrats

- Client 1 : deux contrats dans deux quartiers : OK.
- Ajout d’un contrat : OK.
- Doublon de numéro : refusé.
- Résiliation : OK.
- Contrat affiché sur la fiche client : OK.

### Consommation

- Formulaire avec deux contrats : OK.
- Premier contrat sélectionné par défaut : OK.
- Relevé précédent affiché : OK.
- Deux consommations le même mois sur deux contrats : **acceptées**.
- Même contrat + même mois : **refusé**.
- Photos : affichées dans la facture.
- Mode période fermée : refus avant le 18.

### Anomalies

- Index inférieur : anomalie `INDEX_INFERIEUR`.
- Confirmation d’un index inférieur : impossible.
- Correction : formulaire puis facture.
- Seuil dépassé : anomalie `SEUIL_DEPASSE`.
- Confirmation : facture générée.

### Factures

- Facture client 1 contrat `CTR-1-CENTRE` :
  - eau 0 m³ ;
  - électricité 40 kWh ;
  - HT 38,70 DH ;
  - TVA 6,97 DH ;
  - TTC 45,67 DH.
- Facture client 1 contrat `CTR-1-NORD` :
  - eau 10 m³ ;
  - électricité 20 kWh ;
  - HT 60,18 DH ;
  - TVA 10,83 DH ;
  - TTC 71,01 DH.
- Contrat, quartier, type d’activité affichés.
- Client 2 accède à une facture de client 1 : **404**.

### Notifications

- Badge non lu : OK.
- Notification après facture : OK.
- Marquage comme lu : OK.
- Tout marquer comme lu : OK.
- Client 2 : aucune notification.
- Fournisseur : notifications visibles.

## Responsive

Largeurs testées :

```text
1440 × 900
1024 × 768
390 × 844
```

Vérifications :

- tableaux larges scrollables ;
- `scrollWidth` supérieur à `clientWidth` sur mobile ;
- navigation mobile fixe ;
- formulaires empilés ;
- pas de perte de contenu.

Exemples mesurés :

```text
Factures mobile  : clientWidth 316 / scrollWidth 810
Contrats mobile  : clientWidth 316 / scrollWidth 760
Quartiers mobile : clientWidth 316 / scrollWidth 760
```

## Sécurité

- Accès à une facture d’un autre client : 404.
- CSRF quartier invalide : 419.
- CSRF notification invalide : 419.
- Upload non-image : refusé.
- Contrôle de rôle client / fournisseur : OK.

## Limites connues

- Le seed ne contient pas de photos pour septembre/octobre.
- Le « mois précédent » en novembre affiche septembre dans le seed actuel.
- Les modules réclamations, paiements, Agent, vérification et dashboard final ne sont pas encore sur `main`.
- Les tableaux larges nécessitent un défilement horizontal sur mobile.
- Le format du champ date dépend de la locale du navigateur.

## Conclusion

Le socle actuel est stable pour les modules présents sur `main`.  
Les deux anomalies bloquantes détectées pendant la campagne ont été corrigées et revérifiées.
