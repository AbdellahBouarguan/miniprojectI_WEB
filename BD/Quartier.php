<?php
declare(strict_types=1);

/**
 * Quartiers : un quartier possede un type d'activite et peut
 * regrouper plusieurs contrats clients.
 */
final class QuartierBD
{
    public const TYPES = ['RESIDENTIEL', 'COMMERCIAL', 'INDUSTRIEL'];

    public function __construct(private PDO $pdo) {}

    /** @return array<int, array<string, mixed>> */
    public function lister(): array
    {
        return $this->pdo
            ->query('SELECT id_quartier, nom, type_activite, date_creation
                     FROM quartier
                     ORDER BY nom, type_activite')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouver(int $idQuartier): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_quartier, nom, type_activite, date_creation
             FROM quartier
             WHERE id_quartier = :id'
        );
        $stmt->execute(['id' => $idQuartier]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function existe(string $nom, string $typeActivite): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM quartier
             WHERE nom = :nom AND type_activite = :type
             LIMIT 1'
        );
        $stmt->execute(['nom' => $nom, 'type' => $typeActivite]);

        return (bool) $stmt->fetchColumn();
    }

    public function creer(string $nom, string $typeActivite): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO quartier (nom, type_activite)
             VALUES (:nom, :type)'
        );
        $stmt->execute(['nom' => $nom, 'type' => $typeActivite]);

        return (int) $this->pdo->lastInsertId();
    }
}

function quartier_bd(): QuartierBD
{
    return new QuartierBD(pdo());
}

/** @return array<int, array<string, mixed>> */
function quartier_lister(): array
{
    return quartier_bd()->lister();
}

function quartier_trouver(int $idQuartier): ?array
{
    return quartier_bd()->trouver($idQuartier);
}

function quartier_creer(string $nom, string $typeActivite): int
{
    return quartier_bd()->creer($nom, $typeActivite);
}
