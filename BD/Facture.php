<?php
declare(strict_types=1);

final class FactureBD
{
    public function __construct(private PDO $pdo) {}

    public function findByConso(int $idConso): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM facture WHERE id_conso = :id LIMIT 1');
        $stmt->execute(['id' => $idConso]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $facture): int
    {
        $sql = 'INSERT INTO facture
                    (numero, id_client, id_conso, date_facture,
                     periode_mois, periode_annee, conso_eau, conso_elec,
                     montant_ht, taux_tva, montant_tva, montant_ttc, etat)
                VALUES
                    (:numero, :id_client, :id_conso, :date_facture,
                     :periode_mois, :periode_annee, :conso_eau, :conso_elec,
                     :montant_ht, :taux_tva, :montant_tva, :montant_ttc, :etat)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($facture);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByClient(int $idClient): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM facture
             WHERE id_client = :id
             ORDER BY periode_annee DESC, periode_mois DESC, id_facture DESC'
        );
        $stmt->execute(['id' => $idClient]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByFournisseur(int $idFournisseur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             WHERE c.id_fournisseur = :id
             ORDER BY f.periode_annee DESC, f.periode_mois DESC, f.id_facture DESC'
        );
        $stmt->execute(['id' => $idFournisseur]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForClient(int $idFacture, int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom, c.adresse, c.email
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             WHERE f.id_facture = :id AND f.id_client = :client
             LIMIT 1'
        );
        $stmt->execute(['id' => $idFacture, 'client' => $idClient]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findForFournisseur(int $idFacture, int $idFournisseur): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom, c.adresse, c.email
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             WHERE f.id_facture = :id AND c.id_fournisseur = :fournisseur
             LIMIT 1'
        );
        $stmt->execute(['id' => $idFacture, 'fournisseur' => $idFournisseur]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
