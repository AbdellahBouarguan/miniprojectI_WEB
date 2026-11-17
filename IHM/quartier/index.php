<?php
/**
 * @var array<int, array<string, mixed>> $quartiers
 * @var string|null $erreur
 * @var string|null $succes
 * @var string $csrf
 */
$types = [
    'RESIDENTIEL' => 'Résidentiel',
    'COMMERCIAL' => 'Commercial',
    'INDUSTRIEL' => 'Industriel',
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Quartiers</h1>
        <p>Chaque quartier est associé à un type d’activité.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Contrat/all')) ?>">Voir les contrats</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <h2>Ajouter un quartier</h2>
    <form method="post" action="<?= e(url('Quartier/add')) ?>" class="form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="nom">Nom du quartier</label>
                <input type="text" id="nom" name="nom" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="type_activite">Type d’activité</label>
                <select id="type_activite" name="type_activite" required>
                    <?php foreach ($types as $code => $libelle): ?>
                        <option value="<?= e($code) ?>"><?= e($libelle) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Ajouter le quartier</button>
        </div>
    </form>
</section>

<section class="card">
    <h2>Quartiers enregistrés</h2>
    <?php if ($quartiers === []): ?>
        <div class="empty-state">Aucun quartier enregistré.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type d’activité</th>
                        <th>Date de création</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($quartiers as $quartier): ?>
                    <tr>
                        <td><?= e($quartier['nom']) ?></td>
                        <td><?= e($types[$quartier['type_activite']] ?? $quartier['type_activite']) ?></td>
                        <td><?= e(format_date_fr($quartier['date_creation'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
