<?php
/**
 * @var array $reclamation
 * @var string|null $erreur
 * @var string|null $succes
 * @var string $csrf
 */
$role = $_SESSION['role'] ?? null;
$statuts = [
    'EN_COURS' => 'En cours',
    'RESOLUE' => 'Résolue',
    'REJETEE' => 'Rejetée',
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Réclamation <?= e($reclamation['numero']) ?></h1>
        <p>Déposée le <?= e(format_date_fr($reclamation['date_creation'])) ?>.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Reclamation/index')) ?>">Retour</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <table class="table">
        <?php if ($role === 'fournisseur'): ?>
            <tr>
                <th>Client</th>
                <td><?= e($reclamation['prenom'] . ' ' . $reclamation['nom']) ?> (<?= e($reclamation['cin']) ?>)</td>
            </tr>
        <?php endif; ?>
        <tr>
            <th>Type</th>
            <td><?= e(reclamation_type_label($reclamation['type'])) ?></td>
        </tr>
        <tr>
            <th>Facture</th>
            <td><?= e($reclamation['facture_numero'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Statut</th>
            <td>
                <span class="badge <?= e(reclamation_statut_badge($reclamation['statut'])) ?>">
                    <?= e(reclamation_statut_label($reclamation['statut'])) ?>
                </span>
            </td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?= nl2br(e($reclamation['description'])) ?></td>
        </tr>
        <?php if (!empty($reclamation['reponse'])): ?>
            <tr>
                <th>Réponse du fournisseur</th>
                <td><?= nl2br(e($reclamation['reponse'])) ?></td>
            </tr>
        <?php endif; ?>
    </table>
</section>

<?php if ($role === 'fournisseur'): ?>
<section class="card">
    <h2>Traiter la réclamation</h2>
    <form method="post" action="<?= e(url('Reclamation/traiter/' . (int) $reclamation['id_reclamation'])) ?>" class="form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="form-group">
            <label for="statut">Statut</label>
            <select id="statut" name="statut" required>
                <?php foreach ($statuts as $code => $libelle): ?>
                    <option value="<?= e($code) ?>" <?= $code === $reclamation['statut'] ? 'selected' : '' ?>>
                        <?= e($libelle) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="reponse">Réponse au client</label>
            <textarea id="reponse" name="reponse" rows="5" maxlength="2000" required><?= e($reclamation['reponse'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Enregistrer le traitement</button>
        </div>
    </form>
</section>
<?php endif; ?>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
