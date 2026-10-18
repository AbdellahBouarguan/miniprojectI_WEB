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
