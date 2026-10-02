<?php

define('BASE_PATH', dirname(__DIR__));


session_start([
    'cookie_lifetime' => 7200, //  connexion 2h
    'gc_maxlifetime'  => 7200, //  session 2h
    'cookie_httponly' => true, // sécurité  JavaScript pas lire le cookie
]);

// Connecté  > 2h  déconnection 
if (isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > 7200) {
    session_unset();
}

require BASE_PATH . '/vendor/autoload.php';

// routeur regarde l'URL lance contrôleur
$router = new App\Core\Router();
$router->route();
