<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/ClientBD.php';
require_once __DIR__ . '/../BD/Contrat.php';
require_once __DIR__ . '/../BD/Quartier.php';

function contrat_exiger_fournisseur(): int
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

function contrat_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}

function contrat_all($id = null): void
{
    $idFournisseur = contrat_exiger_fournisseur();
    $contrats = contrat_lister_par_fournisseur($idFournisseur);
    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;

    require ROOT . 'IHM/contrat/index.php';
}

function contrat_add($id = null): void
{
    $idFournisseur = contrat_exiger_fournisseur();
    $idClient = (int) ($id ?? 0);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Client/one/' . $idClient));
        exit;
    }

    if (!contrat_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $client = (new ClientBD(db()))->trouver($idClient, $idFournisseur);

    if ($client === null) {
        header(
            'Location: ' . url('Client/all')
            . '?erreur=' . urlencode('Client introuvable.')
        );
        exit;
    }

    $numero = trim((string) ($_POST['numero_contrat'] ?? ''));
    $idQuartier = (int) ($_POST['id_quartier'] ?? 0);
    $dateDebut = trim((string) ($_POST['date_debut'] ?? ''));
    $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
    $erreurs = [];

    if ($numero === '' || strlen($numero) > 50) {
        $erreurs[] = 'Le numéro de contrat est obligatoire (50 caractères maximum).';
    } elseif (contrat_bd()->numeroExiste($numero)) {
        $erreurs[] = 'Ce numéro de contrat existe déjà.';
    }

    if (quartier_trouver($idQuartier) === null) {
        $erreurs[] = 'Quartier invalide.';
    }

    $dateDebutValide = DateTimeImmutable::createFromFormat('!Y-m-d', $dateDebut);
    if (!$dateDebutValide instanceof DateTimeImmutable) {
        $erreurs[] = 'Date de début invalide.';
    }

    $dateFinValide = null;
    if ($dateFin !== '') {
        $dateFinValide = DateTimeImmutable::createFromFormat('!Y-m-d', $dateFin);

        if (!$dateFinValide instanceof DateTimeImmutable) {
            $erreurs[] = 'Date de fin invalide.';
        } elseif (
            $dateDebutValide instanceof DateTimeImmutable
            && $dateFinValide < $dateDebutValide
        ) {
            $erreurs[] = 'La date de fin doit être postérieure à la date de début.';
        }
    }

    if ($erreurs !== []) {
        header(
            'Location: ' . url('Client/one/' . $idClient)
            . '?erreur=' . urlencode(implode(' ', $erreurs))
        );
        exit;
    }

    contrat_bd()->creer(
        $idClient,
        $idQuartier,
        $numero,
        $dateDebut,
        $dateFin !== '' ? $dateFin : null
    );

    header(
        'Location: ' . url('Client/one/' . $idClient)
        . '?succes=' . urlencode('Contrat ajouté avec succès.')
    );
    exit;
}

function contrat_resilier($id = null): void
{
    $idFournisseur = contrat_exiger_fournisseur();
    $idContrat = (int) ($id ?? 0);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Contrat/all'));
        exit;
    }

    if (!contrat_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $contrat = contrat_bd()->trouver($idContrat, $idFournisseur);

    if ($contrat === null) {
        header(
            'Location: ' . url('Contrat/all')
            . '?erreur=' . urlencode('Contrat introuvable.')
        );
        exit;
    }

    $ok = contrat_bd()->resilier($idContrat, $idFournisseur);

    header(
        'Location: ' . url('Client/one/' . (int) $contrat['id_client'])
        . ($ok
            ? '?succes=' . urlencode('Contrat résilié.')
            : '?erreur=' . urlencode('Impossible de résilier ce contrat.'))
    );
    exit;
}

function contrat_index($id = null): void
{
    contrat_all($id);
}
