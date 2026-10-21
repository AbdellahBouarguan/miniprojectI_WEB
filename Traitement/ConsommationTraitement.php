<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Anomalie.php';
require_once __DIR__ . '/../BD/ConsommationBD.php';

final class ConsommationTraitement
{
    private const JOUR_DEBUT = 18;
    private const MAX_PHOTO = 5 * 1024 * 1024; // 5 Mo

    private const MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private ConsommationBD $bd;

    public function __construct(private PDO $pdo)
    {
        $this->bd = new ConsommationBD($pdo);
    }
        public function afficherSaisie(): void
    {
        $idClient = $this->clientConnecte();
        $conso = null;
        $anciennes = [];

        if (!empty($_GET['id_conso'])) {
            $conso = $this->bd->trouver((int) $_GET['id_conso'], $idClient);

            if (!$conso || !in_array($conso['statut'], ['EN_ATTENTE', 'ANOMALIE'], true)) {
                $this->rediriger('conso.historique', [
                    'erreur' => 'Cette consommation n’est pas modifiable.',
                ]);
            }

            $anciennes = [
                'periode_mois' => $conso['periode_mois'],
                'periode_annee' => $conso['periode_annee'],
                'index_eau' => $conso['index_eau'],
                'index_elec' => $conso['index_elec'],
            ];
        }

        $this->rendreFormulaire([], $anciennes, $conso);
    }

    public function enregistrer(): void
    {
        $this->exigerPost();
        $this->verifierCsrf();

        $idClient = $this->clientConnecte();

        $idConso = !empty($_POST['id_conso']) ? (int) $_POST['id_conso'] : null;
        $consoExistante = null;

        if ($idConso !== null) {
            $consoExistante = $this->bd->trouver($idConso, $idClient);

            if (!$consoExistante || !in_array($consoExistante['statut'], ['EN_ATTENTE', 'ANOMALIE'], true)) {
                $this->rediriger('conso.historique', [
                    'erreur' => 'Cette consommation n’est pas modifiable.',
                ]);
            }
        }

        $mois = (int) ($_POST['periode_mois'] ?? 0);
        $annee = (int) ($_POST['periode_annee'] ?? 0);
        $erreurs = [];

        if ($consoExistante === null) {
            $erreurPeriode = $this->verifierPeriode($mois, $annee);

            if ($erreurPeriode !== null) {
                $erreurs['periode'] = $erreurPeriode;
            }
        } else {
            // La période d'une correction ne change pas
            $mois = (int) $consoExistante['periode_mois'];
            $annee = (int) $consoExistante['periode_annee'];
        }

        $indexEau = $this->lireIndex('index_eau', $erreurs);
        $indexElec = $this->lireIndex('index_elec', $erreurs);

        $photos = [];

        foreach (['EAU' => 'photo_eau', 'ELEC' => 'photo_elec'] as $type => $champ) {
            $photo = $this->lirePhoto($champ, $erreurs);

            if ($photo !== null) {
                $photos[$type] = $photo;
            }
        }

        if ($erreurs !== []) {
            $this->rendreFormulaire($erreurs, $_POST, $consoExistante);
            return;
        }

        if (
            $consoExistante === null
            && $this->bd->existePourPeriode($idClient, $mois, $annee)
        ) {
            $erreurs['periode'] = 'Une consommation existe déjà pour cette période.';
            $this->rendreFormulaire($erreurs, $_POST, null);
            return;
        }

        if ($consoExistante === null && $this->bd->aAnomalieEnAttente($idClient)) {
            $this->rediriger('conso.historique', [
                'erreur' => 'Résolvez d’abord l’anomalie en attente.',
            ]);
        }

        $infos = $this->bd->clientEtSeuils($idClient);

        if ($infos === null) {
            http_response_code(403);
            exit('Client inconnu.');
        }

        $precedent = $this->bd->derniereConsommationValidee($idClient, $mois, $annee);

        $anomalies = Anomalie::detecter(
            $precedent,
            (float) $indexEau,
            (float) $indexElec,
            (float) $infos['seuil_eau'],
            (float) $infos['seuil_elec']
        );

        $statut = $anomalies === [] ? 'VALIDEE' : 'ANOMALIE';
        $chemins = [];

        try {
            foreach ($photos as $type => $photo) {
                $chemins[$type] = $this->deplacerPhoto($photo, $idClient, $type);
            }

            if ($consoExistante === null) {
                $idConso = $this->bd->creer(
                    $idClient,
                    (int) $infos['id_fournisseur'],
                    $mois,
                    $annee,
                    (float) $indexEau,
                    (float) $indexElec,
                    $statut,
                    $chemins,
                    $anomalies
                );
            } else {
                $idConso = (int) $consoExistante['id_conso'];
                $anciensChemins = $this->bd->photosDe($idConso);

                $this->bd->mettreAJour(
                    $idConso,
                    (int) $infos['id_fournisseur'],
                    (float) $indexEau,
                    (float) $indexElec,
                    $statut,
                    $chemins,
                    $anomalies,
                    $precedent ?? []
                );

                // On supprime les anciennes images seulement après le commit
                foreach ($anciensChemins as $type => $ancienChemin) {
                    if (isset($chemins[$type]) && $chemins[$type] !== $ancienChemin) {
                        @unlink(dirname(__DIR__) . '/' . $ancienChemin);
                    }
                }
            }
        } catch (Throwable $e) {
            foreach ($chemins as $chemin) {
                @unlink(dirname(__DIR__) . '/' . $chemin);
            }

            error_log('[CONSO] ' . $e->getMessage());

            $erreurs['general'] = 'Erreur technique pendant l’enregistrement. Réessayez.';
            $this->rendreFormulaire($erreurs, $_POST, $consoExistante);
            return;
        }

        if ($anomalies !== []) {
            $this->rediriger('conso.confirmation', ['id_conso' => $idConso]);
        }

        $this->genererFacture($idConso);

        $this->rediriger('conso.detail', [
            'id_conso' => $idConso,
            'succes' => 'Consommation validée et facture générée.',
        ]);
    }

    public function afficherConfirmation(): void
    {
        $idClient = $this->clientConnecte();
        $idConso = (int) ($_GET['id_conso'] ?? 0);
        $conso = $this->bd->trouver($idConso, $idClient);

        if ($conso === null || $conso['statut'] !== 'ANOMALIE') {
            $this->rediriger('conso.historique', [
                'erreur' => 'Aucune anomalie à confirmer pour cette consommation.',
            ]);
        }

        $anomalies = array_values(array_filter(
            $this->bd->anomaliesDe($idConso),
            static fn (array $a): bool => $a['statut'] === 'EN_ATTENTE'
        ));

        $indexInferieur = $this->bd->aIndexInferieur($idConso);
        $calcul = $this->bd->consommationsCalculees($conso);
        $erreur = $_GET['erreur'] ?? null;

        require __DIR__ . '/../IHM/consommation/confirmation.php';
    }

    public function confirmer(): void
    {
        $this->exigerPost();
        $this->verifierCsrf();

        $idClient = $this->clientConnecte();
        $idConso = (int) ($_POST['id_conso'] ?? 0);
        $decision = (string) ($_POST['decision'] ?? '');

        $conso = $this->bd->trouver($idConso, $idClient);

        if ($conso === null || $conso['statut'] !== 'ANOMALIE') {
            $this->rediriger('conso.historique', [
                'erreur' => 'Consommation introuvable ou déjà traitée.',
            ]);
        }

        if ($decision === 'corriger') {
            $this->bd->remettreEnBrouillon($idConso);
            $this->rediriger('conso.saisie', ['id_conso' => $idConso]);
        }

        if ($decision !== 'confirmer') {
            $this->rediriger('conso.confirmation', ['id_conso' => $idConso]);
        }

        if ($this->bd->aIndexInferieur($idConso)) {
            $this->rediriger('conso.confirmation', [
                'id_conso' => $idConso,
                'erreur' => 'Un index inférieur au relevé précédent ne peut pas être confirmé. Corrigez la valeur.',
            ]);
        }

        $this->pdo->beginTransaction();

        try {
            $this->bd->confirmerAnomalies($idConso);
            $this->bd->changerStatut($idConso, 'VALIDEE');
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $this->genererFacture($idConso);

        $this->rediriger('conso.detail', [
            'id_conso' => $idConso,
            'succes' => 'Anomalie confirmée, facture générée.',
        ]);
    }

    public function facturer(): void
    {
        $this->exigerPost();
        $this->verifierCsrf();

        $idClient = $this->clientConnecte();
        $idConso = (int) ($_POST['id_conso'] ?? 0);
        $conso = $this->bd->trouver($idConso, $idClient);

        if ($conso === null || $conso['statut'] !== 'VALIDEE') {
            $this->rediriger('conso.historique', [
                'erreur' => 'Cette consommation n’est pas en attente de facturation.',
            ]);
        }

        $this->genererFacture($idConso);

        $this->rediriger('conso.detail', [
            'id_conso' => $idConso,
            'succes' => 'Facture générée.',
        ]);
    }

    public function historique(): void
    {
        $consommations = $this->bd->listerParClient($this->clientConnecte());
        $erreur = $_GET['erreur'] ?? null;
        $succes = $_GET['succes'] ?? null;

        require __DIR__ . '/../IHM/consommation/historique.php';
    }

    public function detail(): void
    {
        $idClient = $this->clientConnecte();
        $idConso = (int) ($_GET['id_conso'] ?? 0);
        $conso = $this->bd->trouver($idConso, $idClient);

        if ($conso === null) {
            $this->rediriger('conso.historique', ['erreur' => 'Consommation introuvable.']);
        }

        $calcul = $this->bd->consommationsCalculees($conso);
        $photos = $this->bd->photosDe($idConso);
        $anomalies = $this->bd->anomaliesDe($idConso);
        $facture = $this->bd->factureDe($idConso);
        $erreur = $_GET['erreur'] ?? null;
        $succes = $_GET['succes'] ?? null;

        require __DIR__ . '/../IHM/consommation/detail.php';
    }

    private function rendreFormulaire(array $erreurs, array $anciennes, ?array $conso): void
    {
        $idClient = $this->clientConnecte();

        $moisBrut = $anciennes['periode_mois'] ?? $conso['periode_mois'] ?? date('n');
        $anneeBrut = $anciennes['periode_annee'] ?? $conso['periode_annee'] ?? date('Y');

        $mois = is_scalar($moisBrut) ? (int) $moisBrut : (int) date('n');
        $annee = is_scalar($anneeBrut) ? (int) $anneeBrut : (int) date('Y');

        $precedent = $this->bd->derniereConsommationValidee($idClient, $mois, $annee);
        $csrf = csrf_token();

        require __DIR__ . '/../IHM/consommation/saisie.php';
    }

    private function verifierPeriode(int $mois, int $annee): ?string
    {
        if (
            $mois < 1 || $mois > 12
            || $annee < 2000 || $annee > 2100
            || !checkdate($mois, 1, $annee)
        ) {
            return 'Période invalide.';
        }

        $aujourdhui = new DateTimeImmutable('today');

        if (
            $annee !== (int) $aujourdhui->format('Y')
            || $mois !== (int) $aujourdhui->format('n')
        ) {
            return 'La saisie concerne uniquement le mois en cours.';
        }

        if ((int) $aujourdhui->format('j') < self::JOUR_DEBUT) {
            return 'La saisie est ouverte du 18 au dernier jour du mois.';
        }

        return null;
    }

    private function lireIndex(string $champ, array &$erreurs): ?float
    {
        $brut = $_POST[$champ] ?? '';

        if (!is_string($brut)) {
            $erreurs[$champ] = 'Valeur invalide.';
            return null;
        }

        $brut = trim(str_replace(',', '.', $brut));

        if ($brut === '') {
            $erreurs[$champ] = 'Ce champ est obligatoire.';
            return null;
        }

        if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $brut)) {
            $erreurs[$champ] = 'Valeur numérique positive attendue (ex. 1234.56).';
            return null;
        }

        return (float) $brut;
    }

    private function lirePhoto(string $champ, array &$erreurs): ?array
    {
        if (!isset($_FILES[$champ]) || $_FILES[$champ]['error'] === UPLOAD_ERR_NO_FILE) {
            $erreurs[$champ] = 'Photo obligatoire.';
            return null;
        }

        $fichier = $_FILES[$champ];

        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreurs[$champ] = 'Erreur pendant le téléversement (code ' . $fichier['error'] . ').';
            return null;
        }

        if ($fichier['size'] <= 0 || $fichier['size'] > self::MAX_PHOTO) {
            $erreurs[$champ] = 'La photo doit faire au maximum 5 Mo.';
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']) ?: '';

        if (!isset(self::MIMES[$mime])) {
            $erreurs[$champ] = 'Format accepté : JPG, PNG ou WEBP.';
            return null;
        }

        return [
            'tmp_name' => $fichier['tmp_name'],
            'mime' => $mime,
        ];
    }

    private function deplacerPhoto(array $photo, int $idClient, string $type): string
    {
        $extension = self::MIMES[$photo['mime']];
        $dossierRelatif = 'uploads/compteurs/client_' . $idClient;
        $dossierAbsolu = dirname(__DIR__) . '/' . $dossierRelatif;

        if (!is_dir($dossierAbsolu) && !mkdir($dossierAbsolu, 0775, true) && !is_dir($dossierAbsolu)) {
            throw new RuntimeException('Impossible de créer le dossier de téléversement.');
        }

        $nom = strtolower($type) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        if (!move_uploaded_file($photo['tmp_name'], $dossierAbsolu . '/' . $nom)) {
            throw new RuntimeException('Échec du déplacement de la photo.');
        }

        return $dossierRelatif . '/' . $nom;
    }

    private function clientConnecte(): int
    {
        if (($_SESSION['role'] ?? null) !== 'client' || empty($_SESSION['id_client'])) {
            $this->rediriger('auth.login', ['erreur' => 'Veuillez vous connecter comme client.']);
        }

        return (int) $_SESSION['id_client'];
    }

    private function exigerPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }
    }

    private function verifierCsrf(): void
    {
        $attendu = $_SESSION['csrf_token'] ?? '';
        $recu = $_POST['csrf'] ?? '';

        if (!is_string($attendu) || !is_string($recu) || !hash_equals($attendu, $recu)) {
            http_response_code(419);
            exit('Session expirée. Rechargez la page.');
        }
    }

    private function rediriger(string $action, array $parametres = []): void
    {
        $routes = [
            'conso.historique' => 'Consommation/all',
            'conso.confirmation' => 'Consommation/confirmation',
            'conso.detail' => 'Consommation/one',
            'conso.saisie' => 'Consommation/add',
            'auth.login' => 'Auth/login',
        ];

        $route = $routes[$action] ?? 'Consommation/all';
        $idConso = isset($parametres['id_conso']) ? (int) $parametres['id_conso'] : null;
        unset($parametres['id_conso']);

        $url = url($route . ($idConso !== null ? '/' . $idConso : ''));

        if ($parametres !== []) {
            $url .= '?' . http_build_query($parametres);
        }

        header('Location: ' . $url);
        exit;
    }

    private function genererFacture(int $idConso): void
    {
        $fichier = __DIR__ . '/FactureService.php';

        if (!is_file($fichier)) {
            $this->rediriger('conso.detail', [
                'id_conso' => $idConso,
                'erreur' => 'Le module de facturation n’est pas encore disponible.',
            ]);
        }

        require_once $fichier;

        try {
            (new FactureService($this->pdo))->generer($idConso);
        } catch (Throwable $e) {
            error_log('[FACTURE] ' . $e->getMessage());

            $this->rediriger('conso.detail', [
                'id_conso' => $idConso,
                'erreur' => 'Consommation validée, mais la facture n’a pas pu être générée.',
            ]);
        }
    }
}