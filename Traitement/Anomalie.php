<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/AnomalieBD.php';

final class Anomalie
{
    /**
     * @param array|null $precedent Dernier relevé validé (ou null pour le premier)
     * @return array<int, array{type:string, champ:string, valeur_saisie:float, valeur_reference:float}>
     */
    public static function detecter(
        ?array $precedent,
        float $indexEau,
        float $indexElec,
        float $seuilEau,
        float $seuilElec
    ): array {
        $anomalies = [];

        $champs = [
            'EAU'  => ['index' => 'index_eau',  'valeur' => $indexEau,  'seuil' => $seuilEau],
            'ELEC' => ['index' => 'index_elec', 'valeur' => $indexElec, 'seuil' => $seuilElec],
        ];

        foreach ($champs as $code => $c) {
            $indexPrecedent = $precedent !== null ? (float) $precedent[$c['index']] : 0.0;

            // 1. Index inférieur au relevé précédent
            if ($precedent !== null && $c['valeur'] < $indexPrecedent) {
                $anomalies[] = [
                    'type' => 'INDEX_INFERIEUR',
                    'champ' => $code,
                    'valeur_saisie' => $c['valeur'],
                    'valeur_reference' => $indexPrecedent,
                ];
                continue; // inutile de tester le seuil sur une valeur incohérente
            }

            // 2. Index supérieur au seuil de vigilance
            // Le seuil est exprimé en index (index précédent + 40 %,
            // avec le seuil du fournisseur comme plancher).
            if ($c['valeur'] > $c['seuil']) {
                $anomalies[] = [
                    'type' => 'SEUIL_DEPASSE',
                    'champ' => $code,
                    'valeur_saisie' => $c['valeur'],
                    'valeur_reference' => $c['seuil'],
                ];
            }
        }

        return $anomalies;
    }
}

function anomalie_bd(): AnomalieBD
{
    return new AnomalieBD(db());
}

function anomalie_exiger_fournisseur(): int
{
    if (
        ($_SESSION['role'] ?? null) !== 'fournisseur'
        || empty($_SESSION['id_fournisseur'])
    ) {
        header(
            'Location: ' . url('Auth/login')
            . '?erreur=' . urlencode('Connectez-vous comme fournisseur.')
        );
        exit;
    }

    return (int) $_SESSION['id_fournisseur'];
}

function anomalie_index($id = null): void
{
    $idFournisseur = anomalie_exiger_fournisseur();
    $anomalies = anomalie_bd()->listerEnAttente($idFournisseur);
    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;

    require ROOT . 'IHM/anomalie/index.php';
}

function anomalie_corriger($id = null): void
{
    $idFournisseur = anomalie_exiger_fournisseur();
    $idAnomalie = (int) ($id ?? 0);
    $anomalie = anomalie_bd()->trouver($idAnomalie, $idFournisseur);

    if ($anomalie === null) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        return;
    }

    $erreurs = [];
    $valeurSaisie = (string) $anomalie['index_saisi'];

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $valeurSaisie = trim((string) ($_POST['valeur_corrigee'] ?? ''));

        if (!anomalie_csrf_valide()) {
            $erreurs['general'] = 'Session expirée. Rechargez la page.';
        } else {
            $normalisee = str_replace(',', '.', $valeurSaisie);

            if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $normalisee)) {
                $erreurs['valeur_corrigee'] = 'Valeur numérique positive attendue.';
            } else {
                $valeur = (float) $normalisee;

                if (
                    $anomalie['index_precedent'] !== null
                    && $valeur < (float) $anomalie['index_precedent']
                ) {
                    $erreurs['valeur_corrigee'] = 'L’index corrigé doit être supérieur ou égal à l’index précédent.';
                } elseif (anomalie_bd()->corriger($idAnomalie, $idFournisseur, $valeur)) {
                    header(
                        'Location: ' . url('Anomalie/index')
                        . '?succes=' . urlencode('Anomalie corrigée avec succès.')
                    );
                    exit;
                } else {
                    $erreurs['general'] = 'Impossible de corriger l’anomalie.';
                }
            }
        }
    }

    $csrf = csrf_token();

    require ROOT . 'IHM/anomalie/corriger.php';
}

function anomalie_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}