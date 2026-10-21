<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($title ?? 'Ajouter un client') ?></h1>
        <p>Renseignez les informations du nouvel abonné.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Client/all')) ?>">Retour à la liste</a>
    </div>
</div>

<section class="card">
    <?php require __DIR__ . '/form.php'; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
