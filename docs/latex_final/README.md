# Rapport LaTeX final

## Structure

```text
docs/latex_final/
├── main.tex
├── figures/
└── sections/
    ├── 00_page_garde.tex
    ├── 01_remerciements.tex
    ├── 02_resume.tex
    ├── 03_abstract.tex
    ├── 04_abreviations.tex
    ├── 05_introduction.tex
    ├── 06_presentation.tex
    ├── 07_besoins.tex
    ├── 08_architecture.tex
    ├── 09_basededonnees.tex
    ├── 10_auth_securite.tex
    ├── 11_clients.tex
    ├── 12_quartiers_contrats.tex
    ├── 13_consommation.tex
    ├── 14_anomalies.tex
    ├── 15_facturation.tex
    ├── 16_reclamations.tex
    ├── 17_paiements.tex
    ├── 18_agent_import.tex
    ├── 19_verification.tex
    ├── 20_notifications.tex
    ├── 21_dashboard.tex
    ├── 22_tests.tex
    ├── 23_bilan.tex
    ├── 24_conclusion.tex
    ├── 80_bibliographie.tex
    ├── 81_glossaire.tex
    └── annexes 90 à 95
```

## Compilation

```bash
cd docs/latex_final
tectonic -X compile main.tex
```

Le PDF produit est `main.pdf`. La version compilée est enregistrée sous :

```text
docs/latex_final/rapport_mini_projet_1_final.pdf
```

`main.pdf` est ignoré par Git.

## Répartition du travail

Chaque membre remplit uniquement les fichiers de ses chapitres.
Les commentaires `% TODO` et les commandes `\todo{...}` indiquent les parties à compléter.

| Membre | Fichiers |
|---|---|
| Membre 1 | 05 à 10, 90, 91 |
| Membre 2 | 11, 12, 16, 20 |
| Membre 3 | 13, 14, 15, 17 |
| Membre 4 | 18, 19, 21 |
| Membre 5 | 22, 23, 24, 80, 81, 92 à 95, compilation |

## Règles

- Ne pas modifier `main.tex` sans accord de l'intégrateur.
- Ne pas renommer les labels sans vérifier les références.
- Utiliser `\texttt{}` pour les noms de fichiers et de tables.
- Échapper `_` dans les titres : `conso\_annuelle`.
- Ajouter les figures dans `figures/` ou référencer `docs/tests/final/`.
- Compiler avant chaque commit.
- Ne pas committer `main.pdf` si le dépôt ne doit pas contenir le PDF intermédiaire.
