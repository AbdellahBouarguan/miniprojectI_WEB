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
            'SELECT f.*, ct.numero_contrat, q.nom AS quartier_nom, q.type_activite
             FROM facture f
             JOIN consommation co ON co.id_conso = f.id_conso
             JOIN contrat ct ON ct.id_contrat = co.id_contrat
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE f.id_client = :id
             ORDER BY f.periode_annee DESC, f.periode_mois DESC, f.id_facture DESC'
        );
        $stmt->execute(['id' => $idClient]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByFournisseur(int $idFournisseur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom,
                    ct.numero_contrat, q.nom AS quartier_nom, q.type_activite
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             JOIN consommation co ON co.id_conso = f.id_conso
             JOIN contrat ct ON ct.id_contrat = co.id_contrat
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE c.id_fournisseur = :id
             ORDER BY f.periode_annee DESC, f.periode_mois DESC, f.id_facture DESC'
        );
        $stmt->execute(['id' => $idFournisseur]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForClient(int $idFacture, int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom, c.adresse, c.email,
                    ct.numero_contrat, q.nom AS quartier_nom, q.type_activite
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             JOIN consommation co ON co.id_conso = f.id_conso
             JOIN contrat ct ON ct.id_contrat = co.id_contrat
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE f.id_facture = :id AND f.id_client = :client
             LIMIT 1'
        );
        $stmt->execute(['id' => $idFacture, 'client' => $idClient]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findForFournisseur(int $idFacture, int $idFournisseur): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.cin, c.nom, c.prenom, c.adresse, c.email,
                    ct.numero_contrat, q.nom AS quartier_nom, q.type_activite
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             JOIN consommation co ON co.id_conso = f.id_conso
             JOIN contrat ct ON ct.id_contrat = co.id_contrat
             JOIN quartier q ON q.id_quartier = ct.id_quartier
             WHERE f.id_facture = :id AND c.id_fournisseur = :fournisseur
             LIMIT 1'
        );
        $stmt->execute(['id' => $idFacture, 'fournisseur' => $idFournisseur]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
