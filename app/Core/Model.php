<?php
    namespace App\Core;
    use PDO;

    require __DIR__ . '../../../vendor/autoload.php';

    class Model {
        private static $pdo;

        function __construct() {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config');
            $dotenv->load();
            $this -> connect();
        }


        function connect() {
            try {
                $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname='. $_ENV['DB_NAME'];

                self::$pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
                self::$pdo->exec('SET CHARACTER SET utf8');
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo 'Connected successfully';
            }
            catch (\PDOException $e) {
                die('Erreur ' . $e->getMessage());
            }
        }
        function execute(String $sql) {

        }

    }
    $tmp = new Model();
?>