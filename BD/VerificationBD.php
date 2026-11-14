<?php
declare(strict_types=1);

final class VerificationBD
{
    public function __construct(private mysqli $db) {}

    public function clientAppartient(int $idClient, int $idFournisseur): bool
    {
        $stmt = mysqli_prepare(
            $this->db,
            'SELECT 1 FROM client WHERE id_client = ? AND id_fournisseur = ? LIMIT 1'
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idClient, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);

        return (bool) mysqli_fetch_row($resultat);
    }

    /**
     * Enregistre (ou met à jour) le relevé annuel de l'agent.
     * Si les valeurs changent, la décision du fournisseur est remise à "A_VERIFIER".
     */
    public function enregistrerReleveAgent(
        int $idClient,
        ?int $idAgent,
        int $annee,
        float $eau,
        float $elec,
        string $dateSaisie
    ): bool {
        $stmt = mysqli_prepare(
            $this->db,
            "INSERT INTO consommation_annuelle
                 (id_client, id_agent, annee, conso_eau, conso_elec, date_saisie)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 statut_verification = IF(
                     conso_eau <> VALUES(conso_eau) OR conso_elec <> VALUES(conso_elec),
                     'A_VERIFIER', statut_verification),
                 date_verification = IF(
                     conso_eau <> VALUES(conso_eau) OR conso_elec <> VALUES(conso_elec),
                     NULL, date_verification),
                 id_agent = VALUES(id_agent),
                 conso_eau = VALUES(conso_eau),
                 conso_elec = VALUES(conso_elec),
                 date_saisie = VALUES(date_saisie)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iiidds',
            $idClient,
            $idAgent,
            $annee,
            $eau,
            $elec,
            $dateSaisie
        );

        return mysqli_stmt_execute($stmt);
    }

    /**
     * Années proposées dans le sélecteur (décroissant), année courante incluse.
     *
     * @return int[]
     */
    public function anneesDisponibles(int $idFournisseur): array
    {
        $stmt = mysqli_prepare(
            $this->db,
            "SELECT co.periode_annee AS annee
             FROM consommation co
             JOIN client c ON c.id_client = co.id_client
             WHERE c.id_fournisseur = ?
             UNION
             SELECT a.annee
             FROM consommation_annuelle a
             JOIN client c ON c.id_client = a.id_client
             WHERE c.id_fournisseur = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idFournisseur, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $annees = [(int) date('Y')];
        $resultat = mysqli_stmt_get_result($stmt);

        foreach (mysqli_fetch_all($resultat, MYSQLI_ASSOC) as $ligne) {
            $annees[] = (int) $ligne['annee'];
        }

        $annees = array_values(array_unique($annees));
        rsort($annees);

        return $annees;
    }

    public function derniereAnneeReleveAgent(int $idFournisseur): ?int
    {
        $stmt = mysqli_prepare(
            $this->db,
            "SELECT MAX(a.annee)
             FROM consommation_annuelle a
             JOIN client c ON c.id_client = a.id_client
             WHERE c.id_fournisseur = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $ligne = mysqli_fetch_row($resultat);

        return ($ligne && $ligne[0] !== null) ? (int) $ligne[0] : null;
    }

    /**
     * Une ligne par client du fournisseur, avec :
     *  - le nombre de mois validés saisis dans l'année ;
     *  - le dernier index validé de l'année (fin_*) ;
     *  - le dernier index validé avant l'année (base_*) ;
     *  - le relevé annuel de l'agent (agent_*), s'il existe.
     */
    public function listerVerification(int $idFournisseur, int $annee): array
    {
        $sql = "SELECT c.id_client, c.nom, c.prenom, c.cin,
                       a.id_annuelle, a.id_agent,
                       a.conso_eau AS agent_eau, a.conso_elec AS agent_elec,
                       a.date_saisie AS agent_date,
                       a.statut_verification, a.date_verification,
                       (SELECT COUNT(*)
                        FROM consommation x
                        WHERE x.id_client = c.id_client
                          AND x.periode_annee = ?
                          AND x.statut IN ('VALIDEE', 'FACTUREE')) AS nb_mois,
                       fin.index_eau AS fin_eau, fin.index_elec AS fin_elec,
                       base.index_eau AS base_eau, base.index_elec AS base_elec
                FROM client c
                LEFT JOIN consommation_annuelle a
                       ON a.id_client = c.id_client AND a.annee = ?
                LEFT JOIN consommation fin
                       ON fin.id_conso = (
                            SELECT x.id_conso
                            FROM consommation x
                            WHERE x.id_client = c.id_client
                              AND x.periode_annee = ?
                              AND x.statut IN ('VALIDEE', 'FACTUREE')
                            ORDER BY x.periode_mois DESC
                            LIMIT 1)
                LEFT JOIN consommation base
                       ON base.id_conso = (
                            SELECT x.id_conso
                            FROM consommation x
                            WHERE x.id_client = c.id_client
                              AND x.periode_annee < ?
                              AND x.statut IN ('VALIDEE', 'FACTUREE')
                            ORDER BY x.periode_annee DESC, x.periode_mois DESC
                            LIMIT 1)
                WHERE c.id_fournisseur = ?
                ORDER BY c.nom, c.prenom";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'iiiii', $annee, $annee, $annee, $annee, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_all($resultat, MYSQLI_ASSOC);
    }

    public function decider(int $idClient, int $annee, int $idFournisseur, string $decision): bool
    {
        $stmt = mysqli_prepare(
            $this->db,
            "UPDATE consommation_annuelle a
             JOIN client c ON c.id_client = a.id_client
             SET a.statut_verification = ?, a.date_verification = NOW()
             WHERE a.id_client = ? AND a.annee = ? AND c.id_fournisseur = ?"
        );
        mysqli_stmt_bind_param($stmt, 'siii', $decision, $idClient, $annee, $idFournisseur);

        return mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    }
}