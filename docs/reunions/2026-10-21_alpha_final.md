# Finalisation de la version Alpha - 21/10/2026

## Branche

- Branche de travail : `aymane`
- Base : `nizar` (travail de Nizar et Nafisa)
- Branche `reda` non mergee ; seuls ses points forts ont ete repris.

## Corrections realisees

- Correction de la classe `ConsommationTraitement` et de son point d'entree.
- Remplacement de `AnomalieDetector` manquant par `Anomalie::detecter`.
- Correction du routage des redirections et des formulaires de consommation.
- Correction du formulaire client : variables `$old` / `$errors`, confirmation du mot de passe.
- Ajout du CSRF sur l'ajout et la modification d'un client.
- Suppression de l'auto-creation de compte client et de la connexion de demonstration.
- Suppression de `tests/login_dev.php`.
- Deplacement des photos dans `uploads/compteurs/`.
- Ajout de la recherche client.
- Ajout des consommations de demonstration pour le mois precedent.

## Facturation

Fichiers ajoutes :

- `BD/Facture.php`
- `Traitement/Tarification.php`
- `Traitement/FactureService.php`
- `IHM/facture/index.php`
- `IHM/facture/show.php`

Regles appliquees :

- TVA 18 %.
- Tranches progressives eau/electricite.
- Numero `FAC-YYYYMM-{id_conso}`.
- Generation dans une transaction.
- Consommation passée en `FACTUREE`.
- Client : ses factures uniquement.
- Fournisseur : factures de ses clients.

## Flux des anomalies

- `INDEX_INFERIEUR` : correction obligatoire.
- `SEUIL_DEPASSE` : confirmation ou correction.
- Apres confirmation ou correction valide : facture generee automatiquement.

## Test de reference

```text
25 m3 eau + 200 kWh
HT  = 355,58 DH
TVA = 64,00 DH
TTC = 419,58 DH
```

## Tests effectues

- Login client et fournisseur.
- Acces refuse sans session.
- Ajout, modification et recherche client.
- Saisie avec photos.
- Anomalie de seuil : confirmation puis facture.
- Anomalie d'index inferieur : confirmation bloquee.
- Correction puis facture.
- Consultation des factures cote client et fournisseur.
- Controle d'acces aux factures.
- `php -l` sur tous les fichiers PHP.
