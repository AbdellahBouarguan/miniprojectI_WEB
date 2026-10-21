<?php

define('APP_NAME', 'Mini Projet 1 - Version Alpha');
define('BASE_URL', '/miniprojectI_WEB/');
define('TVA_RATE', 10.8);
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
//le chemin des images uploadé 
define('UPLOAD_DIR', ROOT . 'uploads/compteurs/');
define('UPLOAD_URL', BASE_URL . 'uploads/compteurs/');
//organiser l'url 
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}
//permet de protéger ce qui est affiché au cas où le clien saisit un code html , elle le traite comme texte normal 
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
