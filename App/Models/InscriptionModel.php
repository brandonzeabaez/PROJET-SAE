<?php
    namespace App\Models;
    use App\Core\Model;


class InscriptionModel extends Model {
        private String $query='INSERT INTO User(email,mot_de_passe,nom,prenom) VALUES (:email,:mot_de_passe,:nom,:prenom)';
        private array $dataInscription;
        function __construct(Array $dataInscription)
        {
            parent::__construct();
            $this->dataInscription = $dataInscription;
        }
        public function sendFormToDB() : void {
                $stmt = $this->pdo->prepare($this->query);
                $stmt->execute($this->dataInscription);
            }
        }
?>
