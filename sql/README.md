# Base de donnees - Version Alpha

Base : `miniproject_version_alpha`

## Import en ligne de commande

```bash
mysql -u root < sql/schema.sql
mysql -u root < sql/seed.sql
```

Si le compte root possede un mot de passe :

```bash
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/seed.sql
```

## Import via phpMyAdmin

1. Ouvrir phpMyAdmin.
2. Onglet `Importer`.
3. Choisir `sql/schema.sql`.
4. Executer.
5. Repeter avec `sql/seed.sql`.

## Verification

```sql
USE miniproject_version_alpha;
SHOW TABLES;
SELECT * FROM fournisseur;
SELECT * FROM client;
SELECT * FROM tarif ORDER BY type, borne_min;
```

## Tables

- `fournisseur`
- `client`
- `tarif`
- `consommation`
- `photo_capteur`
- `anomalie`
- `facture`

## Remarque

`sql/schema.sql` recree les tables. Toutes les donnees existantes sont supprimees.
`sql/seed.sql` insere le fournisseur, trois clients et les sept tarifs de depart.
