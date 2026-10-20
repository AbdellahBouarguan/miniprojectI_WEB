<?php
/**
 * @var array $consommations
 * @var string|null $erreur
 * @var string|null $succes
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Mes consommations</h1>
        <p>Historique de vos relevés et de leur statut.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= e(url('Consommation/add')) ?>">
            Nouvelle saisie
        </a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <?php if ($consommations === []): ?>
        <p>Aucune consommation enregistrée pour le moment.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Période</th>
                    <th>Index eau</th>
                    <th>Index élec</th>
                    <th>Statut</th>
                    <th>Facture</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($consommations as $c): ?>
                <tr>
                    <td><?= (int) $c['periode_mois'] ?>/<?= (int) $c['periode_annee'] ?></td>
                    <td><?= e(number_format((float) $c['index_eau'], 2, ',', ' ')) ?></td>
                    <td><?= e(number_format((float) $c['index_elec'], 2, ',', ' ')) ?></td>
                    <td><span class="badge"><?= e($c['statut']) ?></span></td>
                    <td><?= !empty($c['facture_numero']) ? e($c['facture_numero']) : '—' ?></td>
                    <td>
                        <a href="<?= e(url('Consommation/one/' . (int) $c['id_conso'])) ?>">
                            Détail
                        </a>

                        <?php if ($c['statut'] === 'ANOMALIE'): ?>
                            |
                            <a href="<?= e(url('Consommation/confirmation/' . (int) $c['id_conso'])) ?>">
                                Confirmer / corriger
                            </a>
                        <?php elseif ($c['statut'] === 'EN_ATTENTE'): ?>
                            |
                            <a href="<?= e(url('Consommation/add/' . (int) $c['id_conso'])) ?>">
                                Reprendre
                            </a>
                        <?php elseif ($c['statut'] === 'VALIDEE'): ?>
                            |
                            <form
                                method="post"
                                action="<?= e(url('Consommation/facturer')) ?>"
                                style="display:inline"
                            >
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input
                                    type="hidden"
                                    name="id_conso"
                                    value="<?= (int) $c['id_conso'] ?>"
                                >
                                <button class="button button-small" type="submit">
                                    Générer la facture
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
