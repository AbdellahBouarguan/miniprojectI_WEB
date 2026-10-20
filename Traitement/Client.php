<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/ClientBD.php';

function client_bd(): ClientBD
{
    return new ClientBD(db());
}

function client_exiger_fournisseur(): int
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

function client_all($id = null): void
{
    $idFournisseur = client_exiger_fournisseur();
    $clients = client_bd()->lister($idFournisseur);
    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;

    require ROOT . 'IHM/client/index.php';
}

function client_one($id = null): void
{
    $idFournisseur = client_exiger_fournisseur();
    $idClient = (int) ($id ?? 0);
    $client = client_bd()->trouver($idClient, $idFournisseur);

    if ($client === null) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        return;
    }

    unset($client['mot_de_passe']);
    $nbConsommations = client_bd()->nbConsommations($idClient);

    require ROOT . 'IHM/client/one.php';
}

function client_add($id = null): void
{
    $idFournisseur = client_exiger_fournisseur();
    $erreurs = [];
    $client = [
        'cin' => '',
        'nom' => '',
        'prenom' => '',
        'adresse' => '',
        'email' => '',
        'telephone' => '',
    ];

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        client_lire_formulaire($client, $erreurs, true);

        if ($erreurs === []) {
            if (client_bd()->emailExiste($client['email'])) {
                $erreurs['email'] = 'Cet email est déjà utilisé.';
            }

            if (client_bd()->cinExiste($client['cin'])) {
                $erreurs['cin'] = 'Ce CIN est déjà utilisé.';
            }
        }

        if ($erreurs === []) {
            $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');
            $client['mot_de_passe'] = password_hash($motDePasse, PASSWORD_DEFAULT);

            if (client_bd()->creer($idFournisseur, $client)) {
                header(
                    'Location: ' . url('Client/all')
                    . '?succes=' . urlencode('Client ajouté avec succès.')
                );
                exit;
            }

            $erreurs['general'] = 'Impossible d’ajouter le client.';
        }

        unset($client['mot_de_passe']);
    }

    $mode = 'add';
    $csrf = csrf_token();

    require ROOT . 'IHM/client/form.php';
}

function client_update($id = null): void
{
    $idFournisseur = client_exiger_fournisseur();
    $idClient = (int) ($id ?? 0);
    $existant = client_bd()->trouver($idClient, $idFournisseur);

    if ($existant === null) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        return;
    }

    $erreurs = [];
    $client = $existant;
    unset($client['mot_de_passe']);

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        client_lire_formulaire($client, $erreurs, false);

        if ($erreurs === []) {
            if (client_bd()->emailExiste($client['email'], $idClient)) {
                $erreurs['email'] = 'Cet email est déjà utilisé.';
            }

            if (client_bd()->cinExiste($client['cin'], $idClient)) {
                $erreurs['cin'] = 'Ce CIN est déjà utilisé.';
            }
        }

        if ($erreurs === []) {
            $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');

            $client['mot_de_passe'] = $motDePasse !== ''
                ? password_hash($motDePasse, PASSWORD_DEFAULT)
                : $existant['mot_de_passe'];

            if (client_bd()->mettreAJour($idClient, $idFournisseur, $client)) {
                header(
                    'Location: ' . url('Client/all')
                    . '?succes=' . urlencode('Client modifié avec succès.')
                );
                exit;
            }

            $erreurs['general'] = 'Impossible de modifier le client.';
        }

        unset($client['mot_de_passe']);
    }

    $mode = 'update';
    $csrf = csrf_token();

    require ROOT . 'IHM/client/form.php';
}

function client_delete($id = null): void
{
    $idFournisseur = client_exiger_fournisseur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Méthode non autorisée.');
    }

    $csrf = $_POST['csrf'] ?? '';
    $attendu = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($attendu)
        || $attendu === ''
        || !is_string($csrf)
        || !hash_equals($attendu, $csrf)
    ) {
        http_response_code(419);
        exit('Session expirée. Rechargez la page.');
    }

    $idClient = (int) ($id ?? 0);
    $client = client_bd()->trouver($idClient, $idFournisseur);

    if ($client === null) {
        header(
            'Location: ' . url('Client/all')
            . '?erreur=' . urlencode('Client introuvable.')
        );
        exit;
    }

    if (client_bd()->nbConsommations($idClient) > 0) {
        header(
            'Location: ' . url('Client/all')
            . '?erreur=' . urlencode(
                'Suppression impossible : ce client possède des consommations.'
            )
        );
        exit;
    }

    $supprime = client_bd()->supprimer($idClient, $idFournisseur);

    if ($supprime) {
        header(
            'Location: ' . url('Client/all')
            . '?succes=' . urlencode('Client supprimé.')
        );
    } else {
        header(
            'Location: ' . url('Client/all')
            . '?erreur=' . urlencode('Suppression impossible.')
        );
    }

    exit;
}

function client_lire_formulaire(array &$client, array &$erreurs, bool $creation): void
{
    $client['cin'] = trim((string) ($_POST['cin'] ?? ''));
    $client['nom'] = trim((string) ($_POST['nom'] ?? ''));
    $client['prenom'] = trim((string) ($_POST['prenom'] ?? ''));
    $client['adresse'] = trim((string) ($_POST['adresse'] ?? ''));
    $client['email'] = trim((string) ($_POST['email'] ?? ''));
    $client['telephone'] = trim((string) ($_POST['telephone'] ?? ''));

    if ($client['cin'] === '' || !preg_match('/^[A-Za-z0-9-]{4,20}$/', $client['cin'])) {
        $erreurs['cin'] = 'CIN invalide (4 à 20 caractères alphanumériques).';
    }

    if ($client['nom'] === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    }

    if ($client['prenom'] === '') {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    }

    if ($client['adresse'] === '') {
        $erreurs['adresse'] = 'L’adresse est obligatoire.';
    }

    if (
        $client['email'] === ''
        || !filter_var($client['email'], FILTER_VALIDATE_EMAIL)
    ) {
        $erreurs['email'] = 'Email invalide.';
    }

    if (
        $client['telephone'] !== ''
        && !preg_match('/^[0-9+ .-]{6,30}$/', $client['telephone'])
    ) {
        $erreurs['telephone'] = 'Téléphone invalide.';
    }

    $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');
    $confirmation = (string) ($_POST['mot_de_passe_confirmation'] ?? '');

    if ($creation || $motDePasse !== '') {
        if (strlen($motDePasse) < 6) {
            $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($motDePasse !== $confirmation) {
            $erreurs['mot_de_passe_confirmation'] = 'Les mots de passe ne correspondent pas.';
        }
    }
}
