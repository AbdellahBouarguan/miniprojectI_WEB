<?php
/**
 * @var array $clients
 * @var string $search
 * @var string|null $erreur
 * @var string|null $succes
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Gestion des clients</h1>
        <p>Gérez les abonnés de votre régie eau et électricité.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= e(url('Client/add')) ?>">Ajouter un client</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <form method="get" action="<?= e(url('Client/all')) ?>" class="search">
        <input type="search" name="q" placeholder="Rechercher (CIN, nom, prénom, email)"
               value="<?= e($search) ?>">
        <button type="submit" class="button">Rechercher</button>
        <?php if ($search !== ''): ?>
            <a href="<?= e(url('Client/all')) ?>">Effacer</a>
        <?php endif; ?>
    </form>

    <?php if ($clients === []): ?>
        <p>Aucun client enregistré pour ce fournisseur.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>CIN</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Consommations</th>
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
                    <td><?= e($c['telephone'] ?? '—') ?></td>
                    <td><?= (int) $c['nb_conso'] ?></td>
                    <td>
                        <div class="actions">
                            <a class="button button-small button-secondary"
                               href="<?= e(url('Client/one/' . (int) $c['id_client'])) ?>">
                                Détail
                            </a>
                            <a class="button button-small"
                               href="<?= e(url('Client/update/' . (int) $c['id_client'])) ?>">
                                Modifier
                            </a>

                            <?php if ((int) $c['nb_conso'] === 0): ?>
                                <form
                                    method="post"
                                    action="<?= e(url('Client/delete/' . (int) $c['id_client'])) ?>"
                                    onsubmit="return confirm('Supprimer ce client ?');"
                                >
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <button class="button button-small button-danger" type="submit">
                                        Supprimer
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="muted" title="Ce client possède des consommations">
                                    Suppression bloquée
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
