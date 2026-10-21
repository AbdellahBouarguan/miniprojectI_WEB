<?php
$role = $_SESSION['role'] ?? null;
$estConnecte = in_array($role, ['client', 'fournisseur'], true);

$nom = trim((string) ($_SESSION['nom'] ?? ''));
$prenom = trim((string) ($_SESSION['prenom'] ?? ''));
$nomComplet = trim($prenom . ' ' . $nom);
$initiales = initials($prenom, $nom);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path .= '/' . (string) ($_GET['url'] ?? '');
$active = 'dashboard';

if (str_contains($path, '/Consommation')) {
    $active = 'consommation';
} elseif (str_contains($path, '/Client')) {
    $active = 'clients';
} elseif (str_contains($path, '/Anomalie')) {
    $active = 'anomalies';
} elseif (str_contains($path, '/Facture')) {
    $active = 'factures';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b2a4a">
    <title><?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= url('assets/img/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=20261020">
</head>
<body class="<?= $estConnecte ? 'app-body' : 'public-body' ?>">
<?php if ($estConnecte): ?>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-user">
                <div class="avatar"><?= e($initiales) ?></div>
                <div class="sidebar-user-text">
                    <strong><?= e(role_label($role)) ?></strong>
                    <span>BONJOUR, <?= e($nomComplet !== '' ? $nomComplet : 'Utilisateur') ?></span>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Navigation principale">
                <?php if ($role === 'client'): ?>
                    <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= url('') ?>">
                        <?= icon('grid') ?> Tableau de bord
                    </a>
                    <a class="<?= $active === 'factures' ? 'active' : '' ?>" href="<?= url('Facture/all') ?>">
                        <?= icon('file') ?> Mes factures
                    </a>
                    <a class="<?= $active === 'consommation' ? 'active' : '' ?>" href="<?= url('Consommation/add') ?>">
                        <?= icon('droplet') ?> Saisie consommation
                    </a>
                    <a class="disabled" href="#" title="Bientôt disponible">
                        <?= icon('message') ?> Réclamations
                    </a>
                <?php else: ?>
                    <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= url('') ?>">
                        <?= icon('grid') ?> Tableau de bord
                    </a>
                    <a class="<?= $active === 'clients' ? 'active' : '' ?>" href="<?= url('Client/all') ?>">
                        <?= icon('users') ?> Clients
                    </a>
                    <a class="<?= $active === 'anomalies' ? 'active' : '' ?>" href="<?= url('Anomalie/index') ?>">
                        <?= icon('alert') ?> Anomalies
                    </a>
                    <a class="<?= $active === 'factures' ? 'active' : '' ?>" href="<?= url('Facture/all') ?>">
                        <?= icon('file') ?> Factures
                    </a>
                    <a class="disabled" href="#" title="Bientôt disponible">
                        <?= icon('message') ?> Réclamations
                    </a>
                    <a class="disabled" href="#" title="Bientôt disponible">
                        <?= icon('card') ?> Paiements
                    </a>
                    <a class="disabled" href="#" title="Bientôt disponible">
                        <?= icon('check') ?> Vérification annuelle
                    </a>
                <?php endif; ?>
            </nav>

            <a class="sidebar-logout" href="<?= url('Auth/logout') ?>">
                <?= icon('logout') ?> Se déconnecter
            </a>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <a class="brand" href="<?= url('') ?>">
                    <?= brand_mark() ?>
                    <span class="brand-text">
                        <strong><?= e(APP_NAME) ?></strong>
                        <small><?= e(APP_TAGLINE) ?></small>
                    </span>
                </a>

                <button class="icon-button" type="button" aria-label="Notifications">
                    <?= icon('bell') ?>
                    <span class="notif-dot"></span>
                </button>
            </header>

            <main class="dash-main">
<?php else: ?>
    <header class="public-header">
        <a class="brand" href="<?= url('') ?>">
            <?= brand_mark() ?>
            <span class="brand-text">
                <strong><?= e(APP_NAME) ?></strong>
                <small><?= e(APP_TAGLINE) ?></small>
            </span>
        </a>

        <nav class="site-nav">
            <a href="<?= url('Auth/login') ?>">Connexion</a>
        </nav>
    </header>

    <main class="container">
<?php endif; ?>
