<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($title ?? 'Modifier un client') ?></h1>
        <p>Mettez à jour les informations de l'abonné.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Client/one/' . (int) $client['id_client'])) ?>">Retour à la fiche</a>
    </div>
</div>

<section class="card">
    <?php require __DIR__ . '/form.php'; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
