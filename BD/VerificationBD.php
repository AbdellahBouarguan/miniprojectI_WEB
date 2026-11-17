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
            "INSERT INTO conso_annuelle
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
             FROM conso_annuelle a
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
             FROM conso_annuelle a
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
                       a.id_conso_annuelle, a.id_agent,
                       a.conso_eau AS agent_eau, a.conso_elec AS agent_elec,
                       a.date_saisie AS agent_date,
                       a.statut_verification, a.date_verification,
                       (SELECT COUNT(*)
                        FROM consommation x
                        WHERE x.id_client = c.id_client
                          AND x.periode_annee = ?
                          AND x.statut IN ('VALIDEE', 'FACTUREE')) AS nb_mois
                FROM client c
                LEFT JOIN conso_annuelle a
                       ON a.id_client = c.id_client AND a.annee = ?
                WHERE c.id_fournisseur = ?
                ORDER BY c.nom, c.prenom";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $annee, $annee, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $lignes = mysqli_fetch_all($resultat, MYSQLI_ASSOC);

        foreach ($lignes as &$ligne) {
            $saisie = $this->saisieAnnuelleClient((int) $ligne['id_client'], $annee);
            $ligne['saisi_eau'] = $saisie['eau'];
            $ligne['saisi_elec'] = $saisie['elec'];
        }
        unset($ligne);

        return $lignes;
    }

    /**
     * Somme les consommations de l'annee pour tous les contrats du client.
     * Pour chaque contrat : dernier index de l'annee - dernier index avant
     * l'annee. Cela fonctionne aussi avec plusieurs contrats.
     *
     * @return array{eau: ?float, elec: ?float}
     */
    private function saisieAnnuelleClient(int $idClient, int $annee): array
    {
        $stmt = mysqli_prepare(
            $this->db,
            "SELECT id_contrat, periode_annee, periode_mois, index_eau, index_elec
             FROM consommation
             WHERE id_client = ?
               AND statut IN ('VALIDEE', 'FACTUREE')
             ORDER BY id_contrat, periode_annee, periode_mois"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idClient);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);

        $etats = [];
        $present = false;

        foreach (mysqli_fetch_all($resultat, MYSQLI_ASSOC) as $ligne) {
            $idContrat = (int) $ligne['id_contrat'];
            $anneeLigne = (int) $ligne['periode_annee'];

            if (!isset($etats[$idContrat])) {
                $etats[$idContrat] = [
                    'base_eau' => null,
                    'base_elec' => null,
                    'fin_eau' => null,
                    'fin_elec' => null,
                ];
            }

            if ($anneeLigne < $annee) {
                $etats[$idContrat]['base_eau'] = (float) $ligne['index_eau'];
                $etats[$idContrat]['base_elec'] = (float) $ligne['index_elec'];
            } elseif ($anneeLigne === $annee) {
                $etats[$idContrat]['fin_eau'] = (float) $ligne['index_eau'];
                $etats[$idContrat]['fin_elec'] = (float) $ligne['index_elec'];
            }
        }

        $eau = 0.0;
        $elec = 0.0;

        foreach ($etats as $etat) {
            if ($etat['fin_eau'] === null || $etat['fin_elec'] === null) {
                continue;
            }

            $present = true;
            $eau += max(0.0, $etat['fin_eau'] - (float) ($etat['base_eau'] ?? 0.0));
            $elec += max(0.0, $etat['fin_elec'] - (float) ($etat['base_elec'] ?? 0.0));
        }

        return [
            'eau' => $present ? $eau : null,
            'elec' => $present ? $elec : null,
        ];
    }

    public function decider(int $idClient, int $annee, int $idFournisseur, string $decision): bool
    {
        $stmt = mysqli_prepare(
            $this->db,
            "UPDATE conso_annuelle a
             JOIN client c ON c.id_client = a.id_client
             SET a.statut_verification = ?, a.date_verification = NOW()
             WHERE a.id_client = ? AND a.annee = ? AND c.id_fournisseur = ?"
        );
        mysqli_stmt_bind_param($stmt, 'siii', $decision, $idClient, $annee, $idFournisseur);

        return mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    }
}