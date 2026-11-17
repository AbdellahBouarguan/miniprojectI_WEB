<?php
/**
 * @var array<int, array<string, mixed>> $reclamations
 * @var string $csrf
 */
$role = $_SESSION['role'] ?? null;
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Réclamations</h1>
        <p>
            <?= $role === 'client'
                ? 'Déposez et suivez vos réclamations.'
                : 'Consultez et traitez les réclamations de vos clients.' ?>
        </p>
    </div>
    <?php if ($role === 'client'): ?>
        <div class="page-head-actions">
            <a class="button" href="<?= e(url('Reclamation/add')) ?>">Nouvelle réclamation</a>
        </div>
    <?php endif; ?>
</div>

<section class="card">
    <?php if ($reclamations === []): ?>
        <div class="empty-state">Aucune réclamation.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <?php if ($role === 'fournisseur'): ?><th>Client</th><?php endif; ?>
                        <th>Type</th>
                        <th>Facture</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reclamations as $reclamation): ?>
                    <tr>
                        <td class="nowrap"><?= e($reclamation['numero']) ?></td>
                        <td><?= e(format_date_fr($reclamation['date_creation'])) ?></td>
                        <?php if ($role === 'fournisseur'): ?>
                            <td>
                                <div class="client-cell">
                                    <strong><?= e($reclamation['prenom'] . ' ' . $reclamation['nom']) ?></strong>
                                    <small><?= e($reclamation['cin']) ?></small>
                                </div>
                            </td>
                        <?php endif; ?>
                        <td><?= e(reclamation_type_label($reclamation['type'])) ?></td>
                        <td><?= e($reclamation['facture_numero'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= e(reclamation_statut_badge($reclamation['statut'])) ?>">
                                <?= e(reclamation_statut_label($reclamation['statut'])) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= e(url('Reclamation/one/' . (int) $reclamation['id_reclamation'])) ?>">
                                <?= $role === 'fournisseur' ? 'Traiter' : 'Détail' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
