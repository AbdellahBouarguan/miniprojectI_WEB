<?php
declare(strict_types=1);

final class ConsommationBD
{
    public function __construct(private PDO $pdo) {}

    /** Client + seuils du fournisseur */
    public function clientEtSeuils(int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id_client, c.id_fournisseur, f.seuil_eau, f.seuil_elec
             FROM client c
             JOIN fournisseur f ON f.id_fournisseur = c.id_fournisseur
             WHERE c.id_client = :id'
        );
        $stmt->execute(['id' => $idClient]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Dernier relevé VALIDEE/FACTUREE strictement avant la période donnée */
    public function derniereConsommationValidee(int $idClient, int $mois, int $annee): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM consommation
             WHERE id_client = :id
               AND statut IN ('VALIDEE', 'FACTUREE')
               AND (periode_annee < :annee_lt
                    OR (periode_annee = :annee_eq AND periode_mois < :mois))
             ORDER BY periode_annee DESC, periode_mois DESC
             LIMIT 1"
        );
        $stmt->execute([
            'id' => $idClient,
            'annee_lt' => $annee,
            'annee_eq' => $annee,
            'mois' => $mois,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function existePourPeriode(int $idClient, int $mois, int $annee, ?int $saufId = null): bool
    {
        $sql = 'SELECT 1 FROM consommation
                WHERE id_client = :id AND periode_mois = :mois AND periode_annee = :annee';
        $params = ['id' => $idClient, 'mois' => $mois, 'annee' => $annee];

        if ($saufId !== null) {
            $sql .= ' AND id_conso <> :sauf';
            $params['sauf'] = $saufId;
        }

        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** Empêche une nouvelle saisie tant qu'une anomalie n'est pas résolue */
    public function aAnomalieEnAttente(int $idClient): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM anomalie a
             JOIN consommation c ON c.id_conso = a.id_conso
             WHERE c.id_client = :id AND a.statut = 'EN_ATTENTE'
             LIMIT 1"
        );
        $stmt->execute(['id' => $idClient]);
        return (bool) $stmt->fetchColumn();
    }

    public function trouver(int $idConso, int $idClient): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM consommation WHERE id_conso = :id AND id_client = :client'
        );
        $stmt->execute(['id' => $idConso, 'client' => $idClient]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function trouverParId(int $idConso): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM consommation WHERE id_conso = :id'
        );
        $stmt->execute(['id' => $idConso]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function anomaliesDe(int $idConso): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM anomalie WHERE id_conso = :id ORDER BY id_anomalie'
        );
        $stmt->execute(['id' => $idConso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retourne ['EAU' => chemin, 'ELEC' => chemin] */
    public function photosDe(int $idConso): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT type, chemin FROM photo_capteur WHERE id_conso = :id'
        );
        $stmt->execute(['id' => $idConso]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function factureDe(int $idConso): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM facture WHERE id_conso = :id');
        $stmt->execute(['id' => $idConso]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listerParClient(int $idClient): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.*,
                    f.id_facture, f.numero AS facture_numero, f.etat AS facture_etat
             FROM consommation c
             LEFT JOIN facture f ON f.id_conso = c.id_conso
             WHERE c.id_client = :id
             ORDER BY c.periode_annee DESC, c.periode_mois DESC"
        );
        $stmt->execute(['id' => $idClient]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tranches tarifaires du fournisseur, groupées par type */
    public function tarifs(int $idFournisseur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT type, borne_min, borne_max, prix_unitaire
             FROM tarif
             WHERE id_fournisseur = :id
             ORDER BY type, borne_min'
        );
        $stmt->execute(['id' => $idFournisseur]);

        $resultat = ['EAU' => [], 'ELEC' => []];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $type = $ligne['type'];

            if (!isset($resultat[$type])) {
                $resultat[$type] = [];
            }

            $resultat[$type][] = [
                'borne_min' => (float) $ligne['borne_min'],
                'borne_max' => $ligne['borne_max'] !== null
                    ? (float) $ligne['borne_max']
                    : null,
                'prix' => (float) $ligne['prix_unitaire'],
            ];
        }

        return $resultat;
    }

    /** Consommations calculées à partir du relevé précédent */
    public function consommationsCalculees(array $conso): array
    {
        $prec = $this->derniereConsommationValidee(
            (int) $conso['id_client'],
            (int) $conso['periode_mois'],
            (int) $conso['periode_annee']
        );

        $baseEau  = $prec ? (float) $prec['index_eau']  : 0.0;
        $baseElec = $prec ? (float) $prec['index_elec'] : 0.0;

        return [
            'eau'  => max(0.0, (float) $conso['index_eau']  - $baseEau),
            'elec' => max(0.0, (float) $conso['index_elec'] - $baseElec),
            'precedent' => $prec,
        ];
    }

    public function creer(
        int $idClient,
        int $idFournisseur,
        int $mois,
        int $annee,
        float $indexEau,
        float $indexElec,
        string $statut,
        array $chemins,
        array $anomalies
    ): int {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO consommation
                    (id_client, periode_mois, periode_annee, index_eau, index_elec, statut)
                 VALUES
                    (:client, :mois, :annee, :eau, :elec, :statut)'
            );
            $stmt->execute([
                'client' => $idClient,
                'mois' => $mois,
                'annee' => $annee,
                'eau' => $indexEau,
                'elec' => $indexElec,
                'statut' => $statut,
            ]);

            $idConso = (int) $this->pdo->lastInsertId();

            $this->ecrirePhotos($idConso, $chemins);
            $this->ecrireAnomalies($idConso, $idFournisseur, $anomalies);

            $this->pdo->commit();
            return $idConso;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Utilisé après « Corriger » */
    public function mettreAJour(
        int $idConso,
        int $idFournisseur,
        float $indexEau,
        float $indexElec,
        string $statut,
        array $chemins,
        array $anomalies,
        array $precedent
    ): void {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE consommation
                 SET index_eau = :eau,
                     index_elec = :elec,
                     statut = :statut,
                     date_saisie = NOW()
                 WHERE id_conso = :id'
            );
            $stmt->execute([
                'eau' => $indexEau,
                'elec' => $indexElec,
                'statut' => $statut,
                'id' => $idConso,
            ]);

            // Les anciennes anomalies passent en CORRIGEE avec la nouvelle valeur
            $this->cloturerAnomalies($idConso, $indexEau, $indexElec, $precedent);

            $this->ecrirePhotos($idConso, $chemins);
            $this->ecrireAnomalies($idConso, $idFournisseur, $anomalies);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function confirmerAnomalies(int $idConso): void
    {
        $this->pdo->prepare(
            "UPDATE anomalie
             SET statut = 'CONFIRMEE', date_resolution = NOW()
             WHERE id_conso = :id AND statut = 'EN_ATTENTE'"
        )->execute(['id' => $idConso]);
    }

    public function aIndexInferieur(int $idConso): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM anomalie
             WHERE id_conso = :id
               AND type_anomalie = 'INDEX_INFERIEUR'
               AND statut = 'EN_ATTENTE'
             LIMIT 1"
        );
        $stmt->execute(['id' => $idConso]);
        return (bool) $stmt->fetchColumn();
    }

    /** « Corriger » : la conso redevient un brouillon */
    public function remettreEnBrouillon(int $idConso): void
    {
        $this->changerStatut($idConso, 'EN_ATTENTE');
    }

    public function changerStatut(int $idConso, string $statut): void
    {
        $this->pdo->prepare(
            'UPDATE consommation SET statut = :statut WHERE id_conso = :id'
        )->execute(['statut' => $statut, 'id' => $idConso]);
    }

    private function ecrirePhotos(int $idConso, array $chemins): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO photo_capteur (id_conso, type, chemin)
             VALUES (:id, :type, :chemin)
             ON DUPLICATE KEY UPDATE
                chemin = VALUES(chemin),
                date_televersement = NOW()'
        );

        foreach ($chemins as $type => $chemin) {
            $stmt->execute([
                'id' => $idConso,
                'type' => $type,
                'chemin' => $chemin,
            ]);
        }
    }

    private function ecrireAnomalies(int $idConso, int $idFournisseur, array $anomalies): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO anomalie
                (id_conso, id_fournisseur, type_anomalie, champ,
                 valeur_saisie, valeur_reference)
             VALUES
                (:conso, :fournisseur, :type, :champ, :saisie, :reference)'
        );

        foreach ($anomalies as $a) {
            $stmt->execute([
                'conso' => $idConso,
                'fournisseur' => $idFournisseur,
                'type' => $a['type'],
                'champ' => $a['champ'],
                'saisie' => $a['valeur_saisie'],
                'reference' => $a['valeur_reference'],
            ]);
        }
    }

    private function cloturerAnomalies(
        int $idConso,
        float $indexEau,
        float $indexElec,
        array $precedent
    ): void {
        $upd = $this->pdo->prepare(
            "UPDATE anomalie
             SET statut = 'CORRIGEE',
                 valeur_corrigee = :valeur,
                 date_resolution = NOW()
             WHERE id_anomalie = :id AND statut = 'EN_ATTENTE'"
        );

        foreach ($this->anomaliesDe($idConso) as $a) {
            if ($a['statut'] !== 'EN_ATTENTE') {
                continue;
            }

            $nouvelIndex = $a['champ'] === 'EAU' ? $indexEau : $indexElec;

            // La valeur corrigée est l'index corrigé, pour les deux types d'anomalie
            $upd->execute([
                'valeur' => $nouvelIndex,
                'id' => $a['id_anomalie'],
            ]);
        }
    }
}