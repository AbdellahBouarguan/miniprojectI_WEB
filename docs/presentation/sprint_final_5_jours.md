# Présentation — Sprint final 5 jours

**Application web de gestion des factures d’eau et d’électricité**

> Document de préparation pour la réunion d’équipe.
> Les libellés **Membre 1 à Membre 5** sont volontairement anonymes.
> Ils seront remplacés manuellement par les prénoms avant la présentation.

## Fichiers de la présentation

| Fichier | Usage |
|---|---|
| `sprint_final_5_jours.md` | Version détaillée : explications, guides par membre, annexes |
| `sprint_final_5_jours.pdf` | Version projetable, 22 slides au format 16:9 |
| `sprint_final_5_jours.html` | Source HTML de la version projetable |

---

## Fiche de la réunion

| Élément | Valeur |
|---|---|
| Sujet | Passage de la version Alpha à la version finale |
| Objectif | Valider le périmètre, répartir le travail et lancer le sprint de 5 jours |
| Durée conseillée | 45 minutes |
| Format | Réunion d’équipe / Google Meet |
| Participants | 5 membres |
| Livrable attendu | Version finale + rapport final + démonstration |
| Date de fin | J5 |
| Règle principale | Aucune nouvelle fonctionnalité après J3 18:00 |

### Ordre du jour conseillé

| Durée | Sujet |
|---|---|
| 5 min | Contexte et objectif |
| 10 min | Ce qui est déjà fait |
| 10 min | Ce qui reste et priorités |
| 10 min | Planning et répartition |
| 5 min | Risques et plans de repli |
| 5 min | Décisions à voter |

### Message d’ouverture

> « Nous avons déjà une Alpha stable. Le sujet n’est pas de recommencer, mais de compléter les modules manquants, de les intégrer et de livrer une version finale en 5 jours. Le risque principal est le périmètre, pas le code existant. »

---

## Sommaire

1. Contexte et objectif
2. Ce que nous avons déjà fait
3. Ce qui reste par rapport à l’énoncé
4. Périmètre du sprint : P0, P1, P2
5. Planning global J1 → J5
6. Répartition des rôles
7. Guide détaillé du Membre 1
8. Guide détaillé du Membre 2
9. Guide détaillé du Membre 3
10. Guide détaillé du Membre 4
11. Guide détaillé du Membre 5
12. Dépendances et chemin critique
13. Méthode de travail et Git
14. Tests et démonstration finale
15. Risques et plans de repli
16. Definition of Done
17. Décisions à prendre pendant la réunion
18. Actions immédiates
19. Annexes

---

# Slide 1 — Titre

## Sprint final — 5 jours pour livrer la version finale

- Application web de gestion des factures d’eau et d’électricité
- Passage de la version Alpha à la version finale
- Durée : **J1 à J5**
- Équipe : **5 membres**
- Livrables :
  - application complète ;
  - rapport final ;
  - démonstration de bout en bout ;
  - tag `v1.0-final`.

> **À dire :**  
> « Nous entrons dans la dernière ligne droite. L’objectif est de livrer une version finale utilisable, testée et documentée en 5 jours. »

---

# Slide 2 — Objectif de la réunion

## Ce que nous devons décider aujourd’hui

1. Valider le périmètre exact des 5 jours.
2. Trancher le flux des anomalies.
3. Valider les choix techniques :
   - notifications internes ;
   - paiement total uniquement ;
   - Agent avec login séparé.
4. Attribuer les 5 rôles aux membres.
5. Verrouiller le planning et les points de contrôle.
6. Fixer la règle : **aucune nouvelle fonctionnalité après J3 18:00**.

> **À dire :**  
> « À la fin de cette réunion, chacun doit savoir ce qu’il fait demain matin, sur quels fichiers, et avec quel critère d’acceptation. »

---

# Slide 3 — Ce que nous avons déjà fait

## Alpha stable et fonctionnelle

| Élément | État |
|---|---|
| Authentification client / fournisseur | Fait |
| Gestion des clients : ajout, modification, recherche | Fait |
| Saisie des consommations eau / électricité | Fait |
| Téléversement des photos des compteurs | Fait |
| Affichage du relevé précédent : valeur + photo | Fait |
| Détection des anomalies : index inférieur / seuil | Fait |
| Calcul des tranches eau / électricité | Fait |
| TVA 18 % et calcul HT / TVA / TTC | Fait |
| Génération et consultation des factures | Fait |
| Contrôle d’accès client / fournisseur | Fait |
| Tableau de bord simple | Partiel |
| Rapport Alpha | 28 pages, compilé |
| Git / PR / tags | 3 PR mergées, tags `v0.1` à `v0.1.2` |

### Exemple de référence validé

```text
25 m³ d’eau + 200 kWh
HT  = 355,58 DH
TVA = 64,00 DH
TTC = 419,58 DH
```

> **À dire :**  
> « Le socle métier est déjà là. Les calculs, la saisie, les photos, les factures et les contrôles d’accès fonctionnent. Nous ne repartons pas de zéro. »

---

# Slide 4 — Ce qui reste par rapport à l’énoncé

## Matrice de conformité

| Exigence | État | Reste à faire |
|---|---|---|
| Client consulte ses factures | Fait | — |
| Saisie des consommations + photos | Fait | — |
| Mois précédent affiché | Fait | — |
| Facture automatique sans anomalie | Fait | — |
| Détection des anomalies | Fait | — |
| Facture complète : numéro, date, période, client, consommations, HT/TVA/TTC, photos | Fait | — |
| Fournisseur gère les clients | Fait | — |
| Saisie du 18 à la fin du mois | Fait | — |
| Fournisseur traite les anomalies | Partiel | Correction fournisseur + facture après correction |
| Réclamations client | Manquant | Module complet |
| Traitement des réclamations + notification | Manquant | Module complet |
| Paiement d’une facture | Manquant | Module complet |
| Tableau de bord : impayés + évolution | Partiel | Requêtes + graphique |
| Import annuel par Agent | Manquant | Module complet |
| Vérification annuelle | Manquant | Page fournisseur |
| Notifications client | Manquant | Table + cloche + événements |

> **À dire :**  
> « Il reste cinq blocs : anomalies, réclamations, paiements, import/Agent, dashboard/notifications. Le reste est déjà couvert. »

---

# Slide 5 — Périmètre du sprint

## Priorité P0 — obligatoire

- Finaliser le flux des anomalies.
- Ajouter les réclamations client.
- Ajouter le traitement des réclamations par le fournisseur.
- Ajouter le paiement total d’une facture.
- Ajouter le tableau de bord : total impayé + évolution mensuelle.
- Ajouter le rôle Agent + import du fichier annuel.
- Ajouter la vérification annuelle.
- Ajouter les notifications internes.
- Mettre à jour le rapport final.

## Priorité P1 — seulement si avance

- Pièce jointe dans les réclamations.
- Filtres et pagination.
- Notification e-mail.
- Impression / export PDF de facture.

## Priorité P2 — reporté

- Paiements partiels.
- Multi-fournisseurs.
- Tableau de bord avancé avec plusieurs graphiques.
- Réinitialisation de mot de passe.

> **À dire :**  
> « Pour tenir 5 jours, on livre d’abord tout le P0. Le P1 n’est pas bloquant. Le P2 est explicitement abandonné pour cette version. »

---

# Slide 6 — Planning global J1 → J5

| Jour | Objectif principal | Résultat attendu |
|---|---|---|
| **J1** | Décisions, schéma, auth Agent, démarrage modules | Base cible + login Agent + branches prêtes |
| **J2** | Backend et écrans des modules | Réclamations, paiements, import : première version |
| **J3** | Intégration, notifications, dashboard, vérification | Parcours complet utilisable |
| **J4** | Tests, corrections, captures, documentation | Checklist validée + captures |
| **J5** | Rapport final, release, démo | PDF final + tag `v1.0-final` |

## Règles de gestion

- **J3 18:00 : feature freeze.**
- J4 : corrections uniquement.
- J5 : documentation, compilation, démonstration.
- Aucun merge massif le J5.

> **À dire :**  
> « On ne merge pas tout à la fin. Chaque jour doit produire une version qui démarre et qui peut être montrée. »

---

# Slide 7 — Répartition des rôles

| Membre | Rôle | Modules principaux |
|---|---|---|
| **Membre 1** | Socle, base, Agent, intégration | Schéma SQL, seed, auth Agent, navigation, merge |
| **Membre 2** | Réclamations et notifications | Dépôt, traitement, statuts, cloche |
| **Membre 3** | Paiements et dashboard | Paiement, état `PAYEE`, impayés, graphique |
| **Membre 4** | Import annuel et vérification | Fichier Agent, parsing, rapport, écarts |
| **Membre 5** | Anomalies, facturation, QA, rapport | Flux anomalies, facture après correction, tests, PDF |

## Reviews croisées

- Membre 2 review Membre 3.
- Membre 3 review Membre 2.
- Membre 4 review Membre 1.
- Membre 5 review Membre 1.
- Membre 1 review Membre 5.

> **À dire :**  
> « Chaque module a un propriétaire unique. Les autres ne modifient pas ses fichiers sans accord. »

---

# Slide 8 — Membre 1 : socle, base, Agent, intégration

## Mission

Garantir que la base, l’authentification et l’intégration restent stables.

## J1 — Base et Agent

- Figer le schéma cible : **12 tables**.
- Créer les migrations :
  ```text
  agent
  conso_annuelle
  reclamation
  paiement
  notification
  ```
- Ajouter le rôle `agent` dans `Traitement/Auth.php`.
- Ajouter la session `id_agent` et la redirection vers l’espace Agent.
- Mettre à jour `index.php` avec les nouveaux contrôleurs.

## J2 — Intégration des modules

- Fusionner les DDL des autres membres dans `sql/schema.sql`.
- Mettre à jour `sql/seed.sql`.
- Ajouter l’Agent de démonstration.
- Préparer les routes et la navigation.

## J3 — Navigation et merges

- Activer les liens :
  - Réclamations ;
  - Paiements ;
  - Vérification annuelle ;
  - Espace Agent.
- Mettre à jour `IHM/layout/header.php`.
- Merger les PR dans `main`.
- Résoudre les conflits.

## J4 — Intégration finale

- Réinitialiser la base.
- Tester le login des 3 rôles.
- Lancer `php -l` sur tout le projet.
- Vérifier les 404 et les accès interdits.

## J5 — Release

- Créer le tag `v1.0-final`.
- Vérifier le README.
- Participer à la démo.

## Livrables

- 12 tables.
- Seed complet.
- Login Agent.
- Navigation finale.
- `main` stable.

## Critères d’acceptation

- `schema.sql` et `seed.sql` s’importent sans erreur.
- Un Agent peut se connecter.
- Un client ne peut pas accéder à l’espace Agent.
- Toutes les routes sont protégées.

## Fichiers principaux

```text
sql/schema.sql
sql/seed.sql
sql/migrations/
config/app.php
Traitement/Auth.php
IHM/layout/header.php
index.php
docs/conception/msd/msd.puml
README.md
```

---

# Slide 9 — Membre 2 : réclamations et notifications

## Mission

Permettre au client de déposer une réclamation et au fournisseur de la traiter.

## J1 — Base et écrans

- Créer `reclamation` et `notification`.
- Préparer les vues :
  - dépôt client ;
  - liste client ;
  - liste fournisseur ;
  - détail.

## J2 — Fonctionnel

- Contrôleur `Traitement/Reclamation.php`.
- Modèle `BD/Reclamation.php`.
- Types :
  - `FUITE_EXTERNE` ;
  - `FUITE_INTERNE` ;
  - `FACTURE` ;
  - `AUTRE`.
- Description obligatoire pour `AUTRE`.
- Lien vers une facture si type `FACTURE`.

## J3 — Traitement et notifications

- Statuts :
  - `OUVERTE` ;
  - `EN_COURS` ;
  - `RESOLUE` ;
  - `REJETEE`.
- Réponse fournisseur.
- Service :
  ```php
  notification_creer($role, $idDestinataire, $message, $lien);
  ```
- Cloche avec compteur non lu.
- Marquage comme lu.

## J4 — Tests et documentation

- Client ne voit que ses réclamations.
- Fournisseur ne voit que ses clients.
- Notification créée après traitement.
- Capture d’écran.
- Section rapport.

## J5 — Buffer

- Corrections.
- Aide à la démo.

## Critères d’acceptation

- Les 4 types fonctionnent.
- `AUTRE` sans description refusé.
- Traitement fournisseur opérationnel.
- Notification visible.
- CSRF et contrôle d’accès validés.

## Fichiers principaux

```text
BD/Reclamation.php
BD/Notification.php
Traitement/Reclamation.php
Traitement/Notification.php
IHM/reclamation/
IHM/notification/
sql/migrations/002_reclamation_notification.sql
```

---

# Slide 10 — Membre 3 : paiements et tableau de bord

## Mission

Encaisser une facture et donner une vision claire de l’activité.

## J1 — Base et requêtes

- Créer `paiement` :
  - `id_paiement` ;
  - `id_facture` ;
  - `montant` ;
  - `date_paiement` ;
  - `mode_paiement` ;
  - `reference`.
- `UNIQUE(id_facture)`.
- Préparer les requêtes :
  - total impayé ;
  - total encaissé ;
  - évolution mensuelle.

## J2 — Paiement

- Formulaire fournisseur :
  - sélection d’une facture impayée ;
  - montant ;
  - date ;
  - mode.
- Contrôles :
  - facture existante ;
  - non déjà payée ;
  - montant = TTC.
- Transaction :
  - insertion du paiement ;
  - passage à `PAYEE`.
- Affichage du paiement sur la facture.

## J3 — Dashboard

- Total des factures impayées.
- Total encaissé.
- Évolution mensuelle eau / électricité.
- Filtre par année.
- Graphique simple en CSS/SVG.

## J4 — Tests et documentation

- Paiement correct accepté.
- Double paiement refusé.
- Montant incorrect refusé.
- Total impayé correct.
- Capture + section rapport.

## J5 — Buffer

- Corrections.
- Aide démo.

## Critères d’acceptation

- Une facture passe à `PAYEE`.
- Le dashboard se met à jour.
- Aucun accès client au paiement.
- Les totaux sont justes.

## Fichiers principaux

```text
BD/Paiement.php
Traitement/Paiement.php
IHM/paiement/
IHM/home.php
sql/migrations/003_paiement.sql
```

---

# Slide 11 — Membre 4 : import annuel, Agent et vérification

## Mission

Couvrir la fin d’année : fichier annuel, Agent, vérification des écarts.

## J1 — Base et parser

- Créer `conso_annuelle` :
  - `id_client` ;
  - `id_agent` ;
  - `conso_eau` ;
  - `conso_elec` ;
  - `annee` ;
  - `date_saisie`.
- `UNIQUE(id_client, annee)`.
- Parser le fichier :
  ```text
  ID_Client,ID_Agent,ConsommationEau,ConsommationElect,Annee,Date_Saisie
  ```
- Valider l’entête.
- Rejeter les lignes malformées.

## J2 — Espace Agent

- Upload du fichier `.txt`.
- Vérification du client.
- Ignorer les doublons.
- Rapport :
  - insérés ;
  - ignorés ;
  - rejetés.
- Fichier d’exemple.

## J3 — Vérification annuelle

- Page fournisseur.
- Choix de l’année.
- Comparaison mensuel vs annuel.
- Écarts par client.
- Notification via le service du Membre 2.

## J4 — Tests et documentation

- Fichier valide.
- Entête invalide.
- Ligne malformée.
- Client inconnu.
- Doublon.
- Capture + section rapport.

## J5 — Buffer

- Corrections.
- Aide démo.

## Critères d’acceptation

- Import valide fonctionnel.
- Rapport clair.
- Vérification annuelle affiche les écarts.
- Accès Agent/Fournisseur respecté.

## Fichiers principaux

```text
BD/ConsoAnnuelle.php
Traitement/Import.php
Traitement/Verification.php
IHM/import/
IHM/verification/
docs/examples/Consommation_Annuelle.txt
sql/migrations/001_agent_conso_annuelle.sql
```

---

# Slide 12 — Membre 5 : anomalies, facturation, QA, rapport

## Mission

Finaliser le cœur métier, garantir la qualité et produire le rapport final.

## J1 — Flux anomalies

- Appliquer la règle :
  - toute anomalie bloque la facture ;
  - correction obligatoire ;
  - correction fournisseur ;
  - revalidation ;
  - facture après correction.
- Corriger `AnomalieBD::corriger()`.
- Contrôler le seuil.

## J2 — Facture après correction

- Intégrer `FactureService`.
- Transaction :
  - correction ;
  - validation ;
  - facture.
- Refuser une correction invalide.
- Mettre à jour les vues.

## J3 — Tests et documentation

- Écrire les tests :
  - index inférieur ;
  - seuil dépassé ;
  - correction valide ;
  - correction invalide ;
  - facture générée une seule fois.
- Mettre à jour `tests/checklist.md`.

## J4 — QA et rapport

- QA complète :
  - `php -l` ;
  - reset base ;
  - tests desktop/mobile ;
  - captures.
- Mettre à jour :
  - périmètre ;
  - acteurs ;
  - besoins ;
  - base ;
  - modules ;
  - tests ;
  - limites.
- Ajouter les sections :
  - réclamations ;
  - paiements ;
  - import ;
  - dashboard ;
  - notifications.

## J5 — PDF final

- Compiler avec Tectonic.
- Vérifier le nombre de pages.
- Relire.
- Préparer la démo.

## Critères d’acceptation

- Aucune facture avant correction.
- Une correction valide génère une facture.
- Pas de doublon.
- PDF final compilé.
- Démo prête.

## Fichiers principaux

```text
Traitement/Anomalie.php
BD/AnomalieBD.php
Traitement/ConsommationTraitement.php
Traitement/FactureService.php
docs/latex/main.tex
docs/latex/sections/
tests/checklist.md
```

---

# Slide 13 — Dépendances et chemin critique

## Chaîne critique

```text
J1 : schéma + auth Agent
        ↓
J2 : modules backend
        ↓
J3 : intégration + notifications + dashboard
        ↓
J4 : tests + corrections
        ↓
J5 : rapport + release
```

## Dépendances fortes

- Membre 2 dépend du schéma `reclamation` / `notification`.
- Membre 3 dépend du schéma `paiement`.
- Membre 4 dépend du schéma `conso_annuelle` et de l’auth Agent.
- Membre 5 dépend de `FactureService` et de la décision sur les anomalies.
- Le rapport dépend des captures de J4.

## Règle

- Si le schéma n’est pas prêt J1 midi, tout le monde est bloqué.
- Le Membre 1 doit livrer le schéma **avant 12:00 J1**.

> **À dire :**  
> « Le chemin critique passe par le schéma et l’auth Agent. Tout le reste peut avancer en parallèle ensuite. »

---

# Slide 14 — Méthode de travail

## Rythme quotidien

| Heure | Action |
|---|---|
| 09:00 | Stand-up 15 min : hier / aujourd’hui / blocage |
| 13:00 | Point d’intégration |
| 18:00 | Merge + démo rapide |

## Git

- Une branche par membre :
  ```text
  feat/final-socle
  feat/reclamations-notifications
  feat/paiements-dashboard
  feat/import-annuel
  feat/anomalies-final
  docs/final-report
  ```
- Une PR par module.
- Review par un autre membre + Membre 1.
- Pas de push direct sur `main`.
- `php -l` avant chaque PR.
- Base réinitialisée avant les tests.

## Qualité

```bash
mysql -u root < sql/schema.sql
mysql -u root < sql/seed.sql

find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

> **À dire :**  
> « On merge tous les jours. Pas de big-bang le J5. »

---

# Slide 15 — Tests et démonstration finale

## Scénario de démo

### 1. Agent

- connexion ;
- import du fichier annuel ;
- rapport d’import.

### 2. Fournisseur

- dashboard : impayés + évolution ;
- ajout / modification d’un client ;
- consultation d’une anomalie ;
- correction ;
- facture générée ;
- paiement ;
- vérification annuelle.

### 3. Client

- consultation de la facture ;
- dépôt d’une réclamation ;
- notification après traitement.

## Contrôles

- Accès inter-role refusé.
- CSRF.
- Upload invalide.
- Montants HT/TVA/TTC.
- Absence de doublon de facture.
- Responsive desktop/mobile.

> **À dire :**  
> « La démo finale doit prouver le parcours complet : Agent → Fournisseur → Client. »

---

# Slide 16 — Risques et plans de repli

| Risque | Plan de repli |
|---|---|
| Schéma en retard | Membre 1 livre les tables minimales J1 midi |
| Import trop long | Parser CSV strict, UI simple, rapport basique |
| Notifications trop longues | Notification interne uniquement, pas d’e-mail |
| Dashboard en retard | Total impayé + un seul graphique |
| Anomalies ambiguës | Appliquer strictement l’énoncé |
| Rapport trop long | Compresser les sections existantes |
| Conflits Git | Un propriétaire par fichier, PR petites |
| Retard global | P0 d’abord, P1 abandonné |

> **À dire :**  
> « Le risque principal n’est pas le code, c’est le périmètre. On protège les P0. »

---

# Slide 17 — Definition of Done

La version finale est prête si :

- [ ] 12 tables créées et seedées.
- [ ] Login Client / Fournisseur / Agent.
- [ ] Anomalies : correction obligatoire + facture après correction.
- [ ] Réclamations : dépôt + traitement + notification.
- [ ] Paiement : facture passée à `PAYEE`.
- [ ] Dashboard : impayés + évolution.
- [ ] Import annuel + vérification.
- [ ] Notifications internes.
- [ ] Checklist de tests complète.
- [ ] Rapport final compilé.
- [ ] README à jour.
- [ ] Tag `v1.0-final`.
- [ ] Démo répétée sans erreur.

> **À dire :**  
> « Si une case n’est pas cochée, ce n’est pas une version finale. »

---

# Slide 18 — Décisions à prendre maintenant

## Vote rapide

1. Flux anomalies strict : **oui / non**
2. Notifications internes uniquement : **oui / non**
3. Paiement total uniquement : **oui / non**
4. Agent avec login séparé : **oui / non**
5. Rapport final autorisé à dépasser 30 pages : **oui / non**
6. Feature freeze J3 18:00 : **oui / non**

## Si désaccord

- On applique la recommandation par défaut.
- On note la décision dans le compte rendu.
- On ne rouvre pas le débat après J1.

> **À dire :**  
> « Chaque décision doit être prise maintenant. Après, on exécute. »

---

# Slide 19 — Prochaines étapes immédiates

## À la fin de la réunion

1. Attribuer les 5 rôles aux membres.
2. Créer les branches.
3. Le Membre 1 publie le schéma cible.
4. Chaque membre ouvre sa PR vide avec la checklist.
5. Premier stand-up : demain 09:00.

## Objectif de fin J1

- Base cible disponible.
- Login Agent fonctionnel.
- Chaque module a un squelette qui démarre.
- Aucun blocage non signalé.

> **Message de clôture :**  
> « Nous avons 5 jours. Le socle est solide. Si nous respectons le périmètre, le freeze J3 et les points de contrôle quotidiens, la version finale est atteignable. »

---

# Annexe A — Checklist par membre

## Membre 1

- [ ] Schéma cible publié.
- [ ] 5 nouvelles tables.
- [ ] `schema.sql` et `seed.sql` à jour.
- [ ] Auth Agent.
- [ ] Navigation activée.
- [ ] PR mergées.
- [ ] `main` stable.

## Membre 2

- [ ] Tables `reclamation` et `notification`.
- [ ] Dépôt client.
- [ ] Liste client.
- [ ] Liste fournisseur.
- [ ] Traitement fournisseur.
- [ ] Notification client.
- [ ] Cloche.
- [ ] Tests + capture.

## Membre 3

- [ ] Table `paiement`.
- [ ] Formulaire de paiement.
- [ ] Passage à `PAYEE`.
- [ ] Affichage sur facture.
- [ ] Total impayé.
- [ ] Graphique mensuel.
- [ ] Tests + capture.

## Membre 4

- [ ] Table `conso_annuelle`.
- [ ] Parser du fichier.
- [ ] Espace Agent.
- [ ] Rapport d’import.
- [ ] Vérification annuelle.
- [ ] Fichier d’exemple.
- [ ] Tests + capture.

## Membre 5

- [ ] Flux anomalies finalisé.
- [ ] Facture après correction.
- [ ] Tests anomalies.
- [ ] Checklist complète.
- [ ] Captures.
- [ ] Rapport final.
- [ ] PDF compilé.

---

# Annexe B — Tables cibles

```text
fournisseur
client
tarif
consommation
photo_capteur
anomalie
facture
agent
conso_annuelle
reclamation
paiement
notification
```

## Nouvelles relations principales

| Relation | Cardinalité |
|---|---|
| Agent — Conso_Annuelle | 1..1 — 0..n |
| Client — Conso_Annuelle | 1..1 — 0..n |
| Client — Reclamation | 1..1 — 0..n |
| Facture — Reclamation | 0..1 — 0..n |
| Facture — Paiement | 1..1 — 0..1 |
| Client — Notification | 1..1 — 0..n |
| Fournisseur — Notification | 1..1 — 0..n |

---

# Annexe C — Routes cibles

```text
GET/POST /Reclamation/add
GET      /Reclamation/all
GET      /Reclamation/one/{id}
GET/POST /Reclamation/traiter/{id}

GET      /Paiement/all
GET/POST /Paiement/add/{idFacture}

GET/POST /Import/index
POST     /Import/upload

GET      /Verification/index

GET      /Notification/all
POST     /Notification/lire/{id}
```

---

# Annexe D — Scénario de démonstration minuté

| Temps | Action |
|---|---|
| 0:00 | Connexion Agent |
| 0:30 | Import du fichier annuel |
| 1:00 | Rapport d’import |
| 1:30 | Connexion Fournisseur |
| 2:00 | Dashboard : impayés + évolution |
| 3:00 | Consultation et correction d’une anomalie |
| 4:00 | Facture générée |
| 4:30 | Paiement de la facture |
| 5:00 | Vérification annuelle |
| 5:30 | Connexion Client |
| 6:00 | Consultation de la facture |
| 6:30 | Dépôt d’une réclamation |
| 7:00 | Traitement fournisseur |
| 7:30 | Notification client |
| 8:00 | Conclusion |

---

# Annexe E — Script de clôture

> « Nous avons validé le périmètre, les rôles et le planning.
> Le socle Alpha est stable.
> Les cinq jours sont organisés autour de cinq blocs : anomalies, réclamations, paiements, import/Agent, dashboard/notifications.
> Le freeze est fixé à J3 18:00.
> La version finale est considérée terminée lorsque la checklist Definition of Done est complète.
> Chacun connaît son rôle et sa première tâche.
> Nous nous retrouvons demain à 09:00 pour le premier stand-up. »

---

# Annexe F — Règles de communication

- Un blocage doit être signalé dans les 30 minutes.
- Pas de silence pendant plus d’une demi-journée.
- Une PR doit être reviewée dans les 2 heures.
- Un membre bloqué aide un autre membre.
- Les décisions sont écrites dans le compte rendu.
- Les captures d’écran sont prises au fur et à mesure, pas le J5.

---

# Annexe G — Ce que nous ne faisons pas

Pour protéger la livraison :

- pas d’e-mail tant que l’interne n’est pas stable ;
- pas d’export PDF de facture ;
- pas de paiements partiels ;
- pas de multi-fournisseurs ;
- pas de refonte graphique ;
- pas de nouvelles fonctionnalités après J3 ;
- pas de merge sans test ;
- pas de modification directe de `main`.

---

# Conclusion de la présentation

La version finale est atteignable en 5 jours si :

1. le périmètre est respecté ;
2. le schéma est prêt J1 midi ;
3. chaque membre travaille sur son périmètre ;
4. les PR sont petites et reviewées ;
5. le freeze J3 est respecté ;
6. les tests et le rapport sont traités dès J3, pas le J5.

**Objectif final :**

```text
Application finale fonctionnelle
+ rapport final compilé
+ démonstration répétée
+ tag v1.0-final
```
