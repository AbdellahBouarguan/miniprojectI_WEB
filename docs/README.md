# Documentation du projet

## Organisation

```text
docs/
├── pdf/                 Enonce, rapport de conception, diagramme de cas d'utilisation
├── conception/
│   ├── diagrams/        Diagrammes de sequence PlantUML, PNG et SVG
│   └── msd/             Modele de donnees : msd.puml, msd.png, msd.svg
├── latex/               Rapport LaTeX du projet
└── reunions/            Comptes rendus des reunions
```

## Modele de donnees

Le fichier source est `docs/conception/msd/msd.puml`.
Le PNG et le SVG sont generes a partir de ce fichier.

Telecharger PlantUML si necessaire :

```bash
curl -L -o plantuml.jar https://github.com/plantuml/plantuml/releases/latest/download/plantuml.jar
```

Generer les images :

```bash
java -jar plantuml.jar -tpng docs/conception/msd/msd.puml
java -jar plantuml.jar -tsvg docs/conception/msd/msd.puml
```

## Rapport LaTeX

Le rapport final est dans `docs/latex/`.

```bash
cd docs/latex
tectonic -X compile main.tex
```

Le PDF final est enregistré dans :

```text
docs/latex/rapport_mini_projet_1_alpha.pdf
```

Chaque partie du rapport est rédigée dans un fichier distinct de `docs/latex/sections/`.
Les captures desktop sont dans `docs/latex/figures/`.

## Comptes rendus

Les comptes rendus de reunion sont dans `docs/reunions/`.
