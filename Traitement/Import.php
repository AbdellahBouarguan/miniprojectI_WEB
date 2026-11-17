<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Connexion.php';
require_once __DIR__ . '/../BD/AgentBD.php';
require_once __DIR__ . '/../BD/ConsoAnnuelle.php';

/**
 * Module Agent : espace dedie au depot du fichier annuel
 * Consommation_Annuelle.txt.
 *
 * Format attendu (entete obligatoire) :
 *   ID_Client,ID_Agent,ConsommationEau,ConsommationElect,Annee,Date_Saisie
 */

const IMPORT_ENTETE_ATTENDU = 'ID_Client,ID_Agent,ConsommationEau,ConsommationElect,Annee,Date_Saisie';
const IMPORT_TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo
const IMPORT_MAX_DETAILS = 200;            // details conserves dans le rapport

function import_exiger_agent(): int
{
    if (
        ($_SESSION['role'] ?? null) !== 'agent'
        || empty($_SESSION['id_agent'])
    ) {
        header(
            'Location: ' . url('Auth/login')
            . '?erreur=' . urlencode('Connectez-vous comme agent pour importer un fichier.')
        );
        exit;
    }

    return (int) $_SESSION['id_agent'];
}

function import_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}

function import_index($id = null): void
{
    $idAgent = import_exiger_agent();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        import_traiter_upload($idAgent);
    }

    $rapport = $_SESSION['import_rapport'] ?? null;
    $erreur = $_SESSION['import_erreur'] ?? null;
    unset($_SESSION['import_rapport'], $_SESSION['import_erreur']);

    $releves = new ConsoAnnuelleBD(pdo());

    $historique = $releves->listerParAgent($idAgent);
    $nbTotal = $releves->compter();
    $annees = $releves->annees();

    require ROOT . 'IHM/import/index.php';
}

function import_upload($id = null): void
{
    $idAgent = import_exiger_agent();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Import/index'));
        exit;
    }

    import_traiter_upload($idAgent);
}

/**
 * Valide le fichier, l'analyse puis redirige vers l'espace Agent
 * avec le rapport d'import en session (motif POST / Redirect / GET).
 */
function import_traiter_upload(int $idAgent): void
{
    if (!import_csrf_valide()) {
        $_SESSION['import_erreur'] = 'Session expirée. Rechargez la page et réessayez.';
        header('Location: ' . url('Import/index'));
        exit;
    }

    $fichier = $_FILES['fichier'] ?? null;

    if (!is_array($fichier)) {
        $_SESSION['import_erreur'] = 'Aucun fichier sélectionné.';
        header('Location: ' . url('Import/index'));
        exit;
    }

    $erreurUpload = (int) ($fichier['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($erreurUpload !== UPLOAD_ERR_OK) {
        $_SESSION['import_erreur'] = import_message_erreur_upload($erreurUpload);
        header('Location: ' . url('Import/index'));
        exit;
    }

    $chemin = (string) ($fichier['tmp_name'] ?? '');
    $nom = trim((string) ($fichier['name'] ?? 'fichier.txt'));
    $taille = (int) ($fichier['size'] ?? 0);

    if (!is_uploaded_file($chemin)) {
        $_SESSION['import_erreur'] = 'Le fichier n’a pas pu être reçu. Réessayez.';
        header('Location: ' . url('Import/index'));
        exit;
    }

    if ($taille <= 0 || $taille > IMPORT_TAILLE_MAX) {
        $_SESSION['import_erreur'] = 'Fichier vide ou trop volumineux (2 Mo maximum).';
        header('Location: ' . url('Import/index'));
        exit;
    }

    $extension = strtolower(pathinfo($nom, PATHINFO_EXTENSION));

    if ($extension !== 'txt') {
        $_SESSION['import_erreur'] = 'Le fichier doit être au format .txt.';
        header('Location: ' . url('Import/index'));
        exit;
    }

    $contenu = file_get_contents($chemin);

    if ($contenu === false) {
        $_SESSION['import_erreur'] = 'Impossible de lire le fichier déposé.';
        header('Location: ' . url('Import/index'));
        exit;
    }

    $resultat = import_analyser($contenu, $idAgent);

    if (!$resultat['entete_valide']) {
        $_SESSION['import_erreur'] = 'En-tête invalide. Format attendu : ' . IMPORT_ENTETE_ATTENDU;
        header('Location: ' . url('Import/index'));
        exit;
    }

    $_SESSION['import_rapport'] = [
        'fichier' => $nom,
        'inseres' => $resultat['inseres'],
        'ignores' => $resultat['ignores'],
        'rejetes' => $resultat['rejetes'],
        'nb_inseres' => count($resultat['inseres']),
        'nb_ignores' => count($resultat['ignores']),
        'nb_rejetes' => count($resultat['rejetes']),
        'nb_lignes' => $resultat['nb_lignes'],
    ];

    header('Location: ' . url('Import/index'));
    exit;
}

function import_message_erreur_upload(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (2 Mo maximum).',
        UPLOAD_ERR_PARTIAL => 'Le téléversement a été interrompu. Réessayez.',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier sélectionné.',
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Erreur serveur : dossier temporaire indisponible.',
        default => 'Le téléversement a échoué (code ' . $code . ').',
    };
}

/**
 * Analyse le contenu du fichier annuel.
 *
 * @return array{entete_valide: bool, inseres: array<int, string>, ignores: array<int, string>, rejetes: array<int, string>, nb_lignes: int}
 */
function import_analyser(string $contenu, int $idAgentSession): array
{
    $contenu = import_normaliser_contenu($contenu);
    $lignes = explode("\n", $contenu);

    $resultat = [
        'entete_valide' => false,
        'inseres' => [],
        'ignores' => [],
        'rejetes' => [],
        'nb_lignes' => 0,
    ];

    // 1. Entete : premiere ligne non vide
    $indexEntete = null;

    foreach ($lignes as $numero => $ligne) {
        if (trim($ligne) !== '') {
            $indexEntete = $numero;
            break;
        }
    }

    if ($indexEntete === null) {
        return $resultat;
    }

    if (import_entete_normalisee($lignes[$indexEntete]) !== import_entete_attendue()) {
        return $resultat;
    }

    $resultat['entete_valide'] = true;

    // 2. Lignes de donnees
    $releves = new ConsoAnnuelleBD(pdo());
    $agents = new AgentBD(pdo());
    $clientsExistants = $releves->idsClientsExistants();
    $agentsExistants = $agents->idsExistants();

    $pdo = pdo();
    $pdo->beginTransaction();

    try {
        for ($i = $indexEntete + 1; $i < count($lignes); $i++) {
            $brut = trim($lignes[$i]);

            if ($brut === '') {
                continue;
            }

            $numeroLigne = $i + 1;
            $resultat['nb_lignes']++;

            $ligne = import_ligne($brut, $numeroLigne, $idAgentSession, $clientsExistants, $agentsExistants, $releves);

            if ($ligne['statut'] === 'rejet') {
                $resultat['rejetes'][] = $ligne['journal'];
                continue;
            }

            if ($ligne['statut'] === 'ignore') {
                $resultat['ignores'][] = $ligne['journal'];
                continue;
            }

            $detail = $ligne['donnees'];
            $releves->inserer(
                $detail['client'],
                $detail['agent'],
                $detail['eau'],
                $detail['elec'],
                $detail['annee'],
                $detail['date']
            );
            $resultat['inseres'][] = sprintf(
                'Ligne %d · client #%d · %d · eau %s m³ · élec %s kWh',
                $numeroLigne,
                $detail['client'],
                $detail['annee'],
                import_nombre($detail['eau']),
                import_nombre($detail['elec'])
            );
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $resultat['inseres'] = [];
        $resultat['rejetes'][] = 'Erreur technique pendant l’import : ' . $e->getMessage();
    }

    $resultat['inseres'] = array_slice($resultat['inseres'], 0, IMPORT_MAX_DETAILS);
    $resultat['ignores'] = array_slice($resultat['ignores'], 0, IMPORT_MAX_DETAILS);
    $resultat['rejetes'] = array_slice($resultat['rejetes'], 0, IMPORT_MAX_DETAILS);

    return $resultat;
}

/**
 * Valide une ligne de donnees et retourne son statut :
 *   - 'ok'     : donnees inserees ;
 *   - 'ignore' : doublon, ligne volontairement ignoree ;
 *   - 'rejet'  : ligne invalide, motif dans journal.
 *
 * @return array{statut: string, journal: ?string, donnees: ?array}
 */
function import_ligne(
    string $brut,
    int $numeroLigne,
    int $idAgentSession,
    array $clientsExistants,
    array $agentsExistants,
    ConsoAnnuelleBD $releves
): array {
    $champs = str_getcsv($brut, ',', '"', '\\');

    if (count($champs) !== 6) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : 6 champs attendus, %d trouvés.',
            $numeroLigne,
            count($champs)
        ));
    }

    $champs = array_map(static fn ($valeur): string => trim((string) $valeur), $champs);
    [$idClient, $idAgent, $eau, $elec, $annee, $date] = $champs;

    // ID_Client
    if (!preg_match('/^\d+$/', $idClient) || !isset($clientsExistants[(int) $idClient])) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : ID_Client « %s » inconnu.',
            $numeroLigne,
            $idClient
        ));
    }

    // ID_Agent : la colonne doit designer un agent existant. Si elle
    // est vide ou nulle, l'agent connecte fait foi.
    $idAgentInt = $idAgentSession;

    if ($idAgent !== '' && $idAgent !== '0') {
        if (!preg_match('/^\d+$/', $idAgent) || !isset($agentsExistants[(int) $idAgent])) {
            return import_statut('rejet', sprintf(
                'Ligne %d · rejetée : ID_Agent « %s » inconnu.',
                $numeroLigne,
                $idAgent
            ));
        }

        $idAgentInt = (int) $idAgent;
    }

    // Consommations
    $eauFloat = import_nombre_valide($eau);
    $elecFloat = import_nombre_valide($elec);

    if ($eauFloat === null) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : ConsommationEau « %s » invalide.',
            $numeroLigne,
            $eau
        ));
    }

    if ($elecFloat === null) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : ConsommationElect « %s » invalide.',
            $numeroLigne,
            $elec
        ));
    }

    // Annee
    if (!preg_match('/^\d{4}$/', $annee)) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : Annee « %s » invalide.',
            $numeroLigne,
            $annee
        ));
    }

    $anneeInt = (int) $annee;

    if ($anneeInt < 2000 || $anneeInt > 2100) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : Annee %d hors limites (2000-2100).',
            $numeroLigne,
            $anneeInt
        ));
    }

    // Date de saisie (facultative : date du jour par defaut)
    $dateNormalisee = import_date_valide($date);

    if ($dateNormalisee === null) {
        return import_statut('rejet', sprintf(
            'Ligne %d · rejetée : Date_Saisie « %s » invalide.',
            $numeroLigne,
            $date
        ));
    }

    // Doublon : deja importe pour ce client et cette annee
    if ($releves->existe((int) $idClient, $anneeInt)) {
        return import_statut('ignore', sprintf(
            'Ligne %d · ignorée : relevé %d déjà importé pour le client #%d.',
            $numeroLigne,
            $anneeInt,
            (int) $idClient
        ));
    }

    return import_statut('ok', null, [
        'client' => (int) $idClient,
        'agent' => $idAgentInt,
        'eau' => $eauFloat,
        'elec' => $elecFloat,
        'annee' => $anneeInt,
        'date' => $dateNormalisee,
    ]);
}

/** @return array{statut: string, journal: ?string, donnees: ?array} */
function import_statut(string $statut, ?string $journal = null, ?array $donnees = null): array
{
    return ['statut' => $statut, 'journal' => $journal, 'donnees' => $donnees];
}

function import_normaliser_contenu(string $contenu): string
{
    // BOM UTF-8 emis par Excel
    $contenu = preg_replace('/^\xEF\xBB\xBF/', '', $contenu) ?? $contenu;

    // Encodage Windows-1252 (exports Excel en français).
    // mbstring n'est pas toujours installe : on utilise une verification
    // UTF-8 de secours et iconv si disponible.
    $utf8Valide = function_exists('mb_check_encoding')
        ? mb_check_encoding($contenu, 'UTF-8')
        : preg_match('//u', $contenu) === 1;

    if (!$utf8Valide) {
        if (function_exists('mb_convert_encoding')) {
            $contenu = mb_convert_encoding($contenu, 'UTF-8', 'Windows-1252');
        } elseif (function_exists('iconv')) {
            $converti = iconv('Windows-1252', 'UTF-8//IGNORE', $contenu);
            $contenu = $converti !== false ? $converti : $contenu;
        }
    }

    return str_replace(["\r\n", "\r"], "\n", $contenu);
}

/**
 * @return array<int, string> colonnes normalisees (minuscules, sans separateur)
 */
function import_entete_normalisee(string $ligne): array
{
    $ligne = preg_replace('/^\xEF\xBB\xBF/', '', $ligne) ?? $ligne;

    return array_map(
        static function (string $colonne): string {
            return strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $colonne));
        },
        explode(',', $ligne)
    );
}

/** @return array<int, string> */
function import_entete_attendue(): array
{
    return ['idclient', 'idagent', 'consommationeau', 'consommationelect', 'annee', 'datesaisie'];
}

/** Accepte "12", "12.5" et "12,5" ; refuse le vide et le negatif. */
function import_nombre_valide(string $valeur): ?float
{
    $valeur = trim(str_replace([' ', ','], ['', '.'], $valeur));

    if ($valeur === '' || !is_numeric($valeur)) {
        return null;
    }

    $nombre = (float) $valeur;

    return $nombre >= 0 ? $nombre : null;
}

/** Retourne la date au format MySQL, ou null si elle est invalide. */
function import_date_valide(string $valeur): ?string
{
    $valeur = trim($valeur);

    if ($valeur === '') {
        return date('Y-m-d H:i:s');
    }

    $formats = [
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        '!Y-m-d',
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        '!d/m/Y',
        '!d-m-Y',
    ];

    foreach ($formats as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $valeur);

        if (!$date instanceof DateTimeImmutable) {
            continue;
        }

        $erreurs = DateTimeImmutable::getLastErrors();

        if (is_array($erreurs) && (($erreurs['warning_count'] ?? 0) > 0 || ($erreurs['error_count'] ?? 0) > 0)) {
            continue;
        }

        return $date->format('Y-m-d H:i:s');
    }

    return null;
}

function import_nombre(float $valeur): string
{
    return rtrim(rtrim(number_format($valeur, 2, ',', ' '), '0'), ',');
}
