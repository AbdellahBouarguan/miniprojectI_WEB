<?php
/** @var array $conso @var array $calcul @var array $photos @var array $anomalies
 *  @var array|null $facture @var string|null $erreur @var string|null $succes
 */
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Consommation <?= (int) $conso['periode_mois'] ?>/<?= (int) $conso['periode_annee'] ?></h1>
        <p>Détail du relevé et de la facture associée.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-secondary" href="<?= e(url('Consommation/all')) ?>">Retour à l’historique</a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <div class="table-wrap">
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
                <td>
                    <span class="badge <?= e(statut_badge($conso['statut'])) ?>">
                        <?= e(statut_label($conso['statut'])) ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</section>

<?php if (isset($photos['EAU'], $photos['ELEC'])): ?>
    <section class="card">
        <h2>Photos des compteurs</h2>
        <div class="photo-grid">
            <div>
                <p>Compteur eau</p>
                <img src="<?= e(url($photos['EAU'])) ?>" alt="Compteur eau">
            </div>
            <div>
                <p>Compteur électricité</p>
                <img src="<?= e(url($photos['ELEC'])) ?>" alt="Compteur électricité">
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($anomalies !== []): ?>
    <section class="card">
        <h2>Anomalies</h2>
        <div class="table-wrap">
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
                        <td><?= e(statut_label($a['type_anomalie'])) ?></td>
                        <td><?= e($a['champ']) ?></td>
                        <td><?= e(number_format((float) $a['valeur_saisie'], 2, ',', ' ')) ?></td>
                        <td><?= e(number_format((float) $a['valeur_reference'], 2, ',', ' ')) ?></td>
                        <td>
                            <span class="badge <?= e(statut_badge($a['statut'])) ?>">
                                <?= e(statut_label($a['statut'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Facture</h2>
    <?php if ($facture !== null): ?>
        <p>
            Numéro :
            <a href="<?= e(url('Facture/one/' . (int) $facture['id_facture'])) ?>">
                <?= e($facture['numero']) ?>
            </a>
            —
            État :
            <span class="badge <?= e(statut_badge($facture['etat'])) ?>">
                <?= e(statut_label($facture['etat'])) ?>
            </span>
            —
            Montant TTC : <?= e(number_format((float) $facture['montant_ttc'], 2, ',', ' ')) ?> DH
        </p>
    <?php elseif ($conso['statut'] === 'VALIDEE'): ?>
        <form method="post" action="<?= e(url('Consommation/facturer')) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id_conso" value="<?= (int) $conso['id_conso'] ?>">
            <button type="submit" class="button">Générer la facture</button>
        </form>
    <?php else: ?>
        <p class="muted">Aucune facture disponible pour cette consommation.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
