<?php
/** @var array $erreurs @var array $anciennes @var array|null $conso @var array|null $precedent @var string $csrf */

$mois = (int) ($anciennes['periode_mois'] ?? $conso['periode_mois'] ?? date('n'));
$annee = (int) ($anciennes['periode_annee'] ?? $conso['periode_annee'] ?? date('Y'));

$valeur = static function (string $cle, string $defaut = '') use ($anciennes): string {
    $v = $anciennes[$cle] ?? $defaut;
    return is_scalar($v) ? (string) $v : $defaut;
};
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <h1>Saisie de la consommation</h1>

    <?php if ($erreurs !== []): ?>
        <div class="alert alert-danger">
            <strong>La saisie n’a pas été acceptée :</strong>
            <ul>
                <?php foreach ($erreurs as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($precedent !== null): ?>
        <div class="alert alert-info">
            Dernier relevé validé (<?= (int) $precedent['periode_mois'] ?>/<?= (int) $precedent['periode_annee'] ?>) :
            eau <?= e(number_format((float) $precedent['index_eau'], 2, ',', ' ')) ?> m³ —
            électricité <?= e(number_format((float) $precedent['index_elec'], 2, ',', ' ')) ?> kWh.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('Consommation/add')) ?>"
          enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <?php if ($conso !== null): ?>
            <input type="hidden" name="id_conso" value="<?= (int) $conso['id_conso'] ?>">
            <input type="hidden" name="periode_mois" value="<?= $mois ?>">
            <input type="hidden" name="periode_annee" value="<?= $annee ?>">
            <p><strong>Correction de la période :</strong> <?= $mois ?>/<?= $annee ?></p>
        <?php else: ?>
            <div class="mb-3">
                <label for="periode_mois">Mois</label>
                <select id="periode_mois" name="periode_mois" class="form-control">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m === $mois ? 'selected' : '' ?>>
                            <?= $m ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="periode_annee">Année</label>
                <input type="number" id="periode_annee" name="periode_annee"
                       class="form-control" min="2000" max="2100"
                       value="<?= $annee ?>" required>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="index_eau">Index compteur eau (m³)</label>
            <input type="number" id="index_eau" name="index_eau"
                   class="form-control" min="0" step="0.01"
                   value="<?= e($valeur('index_eau')) ?>" required>
            <?php if (isset($erreurs['index_eau'])): ?>
                <div class="text-danger"><?= e($erreurs['index_eau']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="photo_eau">Photo du compteur d’eau</label>
            <input type="file" id="photo_eau" name="photo_eau" class="form-control"
                   accept="image/jpeg,image/png,image/webp" required>
            <?php if (isset($erreurs['photo_eau'])): ?>
                <div class="text-danger"><?= e($erreurs['photo_eau']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="index_elec">Index compteur électricité (kWh)</label>
            <input type="number" id="index_elec" name="index_elec"
                   class="form-control" min="0" step="0.01"
                   value="<?= e($valeur('index_elec')) ?>" required>
            <?php if (isset($erreurs['index_elec'])): ?>
                <div class="text-danger"><?= e($erreurs['index_elec']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="photo_elec">Photo du compteur électrique</label>
            <input type="file" id="photo_elec" name="photo_elec" class="form-control"
                   accept="image/jpeg,image/png,image/webp" required>
            <?php if (isset($erreurs['photo_elec'])): ?>
                <div class="text-danger"><?= e($erreurs['photo_elec']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer</button>
        <a href="<?= e(url('Consommation/all')) ?>" class="btn btn-link">Historique</a>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>