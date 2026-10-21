# Mini Projet 1 - Version Alpha

Application web PHP de gestion des factures d'eau et d'electricite.

Branche Alpha : `aymane`

## Perimetre Alpha

- Gestion des clients
- Saisie des consommations et televersement des photos
- Detection et confirmation des anomalies
- Generation automatique et consultation des factures

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

GET      /Facture/all
GET      /Facture/one/{id}

GET      /Anomalie/index
```

Flux des anomalies :

- `INDEX_INFERIEUR` : correction obligatoire.
- `SEUIL_DEPASSE` : confirmation ou correction.
- Après confirmation/correction valide : facture générée automatiquement.

Le routeur appelle la fonction `{controleur}_{action}`, par exemple `client_all()` ou `facture_show()`.

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
- Le depot est prive. La protection de branche GitHub necessite GitHub Pro ou un depot public.
- Regle d'equipe : aucun push direct sur `main`.
