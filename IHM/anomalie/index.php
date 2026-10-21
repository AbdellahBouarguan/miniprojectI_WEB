<?php
/**
 * @var array $anomalies
 * @var string|null $erreur
 * @var string|null $succes
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Traitement des anomalies de saisie</h1>
        <p>Une correction est requise avant la génération de la facture.</p>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <div class="invoice-head" style="margin-bottom: 1rem;">
        <h2 style="font-size: 1rem;">Relevés signalés</h2>
        <span class="badge badge-danger"><?= count($anomalies) ?></span>
    </div>

    <?php if ($anomalies === []): ?>
        <div class="empty-state">
            Aucune anomalie en attente de traitement.
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Anomalie</th>
                    <th>Client</th>
                    <th>Fluide</th>
                    <th>Type</th>
                    <th>Index saisi</th>
                    <th>Index préc.</th>
                    <th>Seuil</th>
                    <th>Détectée le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($anomalies as $a): ?>
                <tr>
                    <td>
                        ANO-<?= str_pad((string) $a['id_anomalie'], 4, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td>
                        <div class="client-cell">
                            <strong><?= e($a['prenom'] . ' ' . $a['nom']) ?></strong>
                            <small><?= e($a['cin']) ?></small>
                        </div>
                    </td>
                    <td><?= $a['champ'] === 'EAU' ? 'Eau' : 'Électricité' ?></td>
                    <td>
                        <span class="type-badge <?= $a['type_anomalie'] === 'INDEX_INFERIEUR' ? 'type-danger' : 'type-warning' ?>">
                            <?= e($a['type_anomalie']) ?>
                        </span>
                    </td>
                    <td><?= e(format_nombre((float) $a['index_saisi'])) ?></td>
                    <td>
                        <?= $a['index_precedent'] !== null
                            ? e(format_nombre((float) $a['index_precedent']))
                            : '—' ?>
                    </td>
                    <td><?= e(format_nombre((float) $a['seuil'], 0)) ?></td>
                    <td><?= e(format_date_fr($a['date_detection'])) ?></td>
                    <td>
                        <a
                            class="btn btn-outline btn-small"
                            href="<?= e(url('Anomalie/corriger/' . (int) $a['id_anomalie'])) ?>"
                        >
                            Corriger
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
