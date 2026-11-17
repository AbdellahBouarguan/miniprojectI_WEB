<?php
declare(strict_types=1);

/**
 * Contrats : un client peut avoir plusieurs contrats dans des
 * quartiers differents. Une consommation est rattachee a un contrat.
 */
final class ContratBD
{
    public function __construct(private PDO $pdo) {}

    /** @return array<int, array<string, mixed>> */
    public function listerParClient(int $idClient): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.*, q.nom AS quartier_nom, q.type_activite
             FROM contrat ct
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE ct.id_client = :client
             ORDER BY ct.statut, ct.numero_contrat'
        );
        $stmt->execute(['client' => $idClient]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string, mixed>> */
    public function listerParFournisseur(int $idFournisseur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.*, q.nom AS quartier_nom, q.type_activite,
                    c.nom, c.prenom, c.cin
             FROM contrat ct
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             JOIN client c ON c.id_client = ct.id_client
             WHERE c.id_fournisseur = :fournisseur
             ORDER BY c.nom, c.prenom, ct.numero_contrat'
        );
        $stmt->execute(['fournisseur' => $idFournisseur]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouver(int $idContrat, int $idFournisseur): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.*, q.nom AS quartier_nom, q.type_activite,
                    c.nom, c.prenom, c.cin, c.id_fournisseur
             FROM contrat ct
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             JOIN client c ON c.id_client = ct.id_client
             WHERE ct.id_contrat = :id AND c.id_fournisseur = :fournisseur
             LIMIT 1'
        );
        $stmt->execute(['id' => $idContrat, 'fournisseur' => $idFournisseur]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function trouverPourClient(int $idContrat, int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.*, q.nom AS quartier_nom, q.type_activite
             FROM contrat ct
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE ct.id_contrat = :id AND ct.id_client = :client
             LIMIT 1'
        );
        $stmt->execute(['id' => $idContrat, 'client' => $idClient]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function appartientClient(int $idContrat, int $idClient): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM contrat
             WHERE id_contrat = :id AND id_client = :client
             LIMIT 1'
        );
        $stmt->execute(['id' => $idContrat, 'client' => $idClient]);

        return (bool) $stmt->fetchColumn();
    }

    public function numeroExiste(string $numero): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM contrat WHERE numero_contrat = :numero LIMIT 1'
        );
        $stmt->execute(['numero' => $numero]);

        return (bool) $stmt->fetchColumn();
    }

    public function creer(
        int $idClient,
        int $idQuartier,
        string $numero,
        string $dateDebut,
        ?string $dateFin = null
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contrat
                (id_client, id_quartier, numero_contrat, date_debut, date_fin, statut)
             VALUES
                (:client, :quartier, :numero, :debut, :fin, :statut)'
        );
        $stmt->execute([
            'client' => $idClient,
            'quartier' => $idQuartier,
            'numero' => $numero,
            'debut' => $dateDebut,
            'fin' => $dateFin,
            'statut' => 'ACTIF',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function resilier(int $idContrat, int $idFournisseur): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE contrat ct
             JOIN client c ON c.id_client = ct.id_client
             SET ct.statut = 'RESILIE', ct.date_fin = COALESCE(ct.date_fin, CURDATE())
             WHERE ct.id_contrat = :id
               AND c.id_fournisseur = :fournisseur
               AND ct.statut = 'ACTIF'"
        );
        $stmt->execute(['id' => $idContrat, 'fournisseur' => $idFournisseur]);

        return $stmt->rowCount() > 0;
    }
}

function contrat_bd(): ContratBD
{
    return new ContratBD(pdo());
}

/** @return array<int, array<string, mixed>> */
function contrat_lister_par_client(int $idClient): array
{
    return contrat_bd()->listerParClient($idClient);
}

/** @return array<int, array<string, mixed>> */
function contrat_lister_par_fournisseur(int $idFournisseur): array
{
    return contrat_bd()->listerParFournisseur($idFournisseur);
}
