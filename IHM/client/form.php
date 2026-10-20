<?php
/*
 * Formulaire commun ajout / modification.
 * Variables attendues : $old (valeurs), $errors (messages), $isEdit (bool), $cancelUrl.
 */
$field = static fn (string $name): string => e((string) ($old[$name] ?? ''));
$error = static function (string $name) use ($errors): string {
    return isset($errors[$name]) ? '<small class="field-error">' . e($errors[$name]) . '</small>' : '';
};
?>
<?php if (!empty($errors['general'])): ?>
    <p class="alert alert-error"><?= e($errors['general']) ?></p>
<?php endif; ?>

<form method="post" action="" class="form" novalidate>

    <label for="cin">CIN</label>
    <input type="text" id="cin" name="cin" maxlength="20" required value="<?= $field('cin') ?>">
    <?= $error('cin') ?>

    <label for="nom">Nom</label>
    <input type="text" id="nom" name="nom" maxlength="100" required value="<?= $field('nom') ?>">
    <?= $error('nom') ?>

    <label for="prenom">Prenom</label>
    <input type="text" id="prenom" name="prenom" maxlength="100" required value="<?= $field('prenom') ?>">
    <?= $error('prenom') ?>

    <label for="adresse">Adresse</label>
    <input type="text" id="adresse" name="adresse" maxlength="255" required value="<?= $field('adresse') ?>">
    <?= $error('adresse') ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" maxlength="150" required value="<?= $field('email') ?>">
    <?= $error('email') ?>

    <label for="telephone">Telephone (facultatif)</label>
    <input type="tel" id="telephone" name="telephone" maxlength="30" value="<?= $field('telephone') ?>">
    <?= $error('telephone') ?>

    <label for="mot_de_passe">
        Mot de passe<?= $isEdit ? ' (laisser vide pour ne pas le changer)' : '' ?>
    </label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8"
           autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
    <?= $error('mot_de_passe') ?>

    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a href="<?= e($cancelUrl) ?>">Annuler</a>
    </div>
</form>
