<?php

declare(strict_types=1);

// namespace déclare l'espace de noms de la classe. Il doit correspondre au
// chemin du dossier pour que l'autoloader retrouve le fichier.
namespace App\Core;

// RuntimeException appartient à l'espace de noms global, d'où ce use.
use RuntimeException;

/**
 * Associe une méthode HTTP et un chemin d'URL à un couple [Contrôleur, méthode].
 */
// final interdit d'hériter de cette classe : elle est une feuille de l'arbre.
final class Router
{
    // private : accessible uniquement depuis l'intérieur de cette classe.
    // array : le type de la propriété, ici un tableau.
    // = [] initialise le tableau à vide, sinon il vaudrait null.
    /** @var array<string, array<string, array{class-string, string}>> */
    private array $routes = [];

    // public : appelable depuis l'extérieur de la classe.
    // : void indique que la méthode ne retourne aucune valeur.
    /** @param array{class-string, string} $action */
    public function get(string $path, array $action): void
    {
        // $this désigne l'objet courant.
        // On range l'action dans $routes['GET'] à la clé du chemin normalisé.
        $this->routes['GET'][$this->normalizePath($path)] = $action;
    }

    /** @param array{class-string, string} $action */
    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        // parse_url découpe une URL ; PHP_URL_PATH n'en garde que le chemin.
        // Cela écarte la query string : /produit/42?tri=prix donne /produit/42
        // (string) force la conversion en chaîne, car parse_url peut renvoyer null.
        $path = $this->normalizePath((string) parse_url($uri, PHP_URL_PATH));

        // strtoupper met en majuscules, pour accepter "get" comme "GET".
        // ?? [] : si aucune route n'est déclarée pour cette méthode, on parcourt
        // un tableau vide plutôt que de provoquer une erreur.
        // foreach parcourt le tableau : $route reçoit la clé, $action la valeur.
        foreach ($this->routes[strtoupper($method)] ?? [] as $route => $action) {
            // preg_match teste une expression régulière et renvoie 1 en cas de
            // correspondance. $matches est rempli par référence avec les captures.
            // !== 1 compare la valeur ET le type, contrairement à !=.
            if (preg_match($this->buildPattern($route), $path, $matches) !== 1) {
                // continue passe directement à l'itération suivante de la boucle.
                continue;
            }

            // preg_match renvoie les captures deux fois : sous une clé numérique
            // et sous leur nom. array_filter avec ARRAY_FILTER_USE_KEY filtre sur
            // les clés, et is_string ne garde que les clés textuelles, donc les
            // seuls paramètres nommés de la route.
            $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Déstructuration de tableau : la première valeur va dans la première
            // variable, la seconde dans la seconde.
            [$controllerClass, $controllerMethod] = $action;

            // method_exists vérifie que la méthode existe avant de l'appeler,
            // sinon PHP provoquerait une erreur fatale.
            if (!method_exists($controllerClass, $controllerMethod)) {
                // throw interrompt l'exécution en lançant une exception.
                // Les accolades dans la chaîne insèrent la valeur de la variable.
                throw new RuntimeException("Action introuvable : {$controllerClass}::{$controllerMethod}()");
            }

            // new $controllerClass() instancie la classe dont le nom est dans la
            // variable. Les accolades autour de $controllerMethod appellent la
            // méthode dont le nom est dans la variable.
            // array_values jette les clés pour ne garder que les valeurs, dans
            // l'ordre. L'opérateur ... les étale en arguments successifs.
            (new $controllerClass())->{$controllerMethod}(...array_values($parameters));

            // return sort de dispatch : une seule route doit être exécutée.
            return;
        }

        // Atteint uniquement si la boucle n'a trouvé aucune correspondance.
        $this->sendNotFound($path);
    }

    private function normalizePath(string $path): string
    {
        // trim retire les slashs au début et à la fin, puis on en remet un seul
        // devant. Sans cela, /inscription et /inscription/ seraient deux routes.
        return '/' . trim($path, '/');
    }

    private function buildPattern(string $route): string
    {
        // explode découpe la chaîne sur les slashs et renvoie un tableau de segments.
        // array_map applique la fonction à chaque segment et renvoie le tableau transformé.
        $segments = array_map(
            static function (string $segment): string {
                // \{ et \} échappent les accolades, qui ont un sens spécial en regex.
                // \w+ signifie un ou plusieurs caractères de mot (lettres, chiffres, _).
                // ^ et $ ancrent le motif au début et à la fin du segment.
                if (preg_match('#^\{(\w+)\}$#', $segment, $matches) === 1) {
                    // $matches[1] contient ce qui était entre parenthèses, donc le
                    // nom du paramètre. (?P<nom>...) crée un groupe de capture nommé.
                    // [^/]+ accepte tout sauf un slash, pour rester sur un seul segment.
                    return '(?P<' . $matches[1] . '>[^/]+)';
                }

                // preg_quote échappe les caractères spéciaux du segment, pour qu'il
                // soit comparé littéralement. Le second argument précise le
                // délimiteur de l'expression, ici le dièse.
                return preg_quote($segment, '#');
            },
            explode('/', $route)
        );

        // implode recolle les segments avec des slashs.
        // Le dièse sert de délimiteur à l'expression régulière, à la place du
        // slash habituel, qui serait à échapper partout dans une URL.
        return '#^' . implode('/', $segments) . '$#';
    }

    private function sendNotFound(string $path): void
    {
        // http_response_code fixe le code de statut renvoyé au navigateur.
        http_response_code(404);

        // echo envoie du texte au navigateur.
        // htmlspecialchars convertit les caractères HTML en entités : le chemin
        // vient du visiteur, donc l'afficher brut permettrait une injection de
        // code (faille XSS).
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
            . '<title>Page introuvable</title></head><body>'
            . '<h1>404 – Page introuvable</h1><p>Aucune route ne correspond à '
            . htmlspecialchars($path, ENT_QUOTES, 'UTF-8')
            . '.</p><p><a href="/connexion">Aller à la connexion</a></p>'
            . '</body></html>';
    }
}
