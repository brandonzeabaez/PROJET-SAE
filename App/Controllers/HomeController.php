<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Views\home\HomeView;

/**
 * @brief Contrôleur de la page d'accueil.
 *
 * Il délègue l'affichage de la vue d'accueil au composant HomeView.
 */
class HomeController
{
    /**
     * @brief Affiche la page d'accueil du site.
     */
    public function execute(): void
    {
        (new HomeView())->showHome();
    }
}