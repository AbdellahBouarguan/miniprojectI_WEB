# Mini Projet 1 - Version finale

Application web PHP de gestion des factures d'eau et d'electricite.

Branche stable : `main`

## Perimetre

- Gestion des clients
- Saisie des consommations et televersement des photos
- Detection et confirmation des anomalies
- Generation automatique et consultation des factures
- Notifications internes client / fournisseur
- Quartiers, contrats clients et rattachement des consommations
- Agent et import annuel
- Verification annuelle
- Reclamations client / fournisseur
- Paiement total des factures

## Pre-requis

- PHP 8.2 ou superieur
- MariaDB ou MySQL
- Apache avec `mod_rewrite`, ou le serveur integre PHP pour le developpement

## Base de donnees

Base : `miniproject_version_alpha`

```bash
mysql -u root < sql/schema.sql
mysql -u root < sql/seed.sql
```

Ou importer `sql/schema.sql` puis `sql/seed.sql` dans phpMyAdmin.

Les tables ajoutées pendant le sprint sont livrées à la fois dans `sql/schema.sql`
(import complet) et dans `sql/migrations/` (DDL par module), par exemple
`sql/migrations/001_agent_conso_annuelle.sql` pour le module Agent.

## Configuration

Copier `config/database.example.php` vers `config/database.php` et adapter les identifiants locaux.

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'miniproject_version_alpha',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];
```

## Lancement

### Avec Apache

Placer le projet dans le repertoire web d'Apache, puis ouvrir :

```text
http://localhost/miniprojectI_WEB/
```

### Sans Apache, avec le serveur integre PHP

```bash
cd /home/boua/Downloads/miniprojectI_WEB
php -S 127.0.0.1:8000 router.php
```

Ouvrir ensuite :

```text
http://127.0.0.1:8000/
```

## Routes

```text
GET/POST /Auth/login
GET      /Auth/logout

GET      /Client/all
GET      /Client/one/{id}
GET/POST /Client/add
GET/POST /Client/update/{id}

GET/POST /Consommation/add
GET/POST /Consommation/add/{id}
GET      /Consommation/all
GET      /Consommation/one/{id}
GET      /Consommation/confirmation/{id}
POST     /Consommation/confirmer
POST     /Consommation/facturer

GET      /Contrat/all
POST     /Contrat/add/{idClient}
POST     /Contrat/resilier/{id}

GET      /Quartier/all
POST     /Quartier/add

GET      /Facture/all
GET      /Facture/one/{id}
POST     /Facture/payer/{id}

GET      /Reclamation/index
GET/POST /Reclamation/add
GET      /Reclamation/one/{id}
POST     /Reclamation/traiter/{id}

GET      /Verification/index

GET      /Anomalie/index

GET      /Notification/all
POST     /Notification/lire/{id}
POST     /Notification/lire_tout

GET/POST /Import/index
POST     /Import/upload
```

Le paiement intégral marque la facture comme payée et enregistre la date dans l'application. Il ne s'agit pas d'un paiement bancaire en ligne.
Pour une base déjà installée, appliquer une seule fois `sql/migrations/002_reclamation_paiement.sql`.

Flux des anomalies :

- `INDEX_INFERIEUR` : correction obligatoire.
- `SEUIL_DEPASSE` : confirmation ou correction.
- Après confirmation/correction valide : facture générée automatiquement.

Le routeur appelle la fonction `{controleur}_{action}`, par exemple `client_all()` ou `facture_show()`.

## Module Agent — import annuel

Le rôle `agent` dispose d’un espace dédié (`/Import/index`) pour déposer le fichier
`Consommation_Annuelle.txt` relevé lors de la tournée annuelle.

Format attendu (en-tête obligatoire) :

```text
ID_Client,ID_Agent,ConsommationEau,ConsommationElect,Annee,Date_Saisie
1,1,120.50,340.00,2026,2026-12-15
```

- L’import est automatique dès le dépôt du fichier.
- Un rapport indique les lignes **insérées**, **ignorées** (doublon client/année) et **rejetées** (ligne malformée, client ou agent inconnu, valeur invalide, date invalide).
- La contrainte `UNIQUE(id_client, annee)` garantit un seul relevé par client et par an.
- Fichiers d’exemple : `docs/examples/Consommation_Annuelle.txt` et `docs/examples/Consommation_Annuelle_erreurs.txt`.

## Tableau de bord fournisseur

La page d’accueil du fournisseur affiche :

- le **montant total des factures impayées** et le **total encaissé** (factures `PAYEE`) ;
- le nombre de factures impayées / payées, les clients et les anomalies en attente ;
- l’**évolution mensuelle** des consommations d’eau et d’électricité (graphiques SVG) ;
- un filtre par année.

Les indicateurs sont calculés par `BD/Dashboard.php` à partir de la table `facture`.

## Structure

```text
index.php          Routeur
config/            Configuration application et base de donnees
BD/                Acces aux donnees
Traitement/        Controleurs et logique metier
IHM/               Vues
assets/            CSS, JS, images
uploads/           Photos des compteurs
sql/               Schema et donnees de depart
docs/              PDF, conception, MSD, LaTeX, reunions
tests/             Checklist de tests
```

## Comptes de test

```text
Fournisseur : fournisseur@example.ma / fournisseur123
Agent       : agent@example.ma       / agent123
Client 1    : client1@example.ma     / client123
Client 2    : client2@example.ma     / client123
Client 3    : client3@example.ma     / client123
```

## Equipe

- Abdellah Bouarguan : coordination, setup, SQL, Git/GitHub
- Nafisa El Younoussi : saisie des consommations et generation de facture
- Reda Zakaria : gestion des clients
- Nizar Ben Yacoub : saisie des consommations et generation de facture
- Aymen Ouggourni : factures

## Rapport final

```text
docs/latex/rapport_mini_projet_1_alpha.pdf
```

Sources LaTeX : `docs/latex/main.tex` et `docs/latex/sections/`.

## Depot GitHub

```text
https://github.com/AbdellahBouarguan/miniprojectI_WEB
```

```bash
git clone https://github.com/AbdellahBouarguan/miniprojectI_WEB.git
```

## Git

- `main` est la branche stable.
- Une branche par tache : `feat/...`, `fix/...`, `chore/...`, `docs/...`.
- Une Pull Request par branche.
- Au moins une approbation avant merge.
- Merge en `Squash and merge`, puis supprimer la branche.
- Le depot est public.
- Regle d'equipe : aucun push direct sur `main`.
- La protection de branche GitHub peut etre activee sur un depot public.
