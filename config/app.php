<?php

define('APP_NAME', 'Mini Projet 1 - Version Alpha');
define('BASE_URL', '/miniprojectI_WEB/');
define('TVA_RATE', 18.00);
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('UPLOAD_DIR', ROOT . 'uploads/compteurs/');
define('UPLOAD_URL', BASE_URL . 'uploads/compteurs/');

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
