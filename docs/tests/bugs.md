# Bugs et limites

## Bugs corrigés

### BUG-01 — Le champ « Nom du quartier » était prérempli avec le nom du fournisseur

- **Sévérité** : P2
- **Module** : Quartiers
- **Cause** : `IHM/layout/header.php` définissait `$nom` pour l’utilisateur connecté, puis `IHM/quartier/index.php` réutilisait `$nom` pour le formulaire.
- **Symptôme** : le champ affichait « Regie Eau et Electricite ».
- **Correction** : renommage en `$nomQuartier` et `$typeQuartier`.
- **Statut** : corrigé et revérifié.

### BUG-02 — Erreur 500 lors d’un doublon de quartier

- **Sévérité** : P1
- **Module** : Quartiers
- **Cause** : après renommage, `quartier_add()` utilisait encore `$nomQuartier` et `$typeQuartier` dans la redirection d’erreur.
- **Symptôme** : `Undefined variable $nomQuartier`, puis `urlencode(null)`.
- **Correction** : utiliser `$nom` et `$type` dans `quartier_add()`.
- **Statut** : corrigé et revérifié.

### BUG-03 — Aucun relevé précédent affiché avec plusieurs contrats

- **Sévérité** : P2
- **Module** : Consommation
- **Cause** : aucun contrat n’était sélectionné par défaut dans le formulaire.
- **Symptôme** : « Aucun relevé précédent » alors que le client avait deux contrats.
- **Correction** : sélection du premier contrat actif par défaut ; le client peut changer.
- **Statut** : corrigé et revérifié sur desktop et mobile.

### BUG-05 — Le formulaire affichait « Ouverte » avant le 18

- **Sévérité** : P2
- **Module** : Consommation
- **Cause** : la vue affichait toujours le bandeau « Période ouverte » sans calculer la date.
- **Symptôme** : le 17/11, le formulaire annonçait une période ouverte alors que la soumission était refusée.
- **Correction** : calcul de `$periodeOuverte` dans `ConsommationTraitement` et affichage conditionnel « Ouverte / Fermée ».
- **Statut** : corrigé et revérifié.

### BUG-04 — Tableaux illisibles sur mobile

- **Sévérité** : P2
- **Module** : Responsive / factures / contrats / quartiers
- **Cause** : les tableaux se compressaient au lieu de défiler.
- **Symptôme** : colonnes écrasées, informations coupées.
- **Correction** :
  - `min-width: 760px` sur les tableaux dans `.table-wrap` en mobile ;
  - `white-space: nowrap` sur les numéros de facture et de contrat.
- **Statut** : corrigé ; le défilement horizontal est vérifié (`scrollWidth > clientWidth`).

## Limites connues

### KNOWN-01 — Seed sans photos ni mois précédent complet

- Le seed contient septembre 2026, pas octobre.
- Le seed ne contient pas de photos de compteur.
- En novembre, le formulaire affiche donc septembre comme dernier relevé et aucune photo précédente.
- **Impact** : démonstration du « mois précédent » moins réaliste.
- **Statut** : ouvert.

### KNOWN-02 — Modules non présents sur `main`

Non testables actuellement :

- réclamations ;
- paiements ;
- Agent + import annuel ;
- vérification annuelle ;
- dashboard final ;
- facture après correction fournisseur.

Ils seront testés après merge de leurs branches.

### KNOWN-03 — Format de date du navigateur

Le champ `date` peut s’afficher au format américain (`mm/dd/yyyy`) selon la locale du navigateur, malgré `lang="fr"`.

- **Impact** : cosmétique.
- **Statut** : accepté pour cette version.

### KNOWN-04 — Défilement horizontal mobile

Les tableaux larges (factures, contrats, quartiers) nécessitent un défilement horizontal sur mobile.

- **Impact** : UX acceptable mais perfectible.
- **Statut** : accepté ; vérifié comme fonctionnel.

## Bilan

Aucun bug P0 restant sur le périmètre testé.  
Les bugs P1/P2 détectés ont été corrigés.  
Les limites restantes concernent surtout les données de démonstration et les modules non encore intégrés.
