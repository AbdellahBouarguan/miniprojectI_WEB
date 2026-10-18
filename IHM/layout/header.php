<?php
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= url('') ?>"><?= e(APP_NAME) ?></a>
    <nav class="site-nav">
        <a href="<?= url('Client/all') ?>">Clients</a>
        <a href="<?= url('Consommation/add') ?>">Consommations</a>
        <a href="<?= url('Facture/all') ?>">Factures</a>
        <a href="<?= url('Anomalie/index') ?>">Anomalies</a>
        <?php if ($role === null): ?>
            <a href="<?= url('Auth/login') ?>">Connexion</a>
        <?php else: ?>
            <span class="badge"><?= e((string) $role) ?></span>
            <a href="<?= url('Auth/logout') ?>">Deconnexion</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
