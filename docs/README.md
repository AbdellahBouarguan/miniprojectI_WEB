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

```bash
java -jar plantuml.jar -tpng docs/conception/msd/msd.puml
java -jar plantuml.jar -tsvg docs/conception/msd/msd.puml
```

## Rapport LaTeX

Le rapport est dans `docs/latex/`.

```bash
cd docs/latex
latexmk -pdf main.tex
```

Chaque membre redige sa partie dans un fichier distinct de `docs/latex/sections/`.
Cela evite les conflits Git.

## Comptes rendus

Les comptes rendus de reunion sont dans `docs/reunions/`.
