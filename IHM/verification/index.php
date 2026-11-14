<?php
/**
 * @var array $lignes
 * @var int[] $annees
 * @var int $annee
 * @var array{fichier: bool, importees: int, rejets: string[]} $rapport
 * @var string $csrf
 * @var string|null $erreur
 * @var string|null $succes
 */

$libelles = [
    'CONFORME'    => ['Conforme', 'v-ok'],
    'ECART'       => ['Écart à vérifier', 'v-ko'],
    'INCOMPLET'   => ['Saisie incomplète', 'v-warn'],
    'SANS_RELEVE' => ['Relevé agent absent', 'v-na'],
];

$decisions = [
    'A_VERIFIER' => 'À vérifier',
    'VALIDEE'    => 'Validée',
    'REJETEE'    => 'Rejetée',
];

$nombreOuTiret = static fn(?float $v): string => $v === null ? '—' : format_nombre($v);
$ecartOuTiret = static fn(?float $v): string => $v === null ? '—' : format_nombre($v, 1) . ' %';
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<style>
    .v-badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .8rem; font-weight: 600; }
    .v-ok   { background: #dcfce7; color: #166534; }
    .v-ko   { background: #fee2e2; color: #991b1b; }
    .v-warn { background: #fef3c7; color: #92400e; }
    .v-na   { background: #e2e8f0; color: #475569; }
    .v-actions { display: flex; gap: .4rem; margin: 0; }
    .v-btn { border: 1px solid #cbd5e1; border-radius: .5rem; padding: .3rem .7rem; font-size: .8rem; cursor: pointer; background: #fff; }
    .v-btn-ok { color: #166534; border-color: #86efac; }
    .v-btn-ko { color: #991b1b; border-color: #fca5a5; }
    .v-filtre { display: flex; align-items: center; gap: .6rem; margin: 0; }
    .v-filtre select { padding: .4rem .7rem; border-radius: .5rem; border: 1px solid #cbd5e1; }
</style>

<div class="page-head">
    <div>
        <h1>Vérification annuelle</h1>
        <p>
            Comparez la consommation saisie par les clients avec le relevé annuel de l'agent
            (tolérance : <?= e(format_nombre(Verification::TOLERANCE_POURCENT, 0)) ?> %).
        </p>
    </div>
    <form method="get" action="<?= e(url('Verification/index')) ?>" class="v-filtre">
        <label for="annee">Année</label>
        <select id="annee" name="annee" onchange="this.form.submit()">
            <?php foreach ($annees as $a): ?>
                <option value="<?= (int) $a ?>" <?= (int) $a === $annee ? 'selected' : '' ?>><?= (int) $a ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<?php if ($rapport['fichier']): ?>
    <div class="message-success">
        Fichier de l'agent lu : <?= (int) $rapport['importees'] ?> ligne(s) importée(s).
    </div>
    <?php if ($rapport['rejets'] !== []): ?>
        <div class="message-error">
            <?= count($rapport['rejets']) ?> ligne(s) rejetée(s) :
            <?= e(implode(' ', array_slice($rapport['rejets'], 0, 5))) ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state" style="margin-bottom: 1rem;">
        Aucun fichier <strong><?= e(Verification::FICHIER_AGENT) ?></strong> trouvé :
        les relevés de l'agent ne sont pas encore importés.
    </div>
<?php endif; ?>

<section class="card">
    <div class="invoice-head" style="margin-bottom: 1rem;">
        <h2 style="font-size: 1rem;">Consommations <?= (int) $annee ?></h2>
        <span class="badge badge-danger"><?= count($lignes) ?></span>
    </div>

    <?php if ($lignes === []): ?>
        <div class="empty-state">Aucun client à vérifier pour cette année.</div>
    <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Mois saisis</th>
                    <th>Eau saisie (m³)</th>
                    <th>Eau agent (m³)</th>
                    <th>Écart eau</th>
                    <th>Élec. saisie (kWh)</th>
                    <th>Élec. agent (kWh)</th>
                    <th>Écart élec.</th>
                    <th>Résultat</th>
                    <th>Décision</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lignes as $l): ?>
                <?php [$texte, $classe] = $libelles[$l['resultat']]; ?>
                <tr>
                    <td>
                        <div class="client-cell">
                            <strong><?= e($l['prenom'] . ' ' . $l['nom']) ?></strong>
                            <small><?= e($l['cin']) ?></small>
                        </div>
                    </td>
                    <td><?= (int) $l['nb_mois'] ?> / <?= (int) Verification::MOIS_ATTENDUS ?></td>
                    <td><?= e($nombreOuTiret($l['saisi_eau'])) ?></td>
                    <td><?= e($nombreOuTiret($l['agent_eau'])) ?></td>
                    <td><?= e($ecartOuTiret($l['ecart_eau'])) ?></td>
                    <td><?= e($nombreOuTiret($l['saisi_elec'])) ?></td>
                    <td><?= e($nombreOuTiret($l['agent_elec'])) ?></td>
                    <td><?= e($ecartOuTiret($l['ecart_elec'])) ?></td>
                    <td><span class="v-badge <?= e($classe) ?>"><?= e($texte) ?></span></td>
                    <td>
                        <?php if ($l['agent_present']): ?>
                            <div style="margin-bottom: .4rem;">
                                <?= e($decisions[$l['statut_verification']] ?? '—') ?>
                                <?php if (!empty($l['date_verification'])): ?>
                                    <small>(<?= e(format_date_fr($l['date_verification'])) ?>)</small>
                                <?php endif; ?>
                            </div>
                            <form method="post"
                                  action="<?= e(url('Verification/decider/' . (int) $l['id_client'])) ?>"
                                  class="v-actions">
                                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="annee" value="<?= (int) $annee ?>">
                                <button type="submit" name="decision" value="VALIDEE" class="v-btn v-btn-ok">Valider</button>
                                <button type="submit" name="decision" value="REJETEE" class="v-btn v-btn-ko">Rejeter</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>