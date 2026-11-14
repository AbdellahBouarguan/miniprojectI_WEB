<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/VerificationBD.php';

final class Verification
{
    /** Écart maximal toléré (en %) entre la consommation saisie et celle de l'agent. */
    public const TOLERANCE_POURCENT = 5.0;

    /** Nombre de relevés mensuels validés attendus sur l'année. */
    public const MOIS_ATTENDUS = 12;

    /** Fichier déposé par l'agent, relatif à la racine du projet. */
    public const FICHIER_AGENT = 'import/Consommation_Annuelle.txt';

    /**
     * Calcule, pour une ligne issue de VerificationBD::listerVerification(),
     * la consommation annuelle saisie par le client, les écarts et le résultat.
     *
     * Résultat : CONFORME | ECART | INCOMPLET | SANS_RELEVE
     */
    public static function analyser(array $l): array
    {
        $agentPresent = $l['id_annuelle'] !== null;
        $nbMois = (int) $l['nb_mois'];

        $saisiEau = self::consommationSaisie($l['fin_eau'], $l['base_eau']);
        $saisiElec = self::consommationSaisie($l['fin_elec'], $l['base_elec']);
        $agentEau = $agentPresent ? (float) $l['agent_eau'] : null;
        $agentElec = $agentPresent ? (float) $l['agent_elec'] : null;

        $ecartEau = ($saisiEau !== null && $agentEau !== null)
            ? self::ecartPourcent($saisiEau, $agentEau)
            : null;
        $ecartElec = ($saisiElec !== null && $agentElec !== null)
            ? self::ecartPourcent($saisiElec, $agentElec)
            : null;

        if (!$agentPresent) {
            $resultat = 'SANS_RELEVE';
        } elseif ($nbMois < self::MOIS_ATTENDUS) {
            $resultat = 'INCOMPLET';
        } elseif (
            ($ecartEau ?? 0.0) > self::TOLERANCE_POURCENT
            || ($ecartElec ?? 0.0) > self::TOLERANCE_POURCENT
        ) {
            $resultat = 'ECART';
        } else {
            $resultat = 'CONFORME';
        }

        return $l + [
            'agent_present' => $agentPresent,
            'saisi_eau' => $saisiEau,
            'saisi_elec' => $saisiElec,
            'agent_eau' => $agentEau,
            'agent_elec' => $agentElec,
            'ecart_eau' => $ecartEau,
            'ecart_elec' => $ecartElec,
            'resultat' => $resultat,
        ];
    }

    /**
     * Importe automatiquement le fichier de l'agent (idempotent : on peut le relire
     * à chaque ouverture de la page sans créer de doublons).
     *
     * Colonnes : ID_Client ; ID_Agent ; ConsommationEau ; ConsommationElect ; Annee ; Date_Saisie
     *
     * @return array{fichier: bool, importees: int, rejets: string[]}
     */
    public static function importerFichier(VerificationBD $bd, int $idFournisseur): array
    {
        $rapport = ['fichier' => false, 'importees' => 0, 'rejets' => []];
        $chemin = ROOT . self::FICHIER_AGENT;

        if (!is_file($chemin)) {
            return $rapport;
        }

        $rapport['fichier'] = true;
        $lignes = file($chemin, FILE_IGNORE_NEW_LINES);

        if ($lignes === false) {
            $rapport['rejets'][] = 'Fichier illisible.';
            return $rapport;
        }

        $enteteVu = false;

        foreach ($lignes as $index => $brute) {
            $numero = $index + 1;
            $ligne = trim($brute, " \t\r\n\xEF\xBB\xBF");

            if ($ligne === '' || $ligne[0] === '#') {
                continue;
            }

            $separateur = str_contains($ligne, ';')
                ? ';'
                : (str_contains($ligne, "\t") ? "\t" : ',');
            $colonnes = array_map('trim', explode($separateur, $ligne));

            // Première ligne non vide qui ne commence pas par un nombre = en-tête.
            if (!$enteteVu && !ctype_digit($colonnes[0])) {
                $enteteVu = true;
                continue;
            }
            $enteteVu = true;

            if (count($colonnes) < 6) {
                $rapport['rejets'][] = "Ligne $numero : 6 colonnes attendues.";
                continue;
            }

            [$cClient, $cAgent, $cEau, $cElec, $cAnnee, $cDate] = $colonnes;

            $eau = self::lireNombre($cEau);
            $elec = self::lireNombre($cElec);
            $date = self::lireDate($cDate);

            if (
                !ctype_digit($cClient)
                || ($cAgent !== '' && !ctype_digit($cAgent))
                || $eau === null
                || $elec === null
                || !preg_match('/^20\d{2}$/', $cAnnee)
                || $date === null
            ) {
                $rapport['rejets'][] = "Ligne $numero : valeurs invalides.";
                continue;
            }

            $idClient = (int) $cClient;

            if (!$bd->clientAppartient($idClient, $idFournisseur)) {
                $rapport['rejets'][] = "Ligne $numero : client $idClient introuvable.";
                continue;
            }

            $ok = $bd->enregistrerReleveAgent(
                $idClient,
                $cAgent === '' ? null : (int) $cAgent,
                (int) $cAnnee,
                $eau,
                $elec,
                $date
            );

            if ($ok) {
                $rapport['importees']++;
            } else {
                $rapport['rejets'][] = "Ligne $numero : enregistrement impossible.";
            }
        }

        return $rapport;
    }

    private static function consommationSaisie(mixed $fin, mixed $base): ?float
    {
        if ($fin === null) {
            return null;
        }

        return max(0.0, (float) $fin - (float) ($base ?? 0));
    }

    private static function ecartPourcent(float $saisi, float $agent): float
    {
        if ($agent <= 0.0) {
            return $saisi > 0.0 ? 100.0 : 0.0;
        }

        return abs($saisi - $agent) / $agent * 100.0;
    }

    private static function lireNombre(string $valeur): ?float
    {
        $normalisee = str_replace(',', '.', $valeur);

        return preg_match('/^\d{1,10}(\.\d{1,2})?$/', $normalisee)
            ? (float) $normalisee
            : null;
    }

    private static function lireDate(string $valeur): ?string
    {
        foreach (['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y'] as $format) {
            $date = DateTime::createFromFormat('!' . $format, $valeur);

            if ($date !== false && $date->format($format) === $valeur) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        return null;
    }
}

function verification_exiger_fournisseur(): int
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

function verification_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}

function verification_index($id = null): void
{
    $idFournisseur = verification_exiger_fournisseur();

    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;
    $rapport = ['fichier' => false, 'importees' => 0, 'rejets' => []];
    $annees = [(int) date('Y')];
    $annee = $annees[0];
    $lignes = [];

    try {
        $bd = new VerificationBD(db());
        $rapport = Verification::importerFichier($bd, $idFournisseur);
        $annees = $bd->anneesDisponibles($idFournisseur);

        $demandee = $_GET['annee'] ?? '';

        if (is_string($demandee) && ctype_digit($demandee) && in_array((int) $demandee, $annees, true)) {
            $annee = (int) $demandee;
        } else {
            $annee = $bd->derniereAnneeReleveAgent($idFournisseur) ?? $annees[0];
        }

        $lignes = array_map(
            static fn(array $l): array => Verification::analyser($l),
            $bd->listerVerification($idFournisseur, $annee)
        );
    } catch (Throwable $e) {
        error_log('Verification annuelle : ' . $e->getMessage());
        $erreur = 'Impossible de charger la vérification annuelle. '
            . 'Vérifiez que sql/verification_annuelle.sql a bien été exécuté.';
    }

    $csrf = csrf_token();

    require ROOT . 'IHM/verification/index.php';
}

function verification_decider($id = null): void
{
    $idFournisseur = verification_exiger_fournisseur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Verification/index'));
        exit;
    }

    $idClient = (int) ($id ?? 0);
    $annee = (int) ($_POST['annee'] ?? 0);
    $decision = (string) ($_POST['decision'] ?? '');
    $retour = url('Verification/index') . '?annee=' . $annee;

    if (!verification_csrf_valide()) {
        header('Location: ' . $retour . '&erreur=' . urlencode('Session expirée. Rechargez la page.'));
        exit;
    }

    if (
        $idClient <= 0
        || $annee < 2000
        || !in_array($decision, ['VALIDEE', 'REJETEE'], true)
    ) {
        header('Location: ' . $retour . '&erreur=' . urlencode('Demande invalide.'));
        exit;
    }

    $ok = (new VerificationBD(db()))->decider($idClient, $annee, $idFournisseur, $decision);

    if ($ok) {
        $message = $decision === 'VALIDEE' ? 'Consommation annuelle validée.' : 'Consommation annuelle rejetée.';
        header('Location: ' . $retour . '&succes=' . urlencode($message));
    } else {
        header('Location: ' . $retour . '&erreur=' . urlencode('Impossible d’enregistrer la décision.'));
    }

    exit;
}