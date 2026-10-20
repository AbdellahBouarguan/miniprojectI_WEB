<?php
declare(strict_types=1);

/**
 * Fichier de test uniquement.
 * Il ouvre une session pour le client 1 sans passer par l'authentification,
 * car la partie Auth est encore un placeholder.
 *
 * A SUPPRIMER avant la remise finale.
 */

session_start();

$idClient = isset($_GET['client']) ? (int) $_GET['client'] : 1;
$_SESSION['id_client'] = in_array($idClient, [1, 2, 3], true) ? $idClient : 1;
$_SESSION['role'] = 'client';

header('Location: ../Consommation/add');
exit;
