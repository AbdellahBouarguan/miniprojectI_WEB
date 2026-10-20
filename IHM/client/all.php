<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <div class="card-head">
        <h1><?= e($title) ?></h1>
        <a class="button" href="<?= url('Client/add') ?>">+ Ajouter un client</a>
    </div>

    <?php if (!empty($flash)): ?>
        <p class="alert alert-success"><?= e($flash) ?></p>
    <?php endif; ?>

    <form method="get" action="<?= url('Client/all') ?>" class="search">
        <input type="search" name="q" placeholder="Rechercher (CIN, nom, prenom, email)"
               value="<?= e($search) ?>">
        <button type="submit" class="button">Rechercher</button>
        <?php if ($search !== ''): ?>
            <a href="<?= url('Client/all') ?>">Effacer</a>
        <?php endif; ?>
    </form>

    <?php if ($clients === []): ?>
        <p>Aucun client trouve.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Nom</th>
                        <th>Prenom</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><?= e($c['cin']) ?></td>
                        <td><?= e($c['nom']) ?></td>
                        <td><?= e($c['prenom']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><?= e($c['telephone']) ?></td>
                        <td class="actions">
                            <a href="<?= url('Client/one/' . (int) $c['id_client']) ?>">Voir</a>
                            <a href="<?= url('Client/update/' . (int) $c['id_client']) ?>">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="muted"><?= count($clients) ?> client(s)</p>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
