<?php
/** @var array $erreurs @var array $anciennes @var array|null $conso @var array|null $precedent
 *  @var array $photosPrecedentes @var string $csrf
 */
$mois = (int) ($mois ?? ($anciennes['periode_mois'] ?? $conso['periode_mois'] ?? date('n')));
$annee = (int) ($annee ?? ($anciennes['periode_annee'] ?? $conso['periode_annee'] ?? date('Y')));

$valeur = static function (string $cle) use ($anciennes): string {
    $v = $anciennes[$cle] ?? '';
    return is_scalar($v) ? (string) $v : '';
};

$erreurChamp = static function (string $cle) use ($erreurs): string {
    return isset($erreurs[$cle])
        ? '<div class="meter-error">' . e($erreurs[$cle]) . '</div>'
        : '';
};

$compteurs = [
    'EAU' => [
        'titre' => 'Compteur Eau',
        'unite' => 'm³',
        'index' => 'index_eau',
        'photo' => 'photo_eau',
        'icone' => 'W',
        'classeIcone' => 'photo-icon',
        'precedent' => $precedent['index_eau'] ?? null,
    ],
    'ELEC' => [
        'titre' => 'Compteur Électricité',
        'unite' => 'kWh',
        'index' => 'index_elec',
        'photo' => 'photo_elec',
        'icone' => 'E',
        'classeIcone' => 'photo-icon photo-icon-elec',
        'precedent' => $precedent['index_elec'] ?? null,
    ],
];
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= $conso !== null ? 'Corriger ma consommation' : 'Saisir ma consommation' ?></h1>
        <p>Transmettez vos relevés mensuels avant la fin du mois.</p>
    </div>
</div>

<?php if (!empty($erreurs['general']) || !empty($erreurs['periode'])): ?>
    <div class="message-error">
        <strong>La saisie n’a pas été acceptée :</strong>
        <ul>
            <?php if (!empty($erreurs['general'])): ?>
                <li><?= e($erreurs['general']) ?></li>
            <?php endif; ?>
            <?php if (!empty($erreurs['periode'])): ?>
                <li><?= e($erreurs['periode']) ?></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($conso === null): ?>
    <div class="period-banner">
        <div>
            <strong>Période de saisie ouverte — <?= e(mois_francais($mois)) ?> <?= $annee ?></strong>
            <span>Ouverte du 18 du mois jusqu’à la fin du mois.</span>
        </div>
        <span class="badge badge-open">Ouverte</span>
    </div>
<?php else: ?>
    <div class="period-banner">
        <div>
            <strong>Correction de la période : <?= $mois ?>/<?= $annee ?></strong>
            <span>La période ne peut pas être modifiée.</span>
        </div>
        <span class="badge badge-warning">Correction</span>
    </div>
<?php endif; ?>

<div class="message-warning">
    L’index doit être supérieur ou égal au relevé précédent. Un dépassement du seuil du fournisseur
    déclenche une confirmation. Joignez une photo nette de chaque compteur.
</div>

<form method="post" action="<?= e(url('Consommation/add')) ?>"
      enctype="multipart/form-data" class="form" novalidate>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

    <?php if ($conso !== null): ?>
        <input type="hidden" name="id_conso" value="<?= (int) $conso['id_conso'] ?>">
        <input type="hidden" name="periode_mois" value="<?= $mois ?>">
        <input type="hidden" name="periode_annee" value="<?= $annee ?>">
    <?php else: ?>
        <section class="card">
            <div class="form-grid">
                <div class="form-group">
                    <label for="periode_mois">Mois</label>
                    <select id="periode_mois" name="periode_mois">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m === $mois ? 'selected' : '' ?>>
                                <?= e(mois_francais($m)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periode_annee">Année</label>
                    <input type="number" id="periode_annee" name="periode_annee"
                           min="2000" max="2100" value="<?= $annee ?>" required>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="meter-grid">
        <?php foreach ($compteurs as $type => $compteur): ?>
            <section class="meter-card">
                <div class="meter-head">
                    <h2><?= e($compteur['titre']) ?></h2>
                    <span class="meter-ref">Relevé mensuel</span>
                </div>

                <label class="meter-label" for="<?= e($compteur['index']) ?>">
                    Nouvel index constaté (<?= e($compteur['unite']) ?>)
                </label>
                <input
                    class="meter-input"
                    type="number"
                    id="<?= e($compteur['index']) ?>"
                    name="<?= e($compteur['index']) ?>"
                    min="0"
                    step="0.01"
                    value="<?= e($valeur($compteur['index'])) ?>"
                    required
                >
                <?= $erreurChamp($compteur['index']) ?>

                <p class="meter-help">
                    <?php if ($compteur['precedent'] !== null): ?>
                        Index précédent : <?= e(format_nombre((float) $compteur['precedent'])) ?> <?= e($compteur['unite']) ?>
                    <?php else: ?>
                        Aucun relevé précédent.
                    <?php endif; ?>
                </p>

                <?php if (isset($photosPrecedentes[$type])): ?>
                    <p class="meter-label">Photo du mois précédent</p>
                    <img
                        class="photo-preview show"
                        src="<?= e(url($photosPrecedentes[$type])) ?>"
                        alt="Photo du compteur <?= e($type) ?> du mois précédent"
                    >
                <?php endif; ?>

                <label class="photo-drop">
                    <input
                        type="file"
                        name="<?= e($compteur['photo']) ?>"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <span class="<?= e($compteur['classeIcone']) ?>"><?= e($compteur['icone']) ?></span>
                    <strong>Ajouter une photo du compteur</strong>
                    <small>JPG, PNG ou WEBP — 5 Mo maximum</small>
                </label>
                <?= $erreurChamp($compteur['photo']) ?>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="button">Envoyer</button>
        <a class="button button-secondary" href="<?= e(url('Consommation/all')) ?>">Annuler</a>
    </div>
</form>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
