<?php

declare(strict_types=1);

namespace App\Core;

// PDO (PHP Data Objects) est la classe standard de PHP pour dialoguer avec une
// base de données. Elle est dans l'espace de noms global, d'où ce use.
use PDO;

/**
 * Classe mère des modèles : seule couche du projet où l'on écrit du SQL.
 */
abstract class Model
{
    // static : la propriété appartient à la CLASSE et non à chaque objet. Tous
    // les modèles partagent donc la même connexion, ouverte une fois par requête.
    // Le point d'interrogation rend le type nullable : la propriété accepte un
    // PDO ou null, ce qui permet de démarrer sans connexion.
    private static ?PDO $connection = null;

    protected function db(): PDO
    {
        // instanceof teste si la valeur est un objet de cette classe.
        // Si la connexion existe déjà, on la renvoie sans en rouvrir une.
        if (self::$connection instanceof PDO) {
            // self:: désigne la classe elle-même, là où $this désigne l'objet.
            return self::$connection;
        }

        // require renvoie le tableau retourné par config.php ; les crochets qui
        // suivent en extraient directement la section 'database'.
        $config = (require BASE_PATH . '/config/config.php')['database'];

        // sprintf construit une chaîne à partir d'un modèle :
        // %s insère une chaîne, %d insère un entier.
        // Le DSN (Data Source Name) décrit à PDO où et comment se connecter.
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        // Le constructeur de PDO ouvre la connexion. Le quatrième argument est
        // un tableau d'options.
        self::$connection = new PDO($dsn, $config['user'], $config['password'], [
            // ERRMODE_EXCEPTION : une erreur SQL lève une exception. Sans cela,
            // PDO échouerait en silence et le bug passerait inaperçu.
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // FETCH_ASSOC : les résultats arrivent en tableaux associatifs,
            // indexés par nom de colonne plutôt que par numéro.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // EMULATE_PREPARES à false : les requêtes sont réellement préparées
            // par MySQL au lieu d'être simulées par PDO. C'est la vraie
            // protection contre l'injection SQL.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$connection;
    }
}
