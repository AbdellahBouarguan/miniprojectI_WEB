<?php
/**
 * @var array<int, array<string, mixed>> $factures
 * @var string $type
 * @var string $description
 * @var int|null $idFacture
 * @var string|null $erreur
 * @var string $csrf
 */
$types = [
    'FUITE_EXTERNE' => 'Fuite externe',
    'FUITE_INTERNE' => 'Fuite interne',
    'FACTURE' => 'Facture',
    'AUTRE' => 'Autre',
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Nouvelle réclamation</h1>
        <p>Précisez le type et décrivez votre problème.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Reclamation/index')) ?>">Retour</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<section class="card">
    <form method="post" action="<?= e(url('Reclamation/add')) ?>" class="form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label for="type">Type de réclamation</label>
                <select id="type" name="type" required>
                    <?php foreach ($types as $code => $libelle): ?>
                        <option value="<?= e($code) ?>" <?= $code === $type ? 'selected' : '' ?>>
                            <?= e($libelle) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_facture">Facture concernée (si type Facture)</label>
                <select id="id_facture" name="id_facture">
                    <option value="">— Aucune —</option>
                    <?php foreach ($factures as $facture): ?>
                        <option
                            value="<?= (int) $facture['id_facture'] ?>"
                            <?= (int) $facture['id_facture'] === (int) $idFacture ? 'selected' : '' ?>
                        >
                            <?= e($facture['numero']) ?>
                            — <?= (int) $facture['periode_mois'] ?>/<?= (int) $facture['periode_annee'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" maxlength="2000" required><?= e($description) ?></textarea>
            <p class="field-help">Obligatoire, surtout pour le type « Autre ».</p>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Envoyer la réclamation</button>
        </div>
    </form>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
