<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Quartier.php';

function quartier_exiger_fournisseur(): int
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

function quartier_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}

function quartier_all($id = null): void
{
    quartier_exiger_fournisseur();

    $quartiers = quartier_lister();
    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;
    $nomQuartier = trim((string) ($_GET['nom'] ?? ''));
    $typeQuartier = strtoupper(trim((string) ($_GET['type'] ?? 'RESIDENTIEL')));
    $csrf = csrf_token();

    require ROOT . 'IHM/quartier/index.php';
}

function quartier_add($id = null): void
{
    quartier_exiger_fournisseur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Quartier/all'));
        exit;
    }

    if (!quartier_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $nom = trim((string) ($_POST['nom'] ?? ''));
    $type = strtoupper(trim((string) ($_POST['type_activite'] ?? '')));
    $erreurs = [];

    if ($nom === '' || strlen($nom) > 100) {
        $erreurs[] = 'Le nom du quartier est obligatoire (100 caractères maximum).';
    }

    if (!in_array($type, QuartierBD::TYPES, true)) {
        $erreurs[] = 'Type d’activité invalide.';
    }

    if ($erreurs === [] && quartier_bd()->existe($nom, $type)) {
        $erreurs[] = 'Ce quartier existe déjà pour ce type d’activité.';
    }

    if ($erreurs !== []) {
        header(
            'Location: ' . url('Quartier/all')
            . '?erreur=' . urlencode(implode(' ', $erreurs))
            . '&nom=' . urlencode($nom)
            . '&type=' . urlencode($type)
        );
        exit;
    }

    quartier_creer($nom, $type);

    header(
        'Location: ' . url('Quartier/all')
        . '?succes=' . urlencode('Quartier ajouté avec succès.')
    );
    exit;
}

function quartier_index($id = null): void
{
    quartier_all($id);
}
