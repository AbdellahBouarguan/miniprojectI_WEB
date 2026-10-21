<?php
/** @var array $facture @var array $photos */
$role = $_SESSION['role'] ?? null;
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Facture <?= e($facture['numero']) ?></h1>
        <p>
            Période <?= (int) $facture['periode_mois'] ?>/<?= (int) $facture['periode_annee'] ?>
            — émise le <?= e(format_date_fr($facture['date_facture'])) ?>
        </p>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= e(url('Facture/all')) ?>">Retour aux factures</a>
    </div>
</div>

<section class="card">
    <h2>Client</h2>
    <table class="table">
        <tr>
            <th>CIN</th>
            <td><?= e($facture['cin']) ?></td>
        </tr>
        <tr>
            <th>Nom</th>
            <td><?= e($facture['nom'] . ' ' . $facture['prenom']) ?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td><?= e($facture['adresse']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= e($facture['email']) ?></td>
        </tr>
        <tr>
            <th>État</th>
            <td><span class="badge"><?= e($facture['etat']) ?></span></td>
        </tr>
    </table>
</section>

<section class="card">
    <h2>Consommations et montants</h2>
    <table class="table">
        <tr>
            <th>Consommation eau</th>
            <td><?= e(number_format((float) $facture['conso_eau'], 2, ',', ' ')) ?> m³</td>
        </tr>
        <tr>
            <th>Consommation électricité</th>
            <td><?= e(number_format((float) $facture['conso_elec'], 2, ',', ' ')) ?> kWh</td>
        </tr>
        <tr>
            <th>Montant HT</th>
            <td><?= e(number_format((float) $facture['montant_ht'], 2, ',', ' ')) ?> DH</td>
        </tr>
        <tr>
            <th>TVA (<?= e(number_format((float) $facture['taux_tva'], 2, ',', ' ')) ?> %)</th>
            <td><?= e(number_format((float) $facture['montant_tva'], 2, ',', ' ')) ?> DH</td>
        </tr>
        <tr>
            <th>Montant TTC</th>
            <td><strong><?= e(number_format((float) $facture['montant_ttc'], 2, ',', ' ')) ?> DH</strong></td>
        </tr>
    </table>
</section>

<?php if ($photos !== []): ?>
    <section class="card">
        <h2>Photos des compteurs</h2>
        <div class="row">
            <?php foreach (['EAU' => 'Compteur eau', 'ELEC' => 'Compteur électricité'] as $type => $label): ?>
                <?php if (isset($photos[$type])): ?>
                    <div class="col">
                        <p><?= e($label) ?></p>
                        <img src="<?= e(url($photos[$type])) ?>" alt="<?= e($label) ?>"
                             style="max-width:320px">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
