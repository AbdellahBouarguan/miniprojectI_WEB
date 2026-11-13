<?php
declare(strict_types=1);

/**
 * Consommations annuelles relevees par l'agent.
 * Une seule ligne par client et par annee (voir la contrainte
 * UNIQUE(id_client, annee) dans sql/schema.sql).
 */
final class ConsoAnnuelleBD
{
    public function __construct(private PDO $pdo) {}

    public function existe(int $idClient, int $annee): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM conso_annuelle
             WHERE id_client = :client AND annee = :annee
             LIMIT 1'
        );
        $stmt->execute(['client' => $idClient, 'annee' => $annee]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Identifiants clients existants, indexes par id.
     *
     * @return array<int, true>
     */
    public function idsClientsExistants(): array
    {
        $ids = [];

        foreach ($this->pdo->query('SELECT id_client FROM client') as $ligne) {
            $ids[(int) $ligne['id_client']] = true;
        }

        return $ids;
    }

    public function inserer(
        int $idClient,
        int $idAgent,
        float $consoEau,
        float $consoElec,
        int $annee,
        string $dateSaisie
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO conso_annuelle
                (id_client, id_agent, conso_eau, conso_elec, annee, date_saisie)
             VALUES
                (:client, :agent, :eau, :elec, :annee, :date)'
        );

        $stmt->execute([
            'client' => $idClient,
            'agent' => $idAgent,
            'eau' => $consoEau,
            'elec' => $consoElec,
            'annee' => $annee,
            'date' => $dateSaisie,
        ]);
    }

    /** Releves deja importes par cet agent, du plus recent au plus ancien */
    public function listerParAgent(int $idAgent): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ca.*, c.cin, c.nom, c.prenom
             FROM conso_annuelle ca
             JOIN client c ON c.id_client = ca.id_client
             WHERE ca.id_agent = :agent
             ORDER BY ca.annee DESC, ca.date_saisie DESC, c.nom ASC'
        );
        $stmt->execute(['agent' => $idAgent]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Nombre de releves annuels enregistres (toutes annees) */
    public function compter(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM conso_annuelle')->fetchColumn();
    }

    /** Annees deja importees, pour l'affichage */
    public function annees(): array
    {
        $annees = [];

        foreach ($this->pdo->query('SELECT DISTINCT annee FROM conso_annuelle ORDER BY annee DESC') as $ligne) {
            $annees[] = (int) $ligne['annee'];
        }

        return $annees;
    }
}
