<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/ClientBD.php';

function auth_login($id = null): void
{
    if (isset($_SESSION['role'])) {
        auth_rediriger_selon_role();
    }

    $erreur = null;
    $email = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');

        if (!auth_csrf_valide()) {
            $erreur = 'Session expirée. Rechargez la page.';
        } elseif ($email === '' || $motDePasse === '') {
            $erreur = 'Email et mot de passe obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Adresse e-mail invalide.';
        } else {
            $client = auth_trouver_client($email);

            if ($client !== null) {
                if (password_verify($motDePasse, $client['mot_de_passe'])) {
                    auth_connecter_client($client);
                }

                $erreur = 'Email ou mot de passe incorrect.';
            } else {
                $fournisseur = auth_trouver_fournisseur($email);

                if ($fournisseur !== null) {
                    if (password_verify($motDePasse, $fournisseur['mot_de_passe'])) {
                        auth_connecter_fournisseur($fournisseur);
                    }

                    $erreur = 'Email ou mot de passe incorrect.';
                } else {
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }
    }

    require ROOT . 'IHM/auth/login.php';
}

/**
 * Toute nouvelle adresse e-mail cree un compte client de demonstration.
 * Le client peut ensuite saisir sa consommation avec ce meme couple
 * email / mot de passe.
 */
function auth_creer_client_demo(string $email, string $motDePasse): ?array
{
    $idFournisseur = auth_premier_fournisseur();

    if ($idFournisseur === null) {
        return null;
    }

    $locale = strstr($email, '@', true) ?: 'client';
    $locale = trim((string) preg_replace('/[._-]+/', ' ', $locale));
    $nom = $locale !== '' ? ucwords($locale) : 'Client';
    $nom = substr($nom, 0, 100);

    $client = [
        'cin' => auth_generer_cin(),
        'nom' => $nom,
        'prenom' => 'Client',
        'adresse' => 'Non renseignée',
        'email' => $email,
        'telephone' => '',
        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
    ];

    $bd = new ClientBD(db());

    if (!$bd->creer($idFournisseur, $client)) {
        return null;
    }

    return auth_trouver_client($email);
}

function auth_premier_fournisseur(): ?int
{
    $stmt = mysqli_prepare(
        db(),
        'SELECT id_fournisseur FROM fournisseur ORDER BY id_fournisseur LIMIT 1'
    );
    mysqli_stmt_execute($stmt);

    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_assoc($resultat);

    return $ligne ? (int) $ligne['id_fournisseur'] : null;
}

function auth_generer_cin(): string
{
    $bd = new ClientBD(db());

    do {
        $cin = 'AUTO' . strtoupper(bin2hex(random_bytes(4)));
    } while ($bd->cinExiste($cin));

    return $cin;
}

function auth_connecter_client(array $client): void
{
    session_regenerate_id(true);

    $_SESSION['role'] = 'client';
    $_SESSION['id_client'] = (int) $client['id_client'];
    $_SESSION['nom'] = $client['nom'];
    $_SESSION['prenom'] = $client['prenom'] ?? '';
    $_SESSION['email'] = $client['email'];

    header('Location: ' . url('Consommation/add'));
    exit;
}

function auth_connecter_fournisseur(array $fournisseur): void
{
    session_regenerate_id(true);

    $_SESSION['role'] = 'fournisseur';
    $_SESSION['id_fournisseur'] = (int) $fournisseur['id_fournisseur'];
    $_SESSION['nom'] = $fournisseur['nom'];
    $_SESSION['prenom'] = '';
    $_SESSION['email'] = $fournisseur['email'];

    header('Location: ' . url('Client/all'));
    exit;
}

/**
 * Connexion de demonstration pour les maquettes / soutenance.
 * A desactiver ou supprimer en production.
 */
function auth_demo($id = null): void
{
    http_response_code(404);
    require ROOT . 'IHM/404.php';
    exit;

    $role = $id === 'fournisseur' ? 'fournisseur' : 'client';

    // Destination de demonstration, limitee a une liste blanche.
    $next = (string) ($_GET['next'] ?? '');
    $destinations = ['Consommation/add', 'Client/all', 'Anomalie/index', 'Facture/all'];

    if (!in_array($next, $destinations, true)) {
        $next = '';
    }

    if ($role === 'fournisseur') {
        $stmt = mysqli_prepare(
            db(),
            'SELECT id_fournisseur, nom, email
             FROM fournisseur
             ORDER BY id_fournisseur
             LIMIT 1'
        );
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);
        $utilisateur = mysqli_fetch_assoc($resultat);

        if (!$utilisateur) {
            header('Location: ' . url('Auth/login') . '?erreur=' . urlencode('Aucun fournisseur de démonstration.'));
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['role'] = 'fournisseur';
        $_SESSION['id_fournisseur'] = (int) $utilisateur['id_fournisseur'];
        $_SESSION['nom'] = $utilisateur['nom'];
        $_SESSION['prenom'] = '';
        $_SESSION['email'] = $utilisateur['email'];

        header('Location: ' . url($next !== '' ? $next : 'Client/all'));
        exit;
    }

    $stmt = mysqli_prepare(
        db(),
        "SELECT c.id_client, c.nom, c.prenom, c.email
         FROM client c
         WHERE EXISTS (
             SELECT 1 FROM consommation co
             WHERE co.id_client = c.id_client
               AND co.statut IN ('VALIDEE', 'FACTUREE')
         )
         AND NOT EXISTS (
             SELECT 1
             FROM anomalie a
             JOIN consommation co2 ON co2.id_conso = a.id_conso
             WHERE co2.id_client = c.id_client
               AND a.statut = 'EN_ATTENTE'
         )
         AND NOT EXISTS (
             SELECT 1 FROM consommation co3
             WHERE co3.id_client = c.id_client
               AND co3.periode_mois = MONTH(CURDATE())
               AND co3.periode_annee = YEAR(CURDATE())
         )
         ORDER BY c.id_client
         LIMIT 1"
    );
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $utilisateur = mysqli_fetch_assoc($resultat);

    if (!$utilisateur) {
        $stmt = mysqli_prepare(
            db(),
            'SELECT id_client, nom, prenom, email
             FROM client
             ORDER BY id_client
             LIMIT 1'
        );
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);
        $utilisateur = mysqli_fetch_assoc($resultat);
    }

    if (!$utilisateur) {
        header('Location: ' . url('Auth/login') . '?erreur=' . urlencode('Aucun client de démonstration.'));
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['role'] = 'client';
    $_SESSION['id_client'] = (int) $utilisateur['id_client'];
    $_SESSION['nom'] = $utilisateur['nom'];
    $_SESSION['prenom'] = $utilisateur['prenom'];
    $_SESSION['email'] = $utilisateur['email'];

    header('Location: ' . url($next !== '' ? $next : 'Consommation/add'));
    exit;
}

function auth_logout($id = null): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: ' . url(''));
    exit;
}

function auth_rediriger_selon_role(): void
{
    if (($_SESSION['role'] ?? null) === 'fournisseur') {
        header('Location: ' . url('Client/all'));
    } else {
        header('Location: ' . url('Consommation/add'));
    }

    exit;
}

function auth_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && is_string($recu)
        && $attendu !== ''
        && hash_equals($attendu, $recu);
}

function auth_trouver_client(string $email): ?array
{
    $stmt = mysqli_prepare(
        db(),
        'SELECT id_client, nom, prenom, email, mot_de_passe
         FROM client
         WHERE email = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $resultat = mysqli_stmt_get_result($stmt);
    $client = mysqli_fetch_assoc($resultat);

    return $client ?: null;
}

function auth_trouver_fournisseur(string $email): ?array
{
    $stmt = mysqli_prepare(
        db(),
        'SELECT id_fournisseur, nom, email, mot_de_passe
         FROM fournisseur
         WHERE email = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $resultat = mysqli_stmt_get_result($stmt);
    $fournisseur = mysqli_fetch_assoc($resultat);

    return $fournisseur ?: null;
}
