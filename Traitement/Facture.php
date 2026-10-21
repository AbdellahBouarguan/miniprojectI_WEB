<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Facture.php';
require_once __DIR__ . '/../BD/ConsommationBD.php';

function facture_exiger_utilisateur(): string
{
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, ['client', 'fournisseur'], true)) {
        header('Location: ' . url('Auth/login'));
        exit;
    }

    return $role;
}

function facture_all($id = null): void
{
    $role = facture_exiger_utilisateur();
    $bd = new FactureBD(pdo());

    $factures = $role === 'client'
        ? $bd->listByClient((int) $_SESSION['id_client'])
        : $bd->listByFournisseur((int) $_SESSION['id_fournisseur']);

    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;

    require ROOT . 'IHM/facture/index.php';
}

function facture_one($id = null): void
{
    $role = facture_exiger_utilisateur();
    $idFacture = (int) ($id ?? 0);
    $bd = new FactureBD(pdo());

    $facture = $role === 'client'
        ? $bd->findForClient($idFacture, (int) $_SESSION['id_client'])
        : $bd->findForFournisseur($idFacture, (int) $_SESSION['id_fournisseur']);

    if ($facture === null) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        return;
    }

    $photos = (new ConsommationBD(pdo()))->photosDe((int) $facture['id_conso']);

    require ROOT . 'IHM/facture/show.php';
}

function facture_show($id = null): void
{
    facture_one($id);
}
