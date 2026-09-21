<?php

// declare(strict_types=1) force PHP à refuser un argument du mauvais type au
// lieu de le convertir en silence. Doit être la première instruction du fichier.
declare(strict_types=1);

// use importe la classe : on peut écrire "Router" au lieu de "App\Core\Router".
use App\Core\Router;

// define crée une constante globale, accessible depuis tout le projet.
// __DIR__ vaut le dossier de ce fichier, soit .../PROJET-SAE/public
// dirname remonte d'un niveau, donc BASE_PATH vaut .../PROJET-SAE
define('BASE_PATH', dirname(__DIR__));

// Composer : charge les librairies installées 
require BASE_PATH . '/vendor/autoload.php';

// Lit le fichier .env et remplit $_ENV avec les identifiants
Dotenv\Dotenv::createImmutable(BASE_PATH)->load();

// spl_autoload_register enregistre une fonction que PHP appellera tout seul
// chaque fois qu'une classe encore inconnue est utilisée.
// static devant function : la fonction n'a pas besoin du contexte $this.
spl_autoload_register(static function (string $class): void {
    // str_starts_with teste le début d'une chaîne.
    // Le point d'exclamation nie la condition : on sort si la classe ne
    // commence PAS par "App\", donc si elle ne vient pas de ce projet.
    // Les deux antislashs représentent un seul antislash : le premier échappe le second.
    if (!str_starts_with($class, 'App\\')) {
        // return sans valeur : on quitte la fonction sans rien charger.
        return;
    }

    // strlen compte les caractères de "App\", soit 4.
    // substr coupe la chaîne à partir du 4e caractère, ce qui retire le préfixe.
    // str_replace remplace les antislashs du namespace par des slashs de chemin.
    // Le point est l'opérateur de concaténation de chaînes en PHP.
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen('App\\'))) . '.php';

    // is_file vérifie que le fichier existe vraiment avant de le charger.
    if (is_file($file)) {
        // require charge et exécute le fichier, ce qui déclare la classe.
        require $file;
    }
});

// session_set_cookie_params configure le cookie de session. À appeler AVANT
// session_start, sinon le cookie est déjà envoyé et la configuration est ignorée.
session_set_cookie_params([
    // httponly : le cookie devient invisible pour JavaScript, donc un script
    // injecté par une faille XSS ne peut pas le voler.
    'httponly' => true,
    // samesite Lax : le cookie n'est pas envoyé lors des requêtes venant d'un
    // autre site, ce qui limite les attaques CSRF.
    'samesite' => 'Lax',
    // secure : le cookie ne circule qu'en HTTPS.
    // empty teste si la variable est absente ou vide ; le ! inverse le résultat,
    // donc secure vaut true uniquement quand on est en HTTPS.
    'secure'   => !empty($_SERVER['HTTPS']),
]);

// session_start ouvre la session et remplit le tableau $_SESSION.
session_start();

// new crée une instance de la classe Router.
$router = new Router();

// require renvoie ici la fonction écrite dans routes.php, puisque ce fichier
// se termine par un return. Les parenthèses juste après l'appellent aussitôt,
// en lui passant $router pour qu'elle y déclare les routes.
(require BASE_PATH . '/config/routes.php')($router);

// dispatch cherche la route correspondante et exécute l'action associée.
$router->dispatch(
    // $_SERVER est un tableau rempli par PHP avec les informations de la requête.
    // REQUEST_METHOD vaut GET, POST, etc.
    // L'opérateur ?? fournit une valeur de repli si la clé n'existe pas.
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    // REQUEST_URI contient le chemin demandé, par exemple /inscription
    $_SERVER['REQUEST_URI'] ?? '/'
);
