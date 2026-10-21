<?php

function db(): mysqli
{
    static $link = null;

    if ($link instanceof mysqli) {
        return $link;
    }

    $config = require ROOT . 'config/database.php';

    $link = mysqli_connect(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['dbname'],
        (int) $config['port']
    );

    if (!$link) {
        throw new RuntimeException('Connexion a la base impossible : ' . mysqli_connect_error());
    }

    mysqli_set_charset($link, $config['charset'] ?? 'utf8mb4');

    return $link;
}

/**
 * Connexion PDO, utilisee par la partie consommation.
 * Le reste de l'application continue d'utiliser db() en mysqli.
 */
function pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require ROOT . 'config/database.php';

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        (int) $config['port'],
        $config['dbname'],
        $config['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
