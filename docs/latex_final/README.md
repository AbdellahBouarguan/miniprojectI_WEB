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

## Responsable du rapport

**Nafisa El Younoussi** est responsable de l'ensemble du rapport final.

Elle prend en charge :

- la rédaction de tous les chapitres et sous-sections ;
- l'intégration des contenus techniques fournis par l'équipe ;
- la cohérence du vocabulaire, des labels, des figures et des tableaux ;
- la mise à jour de `main.tex` ;
- la compilation du PDF final ;
- la relecture finale et la vérification du nombre de pages.

Les autres membres fournissent les éléments techniques, les captures et les vérifications de leur module, mais **Nafisa est seule responsable de la rédaction et de la compilation du rapport**.

## Contribution de l'équipe

| Contribution | Responsable |
|---|---|
| Rédaction complète du rapport | **Nafisa El Younoussi** |
| Contenus techniques et captures | Tous les membres |
| Vérification des modules | Tous les membres |
| Relecture | Tous les membres |
| Compilation et PDF final | **Nafisa El Younoussi** |

## Règles

- Nafisa peut modifier tous les fichiers de `sections/` et `main.tex`.
- Les autres membres ne modifient pas le rapport directement ; ils transmettent leurs textes, tableaux et captures à Nafisa.
- Ne pas renommer les labels sans vérifier les références.
- Utiliser `\texttt{}` pour les noms de fichiers et de tables.
- Échapper `_` dans les titres : `conso\_annuelle`.
- Ajouter les figures dans `figures/` ou référencer `docs/tests/final/`.
- Compiler avant chaque commit.
- Ne pas committer `main.pdf` ; seul le PDF final `rapport_mini_projet_1_final.pdf` est versionné.
