<?php

declare(strict_types=1);

session_start();

define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);

require_once ROOT . 'config/app.php';
require_once ROOT . 'BD/Connexion.php';

$url = trim((string) ($_GET['url'] ?? ''), '/');
$segments = $url === '' ? [] : explode('/', $url);

$controller = $segments[0] ?? 'Home';
$action = $segments[1] ?? 'index';
$id = $segments[2] ?? null;

$allowedControllers = ['Home', 'Auth', 'Client', 'Consommation', 'Facture', 'Anomalie'];

if (!in_array($controller, $allowedControllers, true) || !preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
    http_response_code(404);
    require ROOT . 'IHM/404.php';
    exit;
}

$controllerFile = ROOT . 'Traitement/' . $controller . '.php';

if (!is_file($controllerFile)) {
    http_response_code(404);
    require ROOT . 'IHM/404.php';
    exit;
}

require_once $controllerFile;

$function = strtolower($controller) . '_' . $action;

if (!function_exists($function)) {
    http_response_code(404);
    require ROOT . 'IHM/404.php';
    exit;
}

$function($id);
