<?php
/**
 * @var string|null $erreur
 * @var string $email
 */
$email = $email ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b2a4a">
    <title>Connexion — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=20261020">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <?= brand_mark() ?>
            <span class="brand-text">
                <strong><?= e(APP_NAME) ?></strong>
                <small><?= e(APP_TAGLINE) ?></small>
            </span>
        </div>

        <section class="auth-card">
            <p class="eyebrow">BIENVENUE</p>
            <h1>Se connecter</h1>
            <p class="auth-subtitle">Accédez à votre espace de gestion</p>

            <?php if (!empty($erreur)): ?>
                <div class="message-error"><?= e($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('Auth/login')) ?>">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <label for="email">Adresse e-mail</label>
                <div class="input-wrap">
                    <?= icon('mail', 'input-icon') ?>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= e($email) ?>"
                        placeholder="nom@aquavolt.ma"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-wrap">
                    <?= icon('lock', 'input-icon') ?>
                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="password-toggle"
                        data-password-toggle="#mot_de_passe"
                        aria-label="Afficher le mot de passe"
                    >
                        <?= icon('eye') ?>
                    </button>
                </div>

                <button class="btn btn-primary btn-block" type="submit" style="margin-top: 1.25rem;">
                    Se connecter
                </button>
            </form>

        </section>

        <p class="auth-footnote">© <?= date('Y') ?> <?= e(APP_NAME) ?> — ENSA Tétouan</p>
    </div>

    <script src="<?= url('assets/js/app.js') ?>" defer></script>
</body>
</html>
