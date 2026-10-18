# Configuration GitHub

## Creation du depot

1. Creer un depot vide sur GitHub : `miniprojectI_WEB`.
2. Ne pas ajouter de README ni de `.gitignore` sur GitHub.
3. Dans le projet local :

```bash
git remote add origin https://github.com/<compte>/miniprojectI_WEB.git
git branch -M main
git push -u origin main
```

## Collaborateurs

GitHub > Settings > Collaborators > Add people.

Ajouter :

- Nafisa El Younoussi
- Reda Zakaria
- Nizar Ben Yacoub
- Aymen Ouggourni

Role : `Write`.

## Protection de main

GitHub > Settings > Branches ou Rulesets :

- Require a pull request before merging
- Require approvals : 1
- Dismiss stale pull request approvals
- Require conversation resolution
- Interdire le push direct sur `main`

## Labels

- `module:clients`
- `module:consommation`
- `module:factures`
- `module:anomalies`
- `type:feature`
- `type:bug`
- `type:docs`
- `priorite:haute`
- `bloque`

## Milestones

- J1 - Setup
- J2 - Clients
- J3 - Consommations et factures
- J4 - Alpha

## Workflow

```bash
git switch main
git pull origin main
git switch -c feat/nom-de-la-tache

git add <fichiers>
git commit -m "feat(module): description"
git push -u origin feat/nom-de-la-tache
```

Puis ouvrir une Pull Request, lier l'issue avec `Closes #N`, demander une review, faire un `Squash and merge`, puis supprimer la branche.

## Regles

- Une branche par tache.
- Une Pull Request par branche.
- Au moins une approbation.
- Ne jamais pousser directement sur `main`.
- Ne pas committer `config/database.php`.
- Ne pas committer les photos televersees.
- Ne pas committer les fichiers auxiliaires LaTeX.
