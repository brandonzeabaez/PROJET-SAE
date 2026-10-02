<?php
declare(strict_types=1);

namespace App\Core;

class
Router {
    private array $routes = [];
    function __construct() {
        $this->loadRoutes();
    }
    function loadRoutes() : void {
    // charge l'ensemble des routes sur routes si le fichier n'existe pas on saute une exception
        $file = __DIR__ . "/../../config/routes.json";
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
            $data_routes = $json["routes"];
            foreach ($data_routes as $route) {
                // parcours d'une table de deux valeurs et on stocke dans les routes [url => nom_du_controller]
                $this->routes[$route["url"]] = $route["controller"];
            }
        }
        else { throw new \Exception("Routes file not found");}
    }

    function route() : void
    {
        $url = parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH);
        if (isset($this->routes[$url])) {
            $this->loadHandler($this->routes[$url]);
        }
        else {
            echo 'error 404 page '. $_SERVER['REQUEST_URI'] . 'not found' ;
        }
    }
    function loadHandler(string $handler) : void {
        (new $handler())->execute();
    }
    }



?>