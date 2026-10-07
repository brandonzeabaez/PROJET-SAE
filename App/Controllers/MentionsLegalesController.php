<?php
/**
 * @file MentionsLegalesController.php
 * @brief Contrôleur de la page des mentions légales.
 *
 * Il affiche la vue dédiée aux informations légales et réglementaires
 * relatives au site.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Views\MentionsLegales\MentionsLegalesView;

/**
 * @brief Contrôleur de la page des mentions légales.
 *
 * Il affiche la vue dédiée aux informations légales et réglementaires
 * relatives au site.
 */
class MentionsLegalesController
{
    /**
     * @brief Affiche la page des mentions légales.
     */
    public function execute(): void
    {
        (new MentionsLegalesView())->show();
    }
}
