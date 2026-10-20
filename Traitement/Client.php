<?php

require_once ROOT . 'BD/ClientModel.php';

/*
 * Gestion des clients.
 *
 * Cote fournisseur : liste, recherche, detail, ajout, modification.
 * Cote client      : consultation de son propre profil.
 *
 * Contrat avec Auth : apres connexion, la session contient
 *   $_SESSION['role']    = 'fournisseur' ou 'client'
 *   $_SESSION['user_id'] = id_fournisseur ou id_client selon le role
 */

// ---------------------------------------------------------------
// Fonctions utilitaires
// ---------------------------------------------------------------

function client_redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function client_forbidden(): never
{
    http_response_code(403);
    $title = 'Acces refuse';
    require ROOT . 'IHM/403.php';
    exit;
}

/** Exige une session connectee avec l'un des roles donnes. */
function client_require_role(string ...$roles): void
{
    $role = $_SESSION['role'] ?? null;

    if ($role === null) {
        client_redirect('Auth/login');
    }

    if (!in_array($role, $roles, true)) {
        client_forbidden();
    }
}

function client_flash_set(string $message): void
{
    $_SESSION['flash'] = $message;
}

function client_flash_pull(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message;
}

/** Recupere un client et verifie qu'il appartient bien au fournisseur connecte. */
function client_load_for_fournisseur(?string $id): array
{
    $client = ctype_digit((string) $id) ? client_model_find((int) $id) : null;

    if ($client === null || (int) $client['id_fournisseur'] !== (int) $_SESSION['user_id']) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        exit;
    }

    return $client;
}

/**
 * Valide le formulaire client.
 * Renvoie [donnees nettoyees, erreurs, mot de passe saisi]. $idClient = 0 pour un ajout.
 */
function client_validate(array $in, int $idClient = 0): array
{
    $errors = [];

    $data = [
        'cin'       => strtoupper(trim((string) ($in['cin'] ?? ''))),
        'nom'       => trim((string) ($in['nom'] ?? '')),
        'prenom'    => trim((string) ($in['prenom'] ?? '')),
        'adresse'   => trim((string) ($in['adresse'] ?? '')),
        'email'     => strtolower(trim((string) ($in['email'] ?? ''))),
        'telephone' => trim((string) ($in['telephone'] ?? '')),
    ];
    $password = (string) ($in['mot_de_passe'] ?? '');

    if ($data['cin'] === '' || !preg_match('/^[A-Z0-9]{1,20}$/', $data['cin'])) {
        $errors['cin'] = 'CIN obligatoire (lettres et chiffres, 20 caracteres maximum).';
    } elseif (client_model_cin_exists($data['cin'], $idClient)) {
        $errors['cin'] = 'Ce CIN est deja utilise.';
    }

    if ($data['nom'] === '' || mb_strlen($data['nom']) > 100) {
        $errors['nom'] = 'Nom obligatoire (100 caracteres maximum).';
    }

    if ($data['prenom'] === '' || mb_strlen($data['prenom']) > 100) {
        $errors['prenom'] = 'Prenom obligatoire (100 caracteres maximum).';
    }

    if ($data['adresse'] === '' || mb_strlen($data['adresse']) > 255) {
        $errors['adresse'] = 'Adresse obligatoire (255 caracteres maximum).';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
        $errors['email'] = 'Adresse email invalide.';
    } elseif (client_model_email_exists($data['email'], $idClient)) {
        $errors['email'] = 'Cet email est deja utilise.';
    }

    if ($data['telephone'] !== '' && !preg_match('/^[0-9+().\s-]{6,30}$/', $data['telephone'])) {
        $errors['telephone'] = 'Numero de telephone invalide.';
    }

    // Ajout : mot de passe obligatoire. Modification : vide = inchange.
    if ($idClient === 0 && $password === '') {
        $errors['mot_de_passe'] = 'Mot de passe obligatoire.';
    } elseif ($password !== '' && mb_strlen($password) < 8) {
        $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    }

    // La colonne telephone accepte NULL.
    if ($data['telephone'] === '') {
        $data['telephone'] = null;
    }

    return [$data, $errors, $password];
}

// ---------------------------------------------------------------
// Cote fournisseur
// ---------------------------------------------------------------

function client_all($id = null): void
{
    client_require_role('fournisseur');

    $title = 'Gestion des clients';
    $search = trim((string) ($_GET['q'] ?? ''));
    $clients = client_model_all((int) $_SESSION['user_id'], $search);
    $flash = client_flash_pull();

    require ROOT . 'IHM/client/all.php';
}

function client_add($id = null): void
{
    client_require_role('fournisseur');

    $title = 'Ajouter un client';
    $errors = [];
    $old = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old = $_POST;
        [$data, $errors, $password] = client_validate($_POST);

        if ($errors === []) {
            try {
                client_model_insert(
                    (int) $_SESSION['user_id'],
                    $data,
                    password_hash($password, PASSWORD_DEFAULT)
                );
                client_flash_set('Client ajoute avec succes.');
                client_redirect('Client/all');
            } catch (mysqli_sql_exception $e) {
                // 1062 = doublon (cas de deux ajouts simultanes)
                $errors['general'] = $e->getCode() === 1062
                    ? 'CIN ou email deja utilise.'
                    : 'Erreur lors de l\'enregistrement.';
            }
        }
    }

    require ROOT . 'IHM/client/add.php';
}

function client_update($id = null): void
{
    client_require_role('fournisseur');

    $client = client_load_for_fournisseur($id);
    $idClient = (int) $client['id_client'];

    $title = 'Modifier un client';
    $errors = [];
    $old = $client;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old = $_POST;
        [$data, $errors, $password] = client_validate($_POST, $idClient);

        if ($errors === []) {
            try {
                client_model_update($idClient, $data);

                if ($password !== '') {
                    client_model_update_password($idClient, password_hash($password, PASSWORD_DEFAULT));
                }

                client_flash_set('Client modifie avec succes.');
                client_redirect('Client/one/' . $idClient);
            } catch (mysqli_sql_exception $e) {
                $errors['general'] = $e->getCode() === 1062
                    ? 'CIN ou email deja utilise.'
                    : 'Erreur lors de la modification.';
            }
        }
    }

    require ROOT . 'IHM/client/update.php';
}

// ---------------------------------------------------------------
// Fournisseur (n'importe lequel de ses clients) et client (le sien)
// ---------------------------------------------------------------

function client_one($id = null): void
{
    client_require_role('fournisseur', 'client');

    if ($_SESSION['role'] === 'client') {
        // Un client ne voit que son propre profil, quel que soit l'id demande.
        if ($id !== null && (int) $id !== (int) $_SESSION['user_id']) {
            client_forbidden();
        }
        $client = client_model_find((int) $_SESSION['user_id']);

        if ($client === null) {
            client_forbidden();
        }
    } else {
        $client = client_load_for_fournisseur($id);
    }

    $title = $_SESSION['role'] === 'client' ? 'Mon profil' : 'Detail du client';
    $flash = client_flash_pull();

    require ROOT . 'IHM/client/one.php';
}
