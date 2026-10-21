<?php
/**
 * @var array $anomalie
 * @var array $erreurs
 * @var string $valeurSaisie
 * @var string $csrf
 */
$unite = $anomalie['champ'] === 'EAU' ? 'm³' : 'kWh';
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Corriger l’anomalie ANO-<?= str_pad((string) $anomalie['id_anomalie'], 4, '0', STR_PAD_LEFT) ?></h1>
        <p>Vérifiez l’index avec le client avant d’enregistrer la correction.</p>
    </div>
</div>

<?php if (!empty($erreurs['general'])): ?>
    <div class="message-error"><?= e($erreurs['general']) ?></div>
<?php endif; ?>

<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Client</div>
        <div class="stat-value" style="font-size: 1.1rem;">
            <?= e($anomalie['prenom'] . ' ' . $anomalie['nom']) ?>
        </div>
        <div class="stat-note"><?= e($anomalie['cin']) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Fluide / Type</div>
        <div class="stat-value" style="font-size: 1.1rem;">
            <?= $anomalie['champ'] === 'EAU' ? 'Eau' : 'Électricité' ?>
        </div>
        <div class="stat-note"><?= e($anomalie['type_anomalie']) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Index précédent / Seuil</div>
        <div class="stat-value" style="font-size: 1.1rem;">
            <?= $anomalie['index_precedent'] !== null
                ? e(format_nombre((float) $anomalie['index_precedent']))
                : '—' ?>
            <?= e($unite) ?>
        </div>
        <div class="stat-note">
            Seuil : <?= e(format_nombre((float) $anomalie['seuil'], 0)) ?> <?= e($unite) ?>
        </div>
    </div>
</div>

<section class="card">
    <h2 style="font-size: 1rem; margin-top: 0;">Nouvel index constaté (<?= e($unite) ?>)</h2>

    <form method="post" action="<?= e(url('Anomalie/corriger/' . (int) $anomalie['id_anomalie'])) ?>">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="form-group" style="max-width: 320px;">
            <label for="valeur_corrigee">Index corrigé</label>
            <input
                type="number"
                id="valeur_corrigee"
                name="valeur_corrigee"
                min="0"
                step="0.01"
                value="<?= e($valeurSaisie) ?>"
                required
            >
            <?php if (!empty($erreurs['valeur_corrigee'])): ?>
                <span class="field-error"><?= e($erreurs['valeur_corrigee']) ?></span>
            <?php endif; ?>
        </div>

        <p class="muted" style="font-size: 0.8rem;">
            L’index corrigé doit être supérieur ou égal à l’index précédent.
            L’anomalie sera clôturée et la consommation pourra être facturée.
        </p>

        <div class="page-head-actions">
            <button class="btn btn-primary" type="submit">
                Enregistrer la correction
            </button>
            <a class="btn btn-secondary" href="<?= e(url('Anomalie/index')) ?>">
                Annuler
            </a>
        </div>
    </form>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
