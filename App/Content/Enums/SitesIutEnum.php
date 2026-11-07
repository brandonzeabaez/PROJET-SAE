<?php
/**
 * @file SitesIutEnum.php
 * @brief Énumération des sites IUT de l'académie d'Aix-Marseille.
 *
 * Le secrétariat d'un département choisit son site dans cette liste lors
 * de sa première connexion. La valeur est le nom du site, enregistré tel
 * quel dans la colonne `ville` de la table `Departement`.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */
namespace App\Content\Enums;

/**
 * @brief Énumération des sites IUT de l'académie d'Aix-Marseille.
 */
enum SitesIutEnum : string
{
    // IUT d'Aix-Marseille (Aix-Marseille Université)
    case AixEnProvence = 'Aix-en-Provence';
    case Arles = 'Arles';
    case Aubagne = 'Aubagne';
    case DigneLesBains = 'Digne-les-Bains';
    case Gap = 'Gap';
    case LaCiotat = 'La Ciotat';
    case Marseille = 'Marseille';
    case SalonDeProvence = 'Salon-de-Provence';

    // IUT d'Avignon (Avignon Université)
    case Avignon = 'Avignon';
}
