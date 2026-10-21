<?php
/**
 * @var array $client
 * @var int $nbConsommations
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($client['prenom'] . ' ' . $client['nom']) ?></h1>
        <p>Fiche client et suivi des consommations.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= e(url('Client/update/' . (int) $client['id_client'])) ?>">
            Modifier ce client
        </a>
        <a class="btn btn-secondary" href="<?= e(url('Client/all')) ?>">
            Retour à la liste
        </a>
    </div>
</div>

<section class="card">
    <table class="table">
        <tr>
            <th>CIN</th>
            <td><?= e($client['cin']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= e($client['email']) ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?= e($client['telephone'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td><?= e($client['adresse']) ?></td>
        </tr>
        <tr>
            <th>Fournisseur</th>
            <td><?= e($client['fournisseur_nom'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Date de création</th>
            <td><?= e($client['date_creation']) ?></td>
        </tr>
        <tr>
            <th>Consommations enregistrées</th>
            <td><?= (int) $nbConsommations ?></td>
        </tr>
    </table>

</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
