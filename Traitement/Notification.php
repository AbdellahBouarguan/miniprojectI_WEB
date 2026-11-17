<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Notification.php';

/**
 * Retourne [$role, $idDestinataire] pour l'utilisateur connecté.
 *
 * @return array{0:string,1:int}
 */
function notification_utilisateur(): array
{
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, ['client', 'fournisseur', 'agent'], true)) {
        header(
            'Location: ' . url('Auth/login')
            . '?erreur=' . urlencode('Connectez-vous pour consulter vos notifications.')
        );
        exit;
    }

    $id = match ($role) {
        'client' => (int) ($_SESSION['id_client'] ?? 0),
        'fournisseur' => (int) ($_SESSION['id_fournisseur'] ?? 0),
        'agent' => (int) ($_SESSION['id_agent'] ?? 0),
        default => 0,
    };

    if ($id <= 0) {
        header('Location: ' . url('Auth/login'));
        exit;
    }

    return [$role, $id];
}

function notification_all($id = null): void
{
    [$role, $idDestinataire] = notification_utilisateur();

    $notifications = notification_lister($role, $idDestinataire);
    $nonLues = notification_compter_non_lues($role, $idDestinataire);
    $succes = $_GET['succes'] ?? null;
    $erreur = $_GET['erreur'] ?? null;

    require ROOT . 'IHM/notification/index.php';
}

function notification_lire($id = null): void
{
    [$role, $idDestinataire] = notification_utilisateur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        erreur_page(405, 'Méthode non autorisée', 'Cette action n’est pas autorisée.');
    }

    if (!notification_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $idNotification = (int) ($id ?? 0);

    if ($idNotification <= 0 || !notification_marquer_lue($idNotification, $role, $idDestinataire)) {
        header(
            'Location: ' . url('Notification/all')
            . '?erreur=' . urlencode('Notification introuvable ou déjà lue.')
        );
        exit;
    }

    header(
        'Location: ' . url('Notification/all')
        . '?succes=' . urlencode('Notification marquée comme lue.')
    );
    exit;
}

function notification_lire_tout($id = null): void
{
    [$role, $idDestinataire] = notification_utilisateur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        erreur_page(405, 'Méthode non autorisée', 'Cette action n’est pas autorisée.');
    }

    if (!notification_csrf_valide()) {
        erreur_page(419, 'Session expirée', 'Rechargez la page et réessayez.');
    }

    $nombre = notification_marquer_toutes_lues($role, $idDestinataire);

    header(
        'Location: ' . url('Notification/all')
        . '?succes=' . urlencode(
            $nombre > 0
                ? $nombre . ' notification(s) marquée(s) comme lue(s).'
                : 'Aucune notification non lue.'
        )
    );
    exit;
}

function notification_csrf_valide(): bool
{
    $attendu = $_SESSION['csrf_token'] ?? '';
    $recu = $_POST['csrf'] ?? '';

    return is_string($attendu)
        && $attendu !== ''
        && is_string($recu)
        && hash_equals($attendu, $recu);
}
