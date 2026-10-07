<?php

define('BASE_PATH', dirname(__DIR__));

session_start([
    'cookie_httponly' => true, // sécurité  JavaScript pas lire le cookie
]);

require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// routeur regarde l'URL lance contrôleur
$router = new App\Core\Router();
$router->route();