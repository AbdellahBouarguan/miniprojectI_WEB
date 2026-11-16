<?php
declare(strict_types=1);

/**
 * Notifications internes.
 *
 * Le destinataire est identifie par un couple (role, id) afin de couvrir
 * les clients, les fournisseurs et, plus tard, les agents.
 */
final class NotificationBD
{
    private const ROLES = ['client', 'fournisseur', 'agent'];
    private const TYPES = ['FACTURE', 'ANOMALIE', 'RECLAMATION', 'PAIEMENT', 'VERIFICATION', 'SYSTEME'];

    public function __construct(private PDO $pdo) {}

    public function creer(
        string $role,
        int $idDestinataire,
        string $type,
        string $titre,
        string $message,
        ?string $lien = null
    ): int {
        $role = strtolower(trim($role));
        $type = strtoupper(trim($type));

        if (!in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Rôle de notification invalide.');
        }

        if ($idDestinataire <= 0) {
            throw new InvalidArgumentException('Destinataire de notification invalide.');
        }

        if (!in_array($type, self::TYPES, true)) {
            $type = 'SYSTEME';
        }

        $titre = trim($titre);
        $message = trim($message);

        if ($titre === '' || $message === '') {
            throw new InvalidArgumentException('Titre ou message de notification vide.');
        }

        $titre = self::limiter($titre, 150);
        $message = self::limiter($message, 500);
        $lien = self::normaliserLien($lien);

        $stmt = $this->pdo->prepare(
            'INSERT INTO notification
                (destinataire_role, id_destinataire, type, titre, message, lien)
             VALUES
                (:role, :destinataire, :type, :titre, :message, :lien)'
        );
        $stmt->execute([
            'role' => $role,
            'destinataire' => $idDestinataire,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'lien' => $lien,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function lister(string $role, int $idDestinataire, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));

        $stmt = $this->pdo->prepare(
            'SELECT id_notification, destinataire_role, id_destinataire,
                    type, titre, message, lien, lu, date_creation, date_lecture
             FROM notification
             WHERE destinataire_role = :role
               AND id_destinataire = :destinataire
             ORDER BY date_creation DESC, id_notification DESC
             LIMIT :limite'
        );
        $stmt->bindValue(':role', strtolower(trim($role)), PDO::PARAM_STR);
        $stmt->bindValue(':destinataire', $idDestinataire, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function compterNonLues(string $role, int $idDestinataire): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM notification
             WHERE destinataire_role = :role
               AND id_destinataire = :destinataire
               AND lu = 0'
        );
        $stmt->execute([
            'role' => strtolower(trim($role)),
            'destinataire' => $idDestinataire,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function marquerLue(int $idNotification, string $role, int $idDestinataire): bool
    {
        if ($idNotification <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE notification
             SET lu = 1, date_lecture = NOW()
             WHERE id_notification = :id
               AND destinataire_role = :role
               AND id_destinataire = :destinataire
               AND lu = 0'
        );
        $stmt->execute([
            'id' => $idNotification,
            'role' => strtolower(trim($role)),
            'destinataire' => $idDestinataire,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function marquerToutesLues(string $role, int $idDestinataire): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notification
             SET lu = 1, date_lecture = NOW()
             WHERE destinataire_role = :role
               AND id_destinataire = :destinataire
               AND lu = 0'
        );
        $stmt->execute([
            'role' => strtolower(trim($role)),
            'destinataire' => $idDestinataire,
        ]);

        return $stmt->rowCount();
    }

    private static function limiter(string $texte, int $longueur): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($texte, 0, $longueur, 'UTF-8');
        }

        return substr($texte, 0, $longueur);
    }

    private static function normaliserLien(?string $lien): ?string
    {
        if ($lien === null) {
            return null;
        }

        $lien = trim($lien);

        if ($lien === '' || strlen($lien) > 255) {
            return null;
        }

        if (
            preg_match('#^[a-z][a-z0-9+.-]*://#i', $lien)
            || str_starts_with($lien, '//')
            || str_contains($lien, '..')
        ) {
            return null;
        }

        if (!preg_match('#^[A-Za-z0-9_/?=&.%\-]+$#', $lien)) {
            return null;
        }

        return ltrim($lien, '/');
    }
}

function notification_bd(): NotificationBD
{
    return new NotificationBD(pdo());
}

function notification_creer(
    string $role,
    int $idDestinataire,
    string $type,
    string $titre,
    string $message,
    ?string $lien = null
): int {
    return notification_bd()->creer($role, $idDestinataire, $type, $titre, $message, $lien);
}

/** @return array<int, array<string, mixed>> */
function notification_lister(string $role, int $idDestinataire, int $limit = 50): array
{
    return notification_bd()->lister($role, $idDestinataire, $limit);
}

function notification_compter_non_lues(string $role, int $idDestinataire): int
{
    return notification_bd()->compterNonLues($role, $idDestinataire);
}

function notification_marquer_lue(int $idNotification, string $role, int $idDestinataire): bool
{
    return notification_bd()->marquerLue($idNotification, $role, $idDestinataire);
}

function notification_marquer_toutes_lues(string $role, int $idDestinataire): int
{
    return notification_bd()->marquerToutesLues($role, $idDestinataire);
}

function notification_lien_valide(?string $lien): bool
{
    if ($lien === null) {
        return false;
    }

    $lien = trim($lien);

    if ($lien === '' || strlen($lien) > 255) {
        return false;
    }

    if (
        preg_match('#^[a-z][a-z0-9+.-]*://#i', $lien)
        || str_starts_with($lien, '//')
        || str_contains($lien, '..')
    ) {
        return false;
    }

    return (bool) preg_match('#^[A-Za-z0-9_/?=&.%\-]+$#', $lien);
}

function notification_lien_url(?string $lien): ?string
{
    return notification_lien_valide($lien) ? url(ltrim((string) $lien, '/')) : null;
}
