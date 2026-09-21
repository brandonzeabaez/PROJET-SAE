<?php

/**
 * Seul fichier à modifier pour ajouter, renommer ou déplacer une URL.
 */

declare(strict_types=1);

// Les contrôleurs sont importés pour pouvoir écrire HomeController::class
// au lieu du nom complet avec son espace de noms.
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Core\Router;

// Ce fichier renvoie une fonction au lieu de s'exécuter directement. index.php
// la récupère puis l'appelle en lui passant le routeur.
// static : la fonction n'a pas besoin de contexte d'objet.
return static function (Router $router): void {
    // Premier argument : le chemin de l'URL. Ici la racine du site.
    // Second argument : l'action, sous forme [classe du contrôleur, méthode].
    // ::class renvoie le nom complet de la classe sous forme de chaîne, ce qui
    // permet à l'IDE de suivre la référence et de détecter une faute de frappe.
    $router->get('/', [HomeController::class, 'index']);

    // Même URL, deux routes distinctes : le routeur choisit selon la méthode HTTP.
    // GET affiche le formulaire, POST traite son envoi.
    $router->get('/connexion', [AuthController::class, 'showLoginForm']);
    $router->post('/connexion', [AuthController::class, 'login']);

    $router->get('/inscription', [AuthController::class, 'showRegistrationForm']);

    $router->get('/deconnexion', [AuthController::class, 'logout']);

    // TODO : mot de passe oublié, mentions légales, plan du site,
    // puis les routes de la partie connectée.
};
