<?php
/** @var array $conso @var array $calcul @var array $photos @var array $anomalies
 *  @var array|null $facture @var string|null $erreur @var string|null $succes
 */
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <h1>Consommation <?= (int) $conso['periode_mois'] ?>/<?= (int) $conso['periode_annee'] ?></h1>

    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger"><?= e($erreur) ?></div>
    <?php endif; ?>

    <?php if (!empty($succes)): ?>
        <div class="alert alert-success"><?= e($succes) ?></div>
    <?php endif; ?>

    <table class="table">
        <tr>
            <th>Index eau saisi</th>
            <td><?= e(number_format((float) $conso['index_eau'], 2, ',', ' ')) ?> m³</td>
        </tr>
        <tr>
            <th>Index électricité saisi</th>
            <td><?= e(number_format((float) $conso['index_elec'], 2, ',', ' ')) ?> kWh</td>
        </tr>
        <tr>
            <th>Consommation eau calculée</th>
            <td><?= e(number_format((float) $calcul['eau'], 2, ',', ' ')) ?> m³</td>
        </tr>
        <tr>
            <th>Consommation électricité calculée</th>
            <td><?= e(number_format((float) $calcul['elec'], 2, ',', ' ')) ?> kWh</td>
        </tr>
        <tr>
            <th>Statut</th>
            <td><?= e($conso['statut']) ?></td>
        </tr>
    </table>

    <?php if (isset($photos['EAU'], $photos['ELEC'])): ?>
        <div class="row">
            <div class="col">
                <p>Compteur eau</p>
                <img src="<?= e(url($photos['EAU'])) ?>" alt="Compteur eau"
                     style="max-width:300px">
            </div>
            <div class="col">
                <p>Compteur électricité</p>
                <img src="<?= e(url($photos['ELEC'])) ?>" alt="Compteur électricité"
                     style="max-width:300px">
            </div>
        </div>
    <?php endif; ?>

    <?php if ($anomalies !== []): ?>
        <h2>Anomalies</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Champ</th>
                    <th>Valeur</th>
                    <th>Référence</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($anomalies as $a): ?>
                <tr>
                    <td><?= e($a['type_anomalie']) ?></td>
                    <td><?= e($a['champ']) ?></td>
                    <td><?= e(number_format((float) $a['valeur_saisie'], 2, ',', ' ')) ?></td>
                    <td><?= e(number_format((float) $a['valeur_reference'], 2, ',', ' ')) ?></td>
                    <td><?= e($a['statut']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($facture !== null): ?>
        <h2>Facture</h2>
        <p>
            Numéro :
            <a href="<?= e(url('Facture/one/' . (int) $facture['id_facture'])) ?>">
                <?= e($facture['numero']) ?>
            </a>
            —
            État : <?= e($facture['etat']) ?> —
            Montant TTC : <?= e(number_format((float) $facture['montant_ttc'], 2, ',', ' ')) ?> DH
        </p>
    <?php elseif ($conso['statut'] === 'VALIDEE'): ?>
        <form method="post" action="<?= e(url('Consommation/facturer')) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id_conso" value="<?= (int) $conso['id_conso'] ?>">
            <button type="submit" class="btn btn-primary">Générer la facture</button>
        </form>
    <?php endif; ?>

    <a href="<?= e(url('Consommation/all')) ?>">Retour à l’historique</a>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>