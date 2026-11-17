<?php
declare(strict_types=1);

final class ReclamationBD
{
    public const TYPES = ['FUITE_EXTERNE', 'FUITE_INTERNE', 'FACTURE', 'AUTRE'];
    public const STATUTS = ['OUVERTE', 'EN_COURS', 'RESOLUE', 'REJETEE'];

    public function __construct(private PDO $pdo) {}

    public function creer(
        int $idClient,
        string $type,
        string $description,
        ?int $idFacture = null
    ): int {
        $numero = 'REC-' . date('YmdHis') . '-' . bin2hex(random_bytes(2));

        $stmt = $this->pdo->prepare(
            'INSERT INTO reclamation
                (numero, id_client, id_facture, type, description, statut)
             VALUES
                (:numero, :client, :facture, :type, :description, :statut)'
        );
        $stmt->execute([
            'numero' => $numero,
            'client' => $idClient,
            'facture' => $idFacture,
            'type' => $type,
            'description' => $description,
            'statut' => 'OUVERTE',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function listerParClient(int $idClient): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, f.numero AS facture_numero
             FROM reclamation r
             LEFT JOIN facture f ON f.id_facture = r.id_facture
             WHERE r.id_client = :client
             ORDER BY r.date_creation DESC, r.id_reclamation DESC'
        );
        $stmt->execute(['client' => $idClient]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string, mixed>> */
    public function listerParFournisseur(int $idFournisseur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.nom, c.prenom, c.cin, f.numero AS facture_numero
             FROM reclamation r
             JOIN client c ON c.id_client = r.id_client
             LEFT JOIN facture f ON f.id_facture = r.id_facture
             WHERE c.id_fournisseur = :fournisseur
             ORDER BY FIELD(r.statut, \'OUVERTE\', \'EN_COURS\', \'RESOLUE\', \'REJETEE\'),
                      r.date_creation DESC'
        );
        $stmt->execute(['fournisseur' => $idFournisseur]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouverPourClient(int $idReclamation, int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, f.numero AS facture_numero
             FROM reclamation r
             LEFT JOIN facture f ON f.id_facture = r.id_facture
             WHERE r.id_reclamation = :id AND r.id_client = :client
             LIMIT 1'
        );
        $stmt->execute(['id' => $idReclamation, 'client' => $idClient]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function trouverPourFournisseur(int $idReclamation, int $idFournisseur): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.nom, c.prenom, c.cin, c.email, f.numero AS facture_numero
             FROM reclamation r
             JOIN client c ON c.id_client = r.id_client
             LEFT JOIN facture f ON f.id_facture = r.id_facture
             WHERE r.id_reclamation = :id AND c.id_fournisseur = :fournisseur
             LIMIT 1'
        );
        $stmt->execute(['id' => $idReclamation, 'fournisseur' => $idFournisseur]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function traiter(
        int $idReclamation,
        int $idFournisseur,
        string $statut,
        string $reponse
    ): bool {
        $dateResolution = in_array($statut, ['RESOLUE', 'REJETEE'], true)
            ? date('Y-m-d H:i:s')
            : null;

        $stmt = $this->pdo->prepare(
            'UPDATE reclamation r
             JOIN client c ON c.id_client = r.id_client
             SET r.statut = :statut,
                 r.reponse = :reponse,
                 r.date_resolution = :resolution
             WHERE r.id_reclamation = :id
               AND c.id_fournisseur = :fournisseur'
        );
        $stmt->execute([
            'statut' => $statut,
            'reponse' => $reponse,
            'resolution' => $dateResolution,
            'id' => $idReclamation,
            'fournisseur' => $idFournisseur,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function fournisseurDuClient(int $idClient): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_fournisseur FROM client WHERE id_client = :client LIMIT 1'
        );
        $stmt->execute(['client' => $idClient]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /** Factures du client, pour le type FACTURE. */
    public function facturesClient(int $idClient): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_facture, numero, periode_mois, periode_annee, montant_ttc
             FROM facture
             WHERE id_client = :client
             ORDER BY periode_annee DESC, periode_mois DESC'
        );
        $stmt->execute(['client' => $idClient]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function reclamation_bd(): ReclamationBD
{
    return new ReclamationBD(pdo());
}

function reclamation_type_label(string $type): string
{
    return match ($type) {
        'FUITE_EXTERNE' => 'Fuite externe',
        'FUITE_INTERNE' => 'Fuite interne',
        'FACTURE' => 'Facture',
        'AUTRE' => 'Autre',
        default => $type,
    };
}

function reclamation_statut_label(string $statut): string
{
    return match ($statut) {
        'OUVERTE' => 'Ouverte',
        'EN_COURS' => 'En cours',
        'RESOLUE' => 'Résolue',
        'REJETEE' => 'Rejetée',
        default => $statut,
    };
}

function reclamation_statut_badge(string $statut): string
{
    return match ($statut) {
        'OUVERTE' => 'badge-warning',
        'EN_COURS' => 'badge-warning',
        'RESOLUE' => 'badge-open',
        'REJETEE' => 'badge-danger',
        default => '',
    };
}
