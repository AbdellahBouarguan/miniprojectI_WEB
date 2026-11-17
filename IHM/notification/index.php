<?php
/**
 * @var array<int, array<string, mixed>> $notifications
 * @var int $nonLues
 * @var string|null $succes
 * @var string|null $erreur
 */

$libellesType = [
    'FACTURE' => 'Facture',
    'ANOMALIE' => 'Anomalie',
    'RECLAMATION' => 'Réclamation',
    'PAIEMENT' => 'Paiement',
    'VERIFICATION' => 'Vérification',
    'SYSTEME' => 'Système',
];

$classesType = [
    'FACTURE' => 'type-info',
    'ANOMALIE' => 'type-warning',
    'RECLAMATION' => 'type-danger',
    'PAIEMENT' => 'type-success',
    'VERIFICATION' => 'type-info',
    'SYSTEME' => 'type-neutral',
];

$iconesType = [
    'FACTURE' => 'file',
    'ANOMALIE' => 'alert',
    'RECLAMATION' => 'message',
    'PAIEMENT' => 'card',
    'VERIFICATION' => 'check',
    'SYSTEME' => 'bell',
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Notifications</h1>
        <p>
            <?= $nonLues > 0
                ? $nonLues . ' notification' . ($nonLues > 1 ? 's' : '') . ' non lue' . ($nonLues > 1 ? 's' : '')
                : 'Toutes vos notifications sont lues.' ?>
        </p>
    </div>
    <div class="page-head-actions">
        <?php if ($nonLues > 0): ?>
            <form method="post" action="<?= e(url('Notification/lire_tout')) ?>" class="notification-action-form">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button class="button button-secondary" type="submit">Tout marquer comme lu</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <?php if ($notifications === []): ?>
        <div class="empty-state">
            Aucune notification pour le moment.
        </div>
    <?php else: ?>
        <div class="notification-list">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $estNonLue = (int) ($notification['lu'] ?? 0) === 0;
                $type = (string) ($notification['type'] ?? 'SYSTEME');
                $lien = notification_lien_url($notification['lien'] ?? null);
                $dateCreation = strtotime((string) ($notification['date_creation'] ?? ''));
                $dateLabel = $dateCreation !== false ? date('d/m/Y H:i', $dateCreation) : '—';
                ?>
                <article class="notification-item<?= $estNonLue ? ' is-unread' : '' ?>">
                    <div class="notification-icon">
                        <?= icon($iconesType[$type] ?? 'bell') ?>
                    </div>
                    <div class="notification-body">
                        <div class="notification-top">
                            <span class="type-badge <?= e($classesType[$type] ?? 'type-neutral') ?>">
                                <?= e($libellesType[$type] ?? 'Système') ?>
                            </span>
                            <?php if ($estNonLue): ?>
                                <span class="badge badge-warning">Non lue</span>
                            <?php endif; ?>
                            <time datetime="<?= e((string) ($notification['date_creation'] ?? '')) ?>">
                                <?= e($dateLabel) ?>
                            </time>
                        </div>
                        <h2><?= e((string) ($notification['titre'] ?? '')) ?></h2>
                        <p><?= e((string) ($notification['message'] ?? '')) ?></p>
                        <div class="notification-actions">
                            <?php if ($lien !== null): ?>
                                <a class="button button-secondary" href="<?= e($lien) ?>">Ouvrir</a>
                            <?php endif; ?>
                            <?php if ($estNonLue): ?>
                                <form method="post" action="<?= e(url('Notification/lire/' . (int) $notification['id_notification'])) ?>">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <button class="button button-secondary" type="submit">Marquer comme lue</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
