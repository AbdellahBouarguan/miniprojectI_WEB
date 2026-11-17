# Campagne finale — tests et captures

Campagne du **17/11/2026** après intégration des modules :

- Agent + import annuel ;
- dashboard fournisseur ;
- vérification annuelle ;
- réclamations ;
- paiement fournisseur ;
- notifications ;
- quartiers et contrats ;
- anomalies ;
- factures.

## Contenu

| Élément | Valeur |
|---|---|
| Captures desktop | 19 |
| Captures responsive | 27 |
| Planches contact | 2 |
| Total images | 48 |
| Navigateur | Brave headless |
| Desktop | 1440 × 900 |
| Tablette | 1024 × 768 |
| Mobile | 390 × 844 |
| Base | MariaDB 11.8 |
| PHP | 8.4.27 |

## Documents

- `plan.md` : scénarios et critères ;
- `results.md` : résultats détaillés ;
- `bugs.md` : bugs corrigés et limites ;
- `screenshots/` : captures desktop et responsive.

## Captures principales

```text
screenshots/desktop/01_agent_import_page.png
screenshots/desktop/02_agent_import_report.png
screenshots/desktop/02b_agent_import_errors.png
screenshots/desktop/04_reclamation_add_success.png
screenshots/desktop/06_supplier_dashboard.png
screenshots/desktop/08_reclamation_detail.png
screenshots/desktop/10_verification_page.png
screenshots/desktop/13_payment_invoice_unpaid.png
screenshots/desktop/14_payment_success.png
screenshots/desktop/18_client_dashboard_final.png
```

## Liens activés

Les trois liens sont maintenant actifs :

- **Réclamations** → `/Reclamation/index` ;
- **Paiements** → `/Facture/all` avec paiement fournisseur ;
- **Vérification annuelle** → `/Verification/index`.

Il ne reste aucun lien `#` ou `disabled` dans le header.
