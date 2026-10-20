<?php
/**
 * @var array $clients
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
    <?php if ($clients === []): ?>
        <p>Aucun client enregistré pour ce fournisseur.</p>
    <?php else: ?>
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
                        <a href="<?= e(url('Client/one/' . (int) $c['id_client'])) ?>">
                            Détail
                        </a>
                        |
                        <a href="<?= e(url('Client/update/' . (int) $c['id_client'])) ?>">
                            Modifier
                        </a>

                        <?php if ((int) $c['nb_conso'] === 0): ?>
                            |
                            <form
                                method="post"
                                action="<?= e(url('Client/delete/' . (int) $c['id_client'])) ?>"
                                style="display:inline"
                                onsubmit="return confirm('Supprimer ce client ?');"
                            >
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <button class="button button-small button-danger" type="submit">
                                    Supprimer
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="muted" title="Ce client possède des consommations">
                                (suppression bloquée)
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
