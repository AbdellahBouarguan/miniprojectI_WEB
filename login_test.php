<?php
/*
 * CONNEXION DE TEST - A SUPPRIMER AVANT LE RENDU / LE PUSH.
 *
 * Simule une connexion en remplissant la session, sans mot de passe.
 * A placer a la racine du projet : http://localhost/miniprojectI_WEB/login_test.php
 * (Apache sert directement ce fichier, il ne passe pas par le routeur.)
 */

declare(strict_types=1);

session_start();

define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);
require_once ROOT . 'config/app.php';

// Deconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_test.php');
    exit;
}

// Connexion simulee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = $_POST['as'] ?? '';

    if ($choice === 'fournisseur') {
        $_SESSION['role'] = 'fournisseur';
        $_SESSION['user_id'] = 1;
    } elseif (in_array($choice, ['1', '2', '3'], true)) {
        $_SESSION['role'] = 'client';
        $_SESSION['user_id'] = (int) $choice;
    }

    header('Location: ' . url('Client/all'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion de test</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<main class="container">
    <h1>Connexion de test</h1>
    <p><strong>Uniquement pour tester.</strong> Aucun mot de passe n'est verifie.</p>

    <p>
        Session actuelle :
        <?= isset($_SESSION['role'])
            ? e($_SESSION['role'] . ' #' . $_SESSION['user_id'])
            : 'non connecte' ?>
    </p>

    <form method="post">
        <label for="as">Se connecter en tant que :</label>
        <select name="as" id="as">
            <option value="fournisseur">Fournisseur (id 1)</option>
            <option value="1">Client 1 - Ahmed El Amrani</option>
            <option value="2">Client 2 - Sara Bennis</option>
            <option value="3">Client 3 - Youssef El Idrissi</option>
        </select>
        <button type="submit">Entrer</button>
    </form>

    <p><a href="login_test.php?logout=1">Se deconnecter</a></p>
</main>
</body>
</html>