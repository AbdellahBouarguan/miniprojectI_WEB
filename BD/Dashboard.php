<?php
declare(strict_types=1);

/**
 * Indicateurs du tableau de bord fournisseur :
 * - montant total des factures impayees / encaissees ;
 * - evolution mensuelle des consommations d'eau et d'electricite.
 *
 * Les montants proviennent de la table facture : une facture est
 * "encaissee" lorsque son etat vaut PAYEE (mis a jour par le module
 * Paiement), sinon elle reste IMPAYEE.
 */
final class DashboardBD
{
    private const MOIS = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    private const MOIS_COURT = [
        1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Aoû',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
    ];

    public function __construct(private PDO $pdo) {}

    public static function nomMois(int $mois): string
    {
        return self::MOIS[$mois] ?? '';
    }

    public static function nomMoisCourt(int $mois): string
    {
        return self::MOIS_COURT[$mois] ?? '';
    }

    /** Annees presentes dans les factures du fournisseur (recentes d'abord) */
    public function anneesDisponibles(int $idFournisseur): array    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT f.periode_annee AS annee
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             WHERE c.id_fournisseur = :fournisseur
             ORDER BY f.periode_annee DESC'
        );
        $stmt->execute(['fournisseur' => $idFournisseur]);

        $annees = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $annees[] = (int) $ligne['annee'];
        }

        return $annees;
    }

    public function nbClients(int $idFournisseur): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM client WHERE id_fournisseur = :fournisseur'
        );
        $stmt->execute(['fournisseur' => $idFournisseur]);

        return (int) $stmt->fetchColumn();
    }

    public function nbAnomaliesEnAttente(int $idFournisseur): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM anomalie
             WHERE id_fournisseur = :fournisseur AND statut = 'EN_ATTENTE'"
        );
        $stmt->execute(['fournisseur' => $idFournisseur]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Totaux des factures du fournisseur, eventuellement filtres sur
     * une annee de periode.
     *
     * @return array{impaye: float, encaisse: float, nb_impayees: int, nb_payees: int, nb_factures: int}
     */
    public function totaux(int $idFournisseur, ?int $annee = null): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN f.etat = 'IMPAYEE' THEN f.montant_ttc ELSE 0 END), 0) AS impaye,
                    COALESCE(SUM(CASE WHEN f.etat = 'PAYEE' THEN f.montant_ttc ELSE 0 END), 0) AS encaisse,
                    COALESCE(SUM(f.etat = 'IMPAYEE'), 0) AS nb_impayees,
                    COALESCE(SUM(f.etat = 'PAYEE'), 0) AS nb_payees,
                    COUNT(*) AS nb_factures
                FROM facture f
                JOIN client c ON c.id_client = f.id_client
                WHERE c.id_fournisseur = :fournisseur";

        $params = ['fournisseur' => $idFournisseur];

        if ($annee !== null) {
            $sql .= ' AND f.periode_annee = :annee';
            $params['annee'] = $annee;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'impaye' => (float) ($ligne['impaye'] ?? 0),
            'encaisse' => (float) ($ligne['encaisse'] ?? 0),
            'nb_impayees' => (int) ($ligne['nb_impayees'] ?? 0),
            'nb_payees' => (int) ($ligne['nb_payees'] ?? 0),
            'nb_factures' => (int) ($ligne['nb_factures'] ?? 0),
        ];
    }

    /**
     * Evolution mensuelle des consommations facturees : 12 entrees,
     * les mois sans facture etant a zero.
     *
     * @return array<int, array{mois: int, nom: string, court: string, eau: float, elec: float, montant: float, nb: int}>
     */
    public function evolutionMensuelle(int $idFournisseur, int $annee): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.periode_mois AS mois,
                    COALESCE(SUM(f.conso_eau), 0) AS eau,
                    COALESCE(SUM(f.conso_elec), 0) AS elec,
                    COALESCE(SUM(f.montant_ttc), 0) AS montant,
                    COUNT(*) AS nb
             FROM facture f
             JOIN client c ON c.id_client = f.id_client
             WHERE c.id_fournisseur = :fournisseur
               AND f.periode_annee = :annee
             GROUP BY f.periode_mois
             ORDER BY f.periode_mois'
        );
        $stmt->execute(['fournisseur' => $idFournisseur, 'annee' => $annee]);

        $parMois = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $parMois[(int) $ligne['mois']] = $ligne;
        }

        $evolution = [];

        for ($mois = 1; $mois <= 12; $mois++) {
            $ligne = $parMois[$mois] ?? null;

            $evolution[$mois] = [
                'mois' => $mois,
                'nom' => self::nomMois($mois),
                'court' => self::nomMoisCourt($mois),
                'eau' => (float) ($ligne['eau'] ?? 0),
                'elec' => (float) ($ligne['elec'] ?? 0),
                'montant' => (float) ($ligne['montant'] ?? 0),
                'nb' => (int) ($ligne['nb'] ?? 0),
            ];
        }

        return $evolution;
    }
}
