<?php
/**
 * @var array $conso
 * @var array $anomalies
 * @var bool $indexInferieur
 * @var string|null $erreur
 */

$libelles = [
    'INDEX_INFERIEUR' => 'Index inférieur au relevé précédent',
    'SEUIL_DEPASSE' => 'Seuil du fournisseur dépassé',
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Vérification nécessaire</h1>
        <p>Votre relevé présente une anomalie qui nécessite votre confirmation.</p>
    </div>
</div>

<section class="card">
    <p>
        Consommation de
        <strong><?= (int) $conso['periode_mois'] ?>/<?= (int) $conso['periode_annee'] ?></strong>.
    </p>

    <?php if (!empty($erreur)): ?>
        <div class="message-error"><?= e($erreur) ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Champ</th>
                <th>Anomalie</th>
                <th>Valeur concernée</th>
                <th>Référence</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($anomalies as $a): ?>
            <tr>
                <td><?= e($a['champ']) ?></td>
                <td><?= e($libelles[$a['type_anomalie']] ?? $a['type_anomalie']) ?></td>
                <td><?= e(number_format((float) $a['valeur_saisie'], 2, ',', ' ')) ?></td>
                <td><?= e(number_format((float) $a['valeur_reference'], 2, ',', ' ')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($indexInferieur): ?>
        <div class="message-warning">
            Un index inférieur au relevé précédent ne peut pas être confirmé.
            Choisissez « Corriger » pour saisir la bonne valeur.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('Consommation/confirmer')) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id_conso" value="<?= (int) $conso['id_conso'] ?>">

        <button
            type="submit"
            name="decision"
            value="confirmer"
            class="button"
            <?= $indexInferieur ? 'disabled' : '' ?>
        >
            Confirmer ces valeurs
        </button>

        <button type="submit" name="decision" value="corriger" class="button button-secondary">
            Corriger
        </button>
    </form>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
