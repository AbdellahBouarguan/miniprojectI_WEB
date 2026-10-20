<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <div class="card-head">
        <h1><?= e($title) ?></h1>
        <?php if (($_SESSION['role'] ?? null) === 'fournisseur'): ?>
            <a class="button" href="<?= url('Client/update/' . (int) $client['id_client']) ?>">Modifier</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($flash)): ?>
        <p class="alert alert-success"><?= e($flash) ?></p>
    <?php endif; ?>

    <dl class="details">
        <dt>CIN</dt>
        <dd><?= e($client['cin']) ?></dd>

        <dt>Nom</dt>
        <dd><?= e($client['nom']) ?></dd>

        <dt>Prenom</dt>
        <dd><?= e($client['prenom']) ?></dd>

        <dt>Adresse</dt>
        <dd><?= e($client['adresse']) ?></dd>

        <dt>Email</dt>
        <dd><?= e($client['email']) ?></dd>

        <dt>Telephone</dt>
        <dd><?= e($client['telephone'] ?? '-') ?></dd>

        <dt>Client depuis</dt>
        <dd><?= e($client['date_creation']) ?></dd>
    </dl>

    <?php if (($_SESSION['role'] ?? null) === 'fournisseur'): ?>
        <p><a href="<?= url('Client/all') ?>">&larr; Retour a la liste</a></p>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
