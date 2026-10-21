<?php
declare(strict_types=1);

final class Tarification
{
    public static function calculer(
        float $consoEau,
        float $consoElec,
        array $tarifs,
        float $tauxTva = 18.0
    ): array {
        $montantEau = self::montant($consoEau, $tarifs['EAU'] ?? []);
        $montantElec = self::montant($consoElec, $tarifs['ELEC'] ?? []);

        $ht = round($montantEau + $montantElec, 2);
        $tva = round($ht * $tauxTva / 100, 2);
        $ttc = round($ht + $tva, 2);

        return [
            'montant_eau' => $montantEau,
            'montant_elec' => $montantElec,
            'montant_ht' => $ht,
            'taux_tva' => $tauxTva,
            'montant_tva' => $tva,
            'montant_ttc' => $ttc,
        ];
    }

    private static function montant(float $quantite, array $tranches): float
    {
        $total = 0.0;

        foreach ($tranches as $tranche) {
            $min = (float) $tranche['borne_min'];
            $max = $tranche['borne_max'] !== null
                ? (float) $tranche['borne_max']
                : $quantite;

            if ($quantite <= $min) {
                continue;
            }

            $quantiteTranche = min($quantite, $max) - $min;

            if ($quantiteTranche > 0) {
                $total += $quantiteTranche * (float) $tranche['prix'];
            }
        }

        return round($total, 2);
    }
}
