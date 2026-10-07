<?php
/**
 * @file Router.php
 * @brief Charge et résout les routes HTTP vers les contrôleurs associés.
 *
 * Le fichier de configuration `routes.json` est analysé pour associer une URL
 * à une classe de contrôleur, puis le bon contrôleur est instancié.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */
declare(strict_types=1);

namespace App\Core;

/**
 * @brief Charge et résout les routes HTTP vers les contrôleurs associés.
 *
 * Le fichier de configuration `routes.json` est analysé pour associer une URL
 * à une classe de contrôleur, puis le bon contrôleur est instancié.
 */
class Router
{
    /** @var array<string, string> Tableau des routes indexées par leur URL. */
    private array $routes = [];

    /**
     * @brief Constructeur du routeur.
     *
     * Charge immédiatement la configuration des routes.
     */
    public function __construct()
    {
        $this->loadRoutes();
    }

    /**
     * @brief Lit le fichier JSON des routes et les enregistre en mémoire.
     *
     * @throws \Exception Si le fichier de configuration des routes est absent.
     */
    public function loadRoutes(): void
    {
        $file = __DIR__ . "/../../config/routes.json";

        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
            $data_routes = $json["routes"] ?? [];

            foreach ($data_routes as $route) {
                $this->routes[$route["url"]] = $route["controller"];
            }
        } else {
            throw new \Exception("Routes file not found");
        }
    }

    /**
     * @brief Détermine le contrôleur à exécuter selon l'URL demandée.
     *
     * Si aucune route ne correspond, une page 404 est affichée.
     */
    public function route(): void
    {
        $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if (isset($this->routes[$url])) {
            $this->loadHandler($this->routes[$url]);
        } else {
            echo 'error 404 page ' . $_SERVER['REQUEST_URI'] . ' not found';
        }
    }

    /**
     * @brief Instancie et exécute un contrôleur associé à une route.
     *
     * @param string $handler Nom complet de la classe contrôleur.
     */
    public function loadHandler(string $handler): void
    {
        (new $handler())->execute();
    }
}