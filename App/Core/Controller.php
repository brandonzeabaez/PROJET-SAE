<?php
/**
 * @file Controller.php
 * @brief Classe abstraite racine des contrôleurs de l'application.
 *
 * Elle centralise le mécanisme de rendu des vues ainsi que les
 * redirections HTTP pour les contrôleurs du projet.
 *
 * @author Brandon
 * @author Aymen
 * @author Ymen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * @brief Classe abstraite racine des contrôleurs de l'application.
 *
 * Elle centralise le mécanisme de rendu des vues ainsi que les
 * redirections HTTP pour les contrôleurs du projet.
 */
abstract class Controller
{
    /**
     * @brief Rend une vue en injectant les variables de contexte.
     *
     * Le fichier de vue est chargé depuis le dossier App/Views puis
     * encapsulé dans le layout du site pour garder une structure HTML commune.
     *
     * @param string $view Nom de la vue à afficher, sans extension PHP.
     * @param array<string, mixed> $data Tableau associatif de variables à exposer à la vue.
     *
     * @throws \RuntimeException Si le fichier de vue demandé est introuvable.
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = BASE_PATH . '/App/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("Vue introuvable : {$view}");
        }

        $data += [
            'title'       => 'Projet SAÉ',
            'description' => 'Site Web réalisé dans le cadre de la SAÉ du semestre 3.',
        ];

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        require BASE_PATH . '/App/Views/layout.php';
    }

    /**
     * @brief Redirige le navigateur vers une route donnée.
     *
     * Cette méthode envoie un header HTTP de redirection puis termine
     * immédiatement le script pour éviter tout affichage supplémentaire.
     *
     * @param string $path Chemin de destination, par exemple "/connexion".
     *
     * @return never La méthode ne retourne jamais la main.
     */
    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 302);
        exit;
    }
}
