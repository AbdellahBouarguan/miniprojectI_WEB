<?php
declare(strict_types=1);

require_once __DIR__ . '/Tarification.php';
require_once __DIR__ . '/../BD/Facture.php';
require_once __DIR__ . '/../BD/ConsommationBD.php';

final class FactureService
{
    private ConsommationBD $consommations;
    private FactureBD $factures;

    public function __construct(private PDO $pdo)
    {
        $this->consommations = new ConsommationBD($pdo);
        $this->factures = new FactureBD($pdo);
    }

    public function generer(int $idConso): array
    {
        $existante = $this->factures->findByConso($idConso);

        if ($existante !== null) {
            return $existante;
        }

        $conso = $this->consommations->trouverParId($idConso);

        if ($conso === null) {
            throw new RuntimeException('Consommation introuvable.');
        }

        if ($conso['statut'] !== 'VALIDEE') {
            throw new RuntimeException('La consommation doit être validée avant facturation.');
        }

        $infos = $this->consommations->clientEtSeuils((int) $conso['id_client']);

        if ($infos === null) {
            throw new RuntimeException('Client ou fournisseur introuvable.');
        }

        $calcul = $this->consommations->consommationsCalculees($conso);
        $tarifs = $this->consommations->tarifs((int) $infos['id_fournisseur']);

        if (($tarifs['EAU'] ?? []) === [] || ($tarifs['ELEC'] ?? []) === []) {
            throw new RuntimeException('Tarifs du fournisseur introuvables.');
        }

        $montants = Tarification::calculer(
            (float) $calcul['eau'],
            (float) $calcul['elec'],
            $tarifs,
            defined('TVA_RATE') ? (float) TVA_RATE : 18.0
        );

        $numero = sprintf(
            'FAC-%d%02d-%d',
            (int) $conso['periode_annee'],
            (int) $conso['periode_mois'],
            $idConso
        );

        $this->pdo->beginTransaction();

        try {
            $this->factures->create([
                'numero' => $numero,
                'id_client' => (int) $conso['id_client'],
                'id_conso' => $idConso,
                'date_facture' => date('Y-m-d'),
                'periode_mois' => (int) $conso['periode_mois'],
                'periode_annee' => (int) $conso['periode_annee'],
                'conso_eau' => (float) $calcul['eau'],
                'conso_elec' => (float) $calcul['elec'],
                'montant_ht' => $montants['montant_ht'],
                'taux_tva' => $montants['taux_tva'],
                'montant_tva' => $montants['montant_tva'],
                'montant_ttc' => $montants['montant_ttc'],
                'etat' => 'IMPAYEE',
            ]);

            $this->consommations->changerStatut($idConso, 'FACTUREE');
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

        return $this->factures->findByConso($idConso) ?? [];
    }
}
