<?php
$field = static fn (string $name): string => e((string) ($old[$name] ?? ''));
$error = static fn (string $name): string => isset($errors[$name])
    ? '<span class="field-error">' . e($errors[$name]) . '</span>'
    : '';
?>

<?php if (!empty($errors['general'])): ?>
    <div class="message-error"><?= e($errors['general']) ?></div>
<?php endif; ?>

<form method="post" action="" class="form" novalidate>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

    <div class="form-grid">
        <div class="form-group">
            <label for="cin">CIN</label>
            <input type="text" id="cin" name="cin" maxlength="20" required value="<?= $field('cin') ?>">
            <?= $error('cin') ?>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" required value="<?= $field('email') ?>">
            <?= $error('email') ?>
        </div>

        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" maxlength="100" required value="<?= $field('nom') ?>">
            <?= $error('nom') ?>
        </div>

        <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" maxlength="100" required value="<?= $field('prenom') ?>">
            <?= $error('prenom') ?>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" maxlength="255" required value="<?= $field('adresse') ?>">
            <?= $error('adresse') ?>
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone (facultatif)</label>
            <input type="tel" id="telephone" name="telephone" maxlength="30" value="<?= $field('telephone') ?>">
            <?= $error('telephone') ?>
        </div>

        <div class="form-group">
            <label for="mot_de_passe">
                Mot de passe<?= $isEdit ? ' (laisser vide pour ne pas le changer)' : '' ?>
            </label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8"
                   autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
            <?= $error('mot_de_passe') ?>
        </div>

        <div class="form-group">
            <label for="mot_de_passe_confirmation">Confirmer le mot de passe</label>
            <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation"
                   minlength="8" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
            <?= $error('mot_de_passe_confirmation') ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a class="button button-secondary" href="<?= e($cancelUrl) ?>">Annuler</a>
    </div>
</form>
