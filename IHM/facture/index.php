<?php
/** @var array $factures @var string|null $erreur @var string|null $succes */
$role = $_SESSION['role'] ?? null;
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= $role === 'client' ? 'Mes factures' : 'Factures' ?></h1>
        <p>Consultez les factures générées après validation des consommations.</p>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <?php if ($factures === []): ?>
        <p>Aucune facture enregistrée pour le moment.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Date</th>
                    <th>Période</th>
                    <?php if ($role === 'fournisseur'): ?>
                        <th>Client</th>
                    <?php endif; ?>
                    <th>Eau</th>
                    <th>Électricité</th>
                    <th>TTC</th>
                    <th>État</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($factures as $f): ?>
                <tr>
                    <td><?= e($f['numero']) ?></td>
                    <td><?= e(format_date_fr($f['date_facture'])) ?></td>
                    <td><?= (int) $f['periode_mois'] ?>/<?= (int) $f['periode_annee'] ?></td>
                    <?php if ($role === 'fournisseur'): ?>
                        <td>
                            <?= e($f['nom'] . ' ' . $f['prenom']) ?>
                            <small><?= e($f['cin']) ?></small>
                        </td>
                    <?php endif; ?>
                    <td><?= e(number_format((float) $f['conso_eau'], 2, ',', ' ')) ?> m³</td>
                    <td><?= e(number_format((float) $f['conso_elec'], 2, ',', ' ')) ?> kWh</td>
                    <td><?= e(number_format((float) $f['montant_ttc'], 2, ',', ' ')) ?> DH</td>
                    <td><span class="badge"><?= e($f['etat']) ?></span></td>
                    <td>
                        <a href="<?= e(url('Facture/one/' . (int) $f['id_facture'])) ?>">
                            Détail
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
