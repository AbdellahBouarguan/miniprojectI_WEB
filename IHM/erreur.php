<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($titreErreur ?? 'Erreur') ?></h1>
        <p><?= e($messageErreur ?? 'Une erreur est survenue.') ?></p>
    </div>
</div>

<section class="card">
    <p>
        <a class="button" href="<?= e(url('')) ?>">Retour à l'accueil</a>
        <a class="button button-secondary" href="javascript:history.back()">Retour</a>
    </p>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
