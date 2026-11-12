# Présentation — Sprint final 5 jours

Ce dossier contient la présentation de préparation du sprint final.

## Fichiers

| Fichier | Usage |
|---|---|
| `sprint_final_5_jours.md` | Version détaillée, avec les explications, les guides par membre et les annexes |
| `sprint_final_5_jours.pdf` | Version projetable, 22 slides au format 16:9 |
| `sprint_final_5_jours.html` | Source HTML de la version projetable |

## Utilisation

- Pour animer la réunion : ouvrir le PDF.
- Pour préparer ou modifier le contenu : éditer le Markdown.
- Pour régénérer le PDF après modification du HTML :

```bash
cd docs/presentation
brave-browser --headless --disable-gpu --no-sandbox \
  --print-to-pdf=sprint_final_5_jours.pdf \
  file://$PWD/sprint_final_5_jours.html
```

## Remarque

Les libellés **Membre 1 à Membre 5** sont volontairement anonymes.
Ils doivent être remplacés manuellement par les prénoms de l’équipe avant la réunion.
