<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Reclamation.php';
require_once __DIR__ . '/../BD/Notification.php';

function reclamation_utilisateur(): array
{
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, ['client', 'fournisseur'], true)) {
        header(
            'Location: ' . url('Auth/login')
            . '?erreur=' . urlencode('Connectez-vous pour accéder aux réclamations.')
        );
        exit;
    }

    $id = $role === 'client'
        ? (int) ($_SESSION['id_client'] ?? 0)
        : (int) ($_SESSION['id_fournisseur'] ?? 0);

    if ($id <= 0) {
        header('Location: ' . url('Auth/login'));
        exit;
    }

    return [$role, $id];
}

function reclamation_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}

function reclamation_index($id = null): void
{
    [$role, $idUtilisateur] = reclamation_utilisateur();
    $bd = reclamation_bd();

    if ($role === 'client') {
        $reclamations = $bd->listerParClient($idUtilisateur);
        $csrf = csrf_token();
        require ROOT . 'IHM/reclamation/index.php';
        return;
    }

    $reclamations = $bd->listerParFournisseur($idUtilisateur);
    $csrf = csrf_token();
    require ROOT . 'IHM/reclamation/index.php';
}

function reclamation_add($id = null): void
{
    [$role, $idClient] = reclamation_utilisateur();

    if ($role !== 'client') {
        header('Location: ' . url('Reclamation/index'));
        exit;
    }

    $bd = reclamation_bd();
    $factures = $bd->facturesClient($idClient);
    $erreur = null;
    $type = 'FUITE_EXTERNE';
    $description = '';
    $idFacture = null;

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!reclamation_csrf_valide()) {
            erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
        }

        $type = strtoupper(trim((string) ($_POST['type'] ?? '')));
        $description = trim((string) ($_POST['description'] ?? ''));
        $idFacture = (int) ($_POST['id_facture'] ?? 0) ?: null;

        if (!in_array($type, ReclamationBD::TYPES, true)) {
            $erreur = 'Type de réclamation invalide.';
        } elseif ($description === '') {
            $erreur = 'La description est obligatoire.';
        } elseif ($type === 'FACTURE' && $idFacture === null) {
            $erreur = 'Sélectionnez la facture concernée.';
        } elseif ($idFacture !== null) {
            $valides = array_column($factures, 'id_facture');
            if (!in_array($idFacture, $valides, true)) {
                $erreur = 'Facture invalide.';
            }
        }

        if ($erreur === null) {
            $idReclamation = $bd->creer($idClient, $type, $description, $type === 'FACTURE' ? $idFacture : null);

            try {
                $idFournisseur = $bd->fournisseurDuClient($idClient);

                if ($idFournisseur !== null) {
                    notification_creer(
                        'fournisseur',
                        $idFournisseur,
                        'RECLAMATION',
                        'Nouvelle réclamation',
                        'Une nouvelle réclamation a été déposée par un client.',
                        'Reclamation/index'
                    );
                }
            } catch (Throwable $e) {
                error_log('[NOTIFICATION RECLAMATION] ' . $e->getMessage());
            }

            header(
                'Location: ' . url('Reclamation/one/' . $idReclamation)
                . '?succes=' . urlencode('Réclamation enregistrée.')
            );
            exit;
        }
    }

    $csrf = csrf_token();
    require ROOT . 'IHM/reclamation/add.php';
}

function reclamation_one($id = null): void
{
    [$role, $idUtilisateur] = reclamation_utilisateur();
    $idReclamation = (int) ($id ?? 0);
    $bd = reclamation_bd();

    $reclamation = $role === 'client'
        ? $bd->trouverPourClient($idReclamation, $idUtilisateur)
        : $bd->trouverPourFournisseur($idReclamation, $idUtilisateur);

    if ($reclamation === null) {
        http_response_code(404);
        require ROOT . 'IHM/404.php';
        return;
    }

    $erreur = $_GET['erreur'] ?? null;
    $succes = $_GET['succes'] ?? null;
    $csrf = csrf_token();

    require ROOT . 'IHM/reclamation/one.php';
}

function reclamation_traiter($id = null): void
{
    [$role, $idFournisseur] = reclamation_utilisateur();

    if ($role !== 'fournisseur' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . url('Reclamation/index'));
        exit;
    }

    if (!reclamation_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $idReclamation = (int) ($id ?? 0);
    $statut = strtoupper(trim((string) ($_POST['statut'] ?? '')));
    $reponse = trim((string) ($_POST['reponse'] ?? ''));

    if (!in_array($statut, ReclamationBD::STATUTS, true) || $reponse === '') {
        header(
            'Location: ' . url('Reclamation/one/' . $idReclamation)
            . '?erreur=' . urlencode('Statut ou réponse invalide.')
        );
        exit;
    }

    $bd = reclamation_bd();
    $reclamation = $bd->trouverPourFournisseur($idReclamation, $idFournisseur);

    if ($reclamation === null || !$bd->traiter($idReclamation, $idFournisseur, $statut, $reponse)) {
        header(
            'Location: ' . url('Reclamation/one/' . $idReclamation)
            . '?erreur=' . urlencode('Impossible de traiter cette réclamation.')
        );
        exit;
    }

    try {
        notification_creer(
            'client',
            (int) $reclamation['id_client'],
            'RECLAMATION',
            'Réclamation mise à jour',
            'Votre réclamation est maintenant : ' . reclamation_statut_label($statut) . '.',
            'Reclamation/one/' . $idReclamation
        );
    } catch (Throwable $e) {
        error_log('[NOTIFICATION RECLAMATION] ' . $e->getMessage());
    }

    header(
        'Location: ' . url('Reclamation/one/' . $idReclamation)
        . '?succes=' . urlencode('Réclamation mise à jour.')
    );
    exit;
}
