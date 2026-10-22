# Rapport LaTeX

## Structure

```text
docs/latex/
├── main.tex
├── sections/
│   ├── 00_remerciements.tex
│   ├── 01_resume.tex
│   ├── 02_introduction.tex
│   ├── 03_presentation.tex
│   ├── 04_besoins.tex
│   ├── 05_architecture.tex
│   ├── 06_basededonnees.tex
│   ├── 07_clients.tex
│   ├── 08_consommation.tex
│   ├── 09_facturation.tex
│   ├── 10_securite.tex
│   ├── 11_tests.tex
│   ├── 12_bilan.tex
│   ├── 13_conclusion.tex
│   ├── 14_bibliographie.tex
│   ├── 15_glossaire.tex
│   └── 16_annexes.tex
├── figures/
│   └── captures desktop
└── README.md
```

## Compilation avec Tectonic

Tectonic est utilisé pour générer le PDF sans installation système de TeX Live.

```bash
cd docs/latex
tectonic -X compile main.tex
```

Dans l'environnement de développement, le binaire portable utilisé était :

```bash
/tmp/opencode/tectonic/tectonic -X compile main.tex
```

Le PDF produit est ensuite renommé :

```bash
mv main.pdf rapport_mini_projet_1_alpha.pdf
```

`main.pdf` est ignoré par Git ; seul le PDF final nommé est versionné.

## Compilation avec TeX Live

Si TeX Live est installé :

```bash
cd docs/latex
latexmk -pdf main.tex
```

## Figures

Les captures d'écran desktop sont dans `figures/`.
Les diagrammes de conception sont référencés depuis `../conception/` et `../pdf/`.

## PDF final

Le PDF final est enregistré dans :

```text
docs/latex/rapport_mini_projet_1_alpha.pdf
```
