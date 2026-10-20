# Configuration GitHub

## Depot

- URL : https://github.com/AbdellahBouarguan/miniprojectI_WEB
- Visibilite : privee
- Branche principale : `main`

```bash
git clone https://github.com/AbdellahBouarguan/miniprojectI_WEB.git
```

## Collaborateurs

Invitations envoyees avec le role `Write` :

| Membre | Compte GitHub |
|---|---|
| Nafisa El Younoussi | `nafisa-elyounoussi` |
| Reda Zakaria | `reda465` |
| Nizar Ben Yacoub | `nizarbenyacoub3` |
| Aymen Ouggourni | `OuggourniAymen` |

Chaque membre doit accepter l'invitation recue par email ou sur GitHub.

## Protection de main

La protection de branche GitHub n'est pas disponible sur un depot prive gratuit.
Regle d'equipe : aucun push direct sur `main`, une branche par tache et une Pull Request par branche.

Si le depot devient public ou passe en GitHub Pro, activer :

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
- Pas de IA