<?php
/**
 * @file Model.php
 * @brief Classe de base pour les modèles de données.
 *
 * Elle initialise la configuration de l'application et ouvre la connexion
 * PDO vers la base de données MySQL utilisée par le projet.
 *
 * @author Brandon
 * @author Aymen
 * @author Ymen
 * @author Milan
 * @date 2026
 */
namespace App\Core;

use PDO;

require __DIR__ . '../../../vendor/autoload.php';

/**
 * @brief Classe de base pour les modèles de données.
 *
 * Elle initialise la configuration de l'application et ouvre la connexion
 * PDO vers la base de données MySQL utilisée par le projet.
 */
class Model
{
    /** @var PDO Connexion PDO active à la base de données. */
    protected $pdo;

    /**
     * @brief Constructeur du modèle.
     *
     * Charge les variables d'environnement et initialise la connexion.
     */
    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config');
        $dotenv->load();
        $this->connect();
    }

    /**
     * @brief Établit la connexion à la base de données.
     *
     * La configuration provient des variables d'environnement du fichier
     * `.env` ou de la configuration locale du projet.
     */
    public function connect(): void
    {
        try {
            $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'];

            $this->pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $this->pdo->exec('SET CHARACTER SET utf8');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die('Erreur ' . $e->getMessage());
        }
    }

    /**
     * @brief Placeholder pour exécuter une requête SQL dans une classe dérivée.
     *
     * @param string $sql Requête SQL à exécuter.
     */
    public function execute(string $sql): void
    {
    }
}
