<?php
/**
 * @var array<int, array<string, mixed>> $contrats
 * @var string|null $erreur
 * @var string|null $succes
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Contrats</h1>
        <p>Un client peut posséder plusieurs contrats dans des quartiers différents.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Quartier/all')) ?>">Gérer les quartiers</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <?php if ($contrats === []): ?>
        <div class="empty-state">Aucun contrat enregistré.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Quartier</th>
                        <th>Activité</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($contrats as $contrat): ?>
                    <tr>
                        <td class="nowrap"><?= e($contrat['numero_contrat']) ?></td>
                        <td>
                            <a href="<?= e(url('Client/one/' . (int) $contrat['id_client'])) ?>">
                                <?= e($contrat['prenom'] . ' ' . $contrat['nom']) ?>
                            </a>
                            <small><?= e($contrat['cin']) ?></small>
                        </td>
                        <td><?= e($contrat['quartier_nom']) ?></td>
                        <td><?= e($contrat['type_activite']) ?></td>
                        <td><?= e(format_date_fr($contrat['date_debut'])) ?></td>
                        <td><?= e(format_date_fr($contrat['date_fin'])) ?></td>
                        <td>
                            <span class="badge <?= $contrat['statut'] === 'ACTIF' ? 'badge-open' : 'badge-danger' ?>">
                                <?= e($contrat['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($contrat['statut'] === 'ACTIF'): ?>
                                <form method="post"
                                      action="<?= e(url('Contrat/resilier/' . (int) $contrat['id_contrat'])) ?>"
                                      class="notification-action-form">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <button class="button button-secondary" type="submit">Résilier</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
