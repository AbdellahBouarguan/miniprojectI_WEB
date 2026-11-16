# Notifications internes

## Objectif

Informer un client, un fournisseur ou un agent sans dépendre d’un serveur e-mail.

## Table

Migration : `sql/migrations/004_notification.sql`

```sql
notification (
  id_notification,
  destinataire_role,
  id_destinataire,
  type,
  titre,
  message,
  lien,
  lu,
  date_creation,
  date_lecture
)
```

Le destinataire est identifié par `(destinataire_role, id_destinataire)`.
Il n’y a pas de clé étrangère, car le destinataire peut être un client, un fournisseur ou un agent.

## Service

Fichier : `BD/Notification.php`

```php
notification_creer($role, $idDestinataire, $type, $titre, $message, $lien);
notification_lister($role, $idDestinataire);
notification_compter_non_lues($role, $idDestinataire);
notification_marquer_lue($idNotification, $role, $idDestinataire);
notification_marquer_toutes_lues($role, $idDestinataire);
```

## Routes

```text
GET  /Notification/all
POST /Notification/lire/{id}
POST /Notification/lire_tout
```

## Événements prévus

| Événement | Destinataire | Type |
|---|---|---|
| Facture générée | Client | `FACTURE` |
| Anomalie détectée | Fournisseur | `ANOMALIE` |
| Anomalie corrigée | Client | `ANOMALIE` |
| Réclamation créée | Fournisseur | `RECLAMATION` |
| Réclamation traitée | Client | `RECLAMATION` |
| Paiement enregistré | Client | `PAIEMENT` |
| Écart annuel détecté | Client | `VERIFICATION` |
| Import annuel terminé | Agent / Fournisseur | `VERIFICATION` |

La notification doit être créée **après le commit** de l’action métier.

## Exemple d’appel

```php
notification_creer(
    'client',
    $idClient,
    'FACTURE',
    'Nouvelle facture disponible',
    'Votre facture ' . $numero . ' est disponible.',
    'Facture/one/' . $idFacture
);
```

## Intégration déjà réalisée

- `Traitement/FactureService.php` : notification au client après génération d’une facture.

## Intégrations à faire par les autres modules

- Réclamations : Reda.
- Paiement : Reda.
- Anomalies : Aymane.
- Import / vérification : Nizar et Nafisa.

## Tests

- compteur non lu ;
- isolation client / fournisseur / agent ;
- marquage comme lu ;
- tout marquer comme lu ;
- CSRF ;
- lien interne ;
- notification après facture.

## Intégration MSD

La table `notification` doit être ajoutée au MSD par Aymane, puis les images
`msd.png` et `msd.svg` doivent être régénérées.

