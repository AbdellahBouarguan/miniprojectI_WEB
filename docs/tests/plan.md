# Plan de tests manuels

## Objectif

Tester l’application comme un utilisateur et comme un professeur :

- parcours nominal ;
- erreurs de saisie ;
- contrôle d’accès ;
- responsive ;
- cohérence base de données / interface.

Chaque action importante doit produire une capture avant/après et, si possible, une vérification SQL.

---

## Phase 1 — Visiteur et authentification

- [x] Accueil public.
- [x] Connexion vide.
- [x] Mauvais mot de passe.
- [x] Connexion client.
- [x] Connexion fournisseur.
- [x] Déconnexion.
- [x] Client vers une page fournisseur.
- [x] Fournisseur vers une page client.
- [x] Accès notifications sans session.

## Phase 2 — Quartiers

- [x] Liste des quartiers.
- [x] Ajout d’un quartier valide.
- [x] Doublon nom + type.
- [x] Type invalide.
- [x] Nom vide.

## Phase 3 — Clients et contrats

- [x] Liste clients.
- [x] Fiche client 1 avec deux contrats.
- [x] Ajout d’un contrat.
- [x] Doublon de numéro.
- [x] Quartier inexistant.
- [x] Dates invalides.
- [x] Résiliation.
- [x] Suppression client avec consommation bloquée.

## Phase 4 — Consommation

- [x] Formulaire avec deux contrats.
- [x] Sélection du contrat.
- [x] Valeur non numérique.
- [x] Valeur négative.
- [x] Photo manquante.
- [x] Photo invalide.
- [x] Période fermée avant le 18.
- [x] Saisie valide contrat 1.
- [x] Saisie valide contrat 2 le même mois.
- [x] Doublon même contrat + même mois.
- [x] Index inférieur.
- [x] Seuil dépassé.
- [x] Historique et détail avec contrat/quartier.

## Phase 5 — Anomalies

- [x] Anomalie index inférieur.
- [x] Confirmation impossible pour index inférieur.
- [x] Correction d’index inférieur.
- [x] Anomalie de seuil.
- [x] Confirmation d’anomalie de seuil.
- [x] Liste fournisseur.
- [x] Accès client refusé.

## Phase 6 — Factures

- [x] Liste client.
- [x] Détail client.
- [x] Liste fournisseur.
- [x] Détail fournisseur.
- [x] Contrat, quartier et activité affichés.
- [x] Photos affichées.
- [x] HT / TVA / TTC.
- [x] Accès à la facture d’un autre client refusé (404).

## Phase 7 — Notifications

- [x] Cloche et compteur non lu.
- [x] Liste client.
- [x] Marquage comme lu.
- [x] Tout marquer comme lu.
- [x] Notifications fournisseur.
- [x] Notification vide client 2.
- [x] Notification créée après facture.
- [x] Accès croisé refusé.

## Phase 8 — Responsive

Pages testées en 1440, 1024 et 390 :

- [x] Accueil.
- [x] Connexion.
- [x] Dashboard client.
- [x] Formulaire de consommation.
- [x] Liste des factures.
- [x] Notifications.
- [x] Dashboard fournisseur.
- [x] Contrats.
- [x] Quartiers.

Les tableaux larges sont volontairement scrollables horizontalement sur mobile.

## Phase 9 — Sécurité

- [x] Accès direct à une facture d’un autre client → 404.
- [x] CSRF sur quartier → 419.
- [x] CSRF sur notification → 419.
- [x] Contrôle d’accès client / fournisseur.
- [x] Upload image uniquement.
- [ ] Injection SQL complète.
- [ ] XSS complet sur tous les champs.
- [ ] Test de charge.

## Phase 10 — Modules en attente

Non testables sur `main` :

- [ ] Réclamations.
- [ ] Paiements.
- [ ] Agent + import annuel.
- [ ] Vérification annuelle.
- [ ] Dashboard impayés.
- [ ] Facture après correction fournisseur.

---

## Convention des captures

```text
<numéro>_<module>_<action>_<état>.png
```

Les captures sont classées dans :

```text
screenshots/desktop/01_public_auth/
screenshots/desktop/02_client_flow/
screenshots/responsive/
screenshots/fixes/
```

---

## Critères de sortie

- Aucun 500.
- Aucun accès non autorisé.
- Aucune facture en double.
- Contrat et quartier corrects sur la facture.
- Deux contrats du même client fonctionnels.
- Responsive sans blocage.
- Bugs P0/P1 corrigés.
- Base remise au seed.
