<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Facture.php';
require_once __DIR__ . '/../BD/ConsommationBD.php';
require_once __DIR__ . '/../BD/Notification.php';

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
    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;
    $csrf = csrf_token();

    require ROOT . 'IHM/facture/show.php';
}

function facture_show($id = null): void
{
    facture_one($id);
}

function facture_payer($id = null): void
{
    if (($_SESSION['role'] ?? null) !== 'fournisseur') {
        header('Location: ' . url('Auth/login'));
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        return;
    }

    $idFacture = (int) ($id ?? 0);
    $idFournisseur = (int) ($_SESSION['id_fournisseur'] ?? 0);
    $csrf = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($csrf)
        || $csrf === ''
        || !is_string($_POST['csrf'] ?? null)
        || !hash_equals($csrf, (string) $_POST['csrf'])
    ) {
        $query = '?erreur=' . urlencode('Session expirée. Rechargez la page.');
    } else {
        $bd = new FactureBD(pdo());
        $facture = $bd->findForFournisseur($idFacture, $idFournisseur);

        if ($facture === null || $facture['etat'] !== 'IMPAYEE') {
            $query = '?erreur=' . urlencode('Facture introuvable ou déjà payée.');
        } elseif ($bd->payerParFournisseur($idFacture, $idFournisseur)) {
            try {
                notification_creer(
                    'client',
                    (int) $facture['id_client'],
                    'PAIEMENT',
                    'Paiement enregistré',
                    'Votre facture ' . $facture['numero'] . ' a été réglée.',
                    'Facture/one/' . $idFacture
                );
            } catch (Throwable $e) {
                error_log('[NOTIFICATION PAIEMENT] ' . $e->getMessage());
            }

            $query = '?succes=' . urlencode('La facture a été payée intégralement.');
        } else {
            $query = '?erreur=' . urlencode('Impossible d’enregistrer le paiement.');
        }
    }

    header('Location: ' . url('Facture/one/' . $idFacture) . $query);
    exit;
}
