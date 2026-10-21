<?php
declare(strict_types=1);

final class AnomalieBD
{
    public function __construct(private mysqli $db) {}

    public function listerEnAttente(int $idFournisseur): array
    {
        $sql = "SELECT a.id_anomalie, a.id_conso, a.type_anomalie, a.champ,
                       a.valeur_saisie, a.valeur_reference, a.date_detection,
                       co.id_client, co.periode_mois, co.periode_annee,
                       co.index_eau, co.index_elec, co.statut AS conso_statut,
                       c.nom, c.prenom, c.cin,
                       f.seuil_eau, f.seuil_elec
                FROM anomalie a
                JOIN consommation co ON co.id_conso = a.id_conso
                JOIN client c ON c.id_client = co.id_client
                JOIN fournisseur f ON f.id_fournisseur = a.id_fournisseur
                WHERE a.id_fournisseur = ? AND a.statut = 'EN_ATTENTE'
                ORDER BY a.date_detection DESC, a.id_anomalie DESC";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $lignes = mysqli_fetch_all($resultat, MYSQLI_ASSOC);

        foreach ($lignes as &$ligne) {
            $this->enrichir($ligne);
        }
        unset($ligne);

        return $lignes;
    }

    public function trouver(int $idAnomalie, int $idFournisseur): ?array
    {
        $sql = "SELECT a.id_anomalie, a.id_conso, a.type_anomalie, a.champ,
                       a.valeur_saisie, a.valeur_reference, a.date_detection,
                       a.statut,
                       co.id_client, co.periode_mois, co.periode_annee,
                       co.index_eau, co.index_elec, co.statut AS conso_statut,
                       c.nom, c.prenom, c.cin,
                       f.seuil_eau, f.seuil_elec
                FROM anomalie a
                JOIN consommation co ON co.id_conso = a.id_conso
                JOIN client c ON c.id_client = co.id_client
                JOIN fournisseur f ON f.id_fournisseur = a.id_fournisseur
                WHERE a.id_anomalie = ? AND a.id_fournisseur = ?
                LIMIT 1";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $idAnomalie, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $ligne = mysqli_fetch_assoc($resultat);

        if (!$ligne) {
            return null;
        }

        $this->enrichir($ligne);

        return $ligne;
    }

    public function corriger(int $idAnomalie, int $idFournisseur, float $nouvelIndex): bool
    {
        $anomalie = $this->trouverBrut($idAnomalie, $idFournisseur);

        if ($anomalie === null || $anomalie['statut'] !== 'EN_ATTENTE') {
            return false;
        }

        mysqli_begin_transaction($this->db);

        try {
            $colonne = $anomalie['champ'] === 'EAU' ? 'index_eau' : 'index_elec';

            $stmt = mysqli_prepare(
                $this->db,
                'UPDATE consommation SET ' . $colonne . ' = ? WHERE id_conso = ?'
            );
            mysqli_stmt_bind_param(
                $stmt,
                'di',
                $nouvelIndex,
                $anomalie['id_conso']
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException('Impossible de corriger la consommation.');
            }

            $stmt = mysqli_prepare(
                $this->db,
                "UPDATE anomalie
                 SET statut = 'CORRIGEE',
                     valeur_corrigee = ?,
                     date_resolution = NOW()
                 WHERE id_anomalie = ? AND id_fournisseur = ? AND statut = 'EN_ATTENTE'"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'dii',
                $nouvelIndex,
                $idAnomalie,
                $idFournisseur
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException('Impossible de clôturer l’anomalie.');
            }

            $stmt = mysqli_prepare(
                $this->db,
                "SELECT COUNT(*) FROM anomalie
                 WHERE id_conso = ? AND statut = 'EN_ATTENTE'"
            );
            mysqli_stmt_bind_param($stmt, 'i', $anomalie['id_conso']);
            mysqli_stmt_execute($stmt);

            $resultat = mysqli_stmt_get_result($stmt);
            $restantes = mysqli_fetch_row($resultat);

            if ((int) ($restantes[0] ?? 0) === 0) {
                $stmt = mysqli_prepare(
                    $this->db,
                    "UPDATE consommation SET statut = 'VALIDEE' WHERE id_conso = ?"
                );
                mysqli_stmt_bind_param($stmt, 'i', $anomalie['id_conso']);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($this->db);

            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->db);
            return false;
        }
    }

    private function trouverBrut(int $idAnomalie, int $idFournisseur): ?array
    {
        $stmt = mysqli_prepare(
            $this->db,
            'SELECT id_anomalie, id_conso, champ, statut
             FROM anomalie
             WHERE id_anomalie = ? AND id_fournisseur = ?
             LIMIT 1'
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idAnomalie, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $anomalie = mysqli_fetch_assoc($resultat);

        return $anomalie ?: null;
    }

    private function enrichir(array &$ligne): void
    {
        $champ = $ligne['champ'];
        $indexPrecedent = $this->indexPrecedent(
            (int) $ligne['id_client'],
            (int) $ligne['periode_mois'],
            (int) $ligne['periode_annee'],
            $champ
        );

        $indexSaisi = $champ === 'EAU'
            ? (float) $ligne['index_eau']
            : (float) $ligne['index_elec'];

        $seuilBase = $champ === 'EAU'
            ? (float) $ligne['seuil_eau']
            : (float) $ligne['seuil_elec'];

        $ligne['index_precedent'] = $indexPrecedent;
        $ligne['index_saisi'] = $indexSaisi;
        $ligne['seuil'] = seuil_effectif($seuilBase, $indexPrecedent);
    }

    private function indexPrecedent(
        int $idClient,
        int $mois,
        int $annee,
        string $champ
    ): ?float {
        $stmt = mysqli_prepare(
            $this->db,
            "SELECT index_eau, index_elec
             FROM consommation
             WHERE id_client = ?
               AND statut IN ('VALIDEE', 'FACTUREE')
               AND (periode_annee < ? OR (periode_annee = ? AND periode_mois < ?))
             ORDER BY periode_annee DESC, periode_mois DESC
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'iiii', $idClient, $annee, $annee, $mois);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $ligne = mysqli_fetch_assoc($resultat);

        if (!$ligne) {
            return null;
        }

        return $champ === 'EAU'
            ? (float) $ligne['index_eau']
            : (float) $ligne['index_elec'];
    }
}
