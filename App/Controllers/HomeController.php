<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): void
    {
        // Premier argument : la vue à afficher, soit app/Views/home/index.php
        // Second argument : les variables transmises à la vue et au gabarit.
        $this->render('home/index', [
            // Repris par le gabarit dans la balise <title>.
            'title'       => 'Accueil',
            // Repris par le gabarit dans la balise <meta name="description">.
            // Guillemets doubles autour de la chaîne, car elle contient une
            // apostrophe qu'il faudrait sinon échapper.
            'description' => "Page d'accueil du site Web réalisé dans le cadre de la SAÉ du semestre 3.",
        ]);
    }
}
