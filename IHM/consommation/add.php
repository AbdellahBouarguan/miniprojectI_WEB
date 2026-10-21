<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <h1>Saisie des consommations</h1>

    <p>
        Période :
        <?= e((string) ($mois ?? date('n'))) ?>
        /
        <?= e((string) ($annee ?? date('Y'))) ?>
    </p>

    <?php if (!empty($erreurs)): ?>
        <div class="message-error">
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= e($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($ancien) && $ancien !== null): ?>
        <h2>Relevé du mois précédent</h2>

        <div class="grid">
            <div>
                <h3>Compteur d'eau</h3>
                <p>
                    Ancien index :
                    <?= e((string) $ancien['consommation']['index_eau']) ?>
                    m³
                </p>

                <?php if (!empty($ancien['photos']['EAU'])): ?>
                    <img
                        class="meter-photo"
                        src="<?= e(url($ancien['photos']['EAU'])) ?>"
                        alt="Ancienne photo du compteur d'eau"
                    >
                <?php endif; ?>
            </div>

            <div>
                <h3>Compteur électrique</h3>
                <p>
                    Ancien index :
                    <?= e((string) $ancien['consommation']['index_elec']) ?>
                    kWh
                </p>

                <?php if (!empty($ancien['photos']['ELEC'])): ?>
                    <img
                        class="meter-photo"
                        src="<?= e(url($ancien['photos']['ELEC'])) ?>"
                        alt="Ancienne photo du compteur électrique"
                    >
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>
            Aucun relevé du mois précédent n'est disponible.
        </p>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Nouveaux relevés</h2>

    <form
        method="post"
        action="<?= e(url('Consommation/add')) ?>"
        enctype="multipart/form-data"
    >
        <div class="grid">
            <div class="form-group">
                <label for="index_eau">
                    Nouvel index d'eau (m³)
                </label>

                <input
                    type="number"
                    id="index_eau"
                    name="index_eau"
                    min="0"
                    step="0.01"
                    required
                    value="<?= e((string) ($_POST['index_eau'] ?? '')) ?>"
                >
            </div>

            <div class="form-group">
                <label for="photo_eau">
                    Photo du compteur d'eau
                </label>

                <input
                    type="file"
                    id="photo_eau"
                    name="photo_eau"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >
            </div>

            <div class="form-group">
                <label for="index_elec">
                    Nouvel index électrique (kWh)
                </label>

                <input
                    type="number"
                    id="index_elec"
                    name="index_elec"
                    min="0"
                    step="0.01"
                    required
                    value="<?= e((string) ($_POST['index_elec'] ?? '')) ?>"
                >
            </div>

            <div class="form-group">
                <label for="photo_elec">
                    Photo du compteur électrique
                </label>

                <input
                    type="file"
                    id="photo_elec"
                    name="photo_elec"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >
            </div>
        </div>

        <button class="button" type="submit">
            Vérifier ma saisie
        </button>
    </form>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>