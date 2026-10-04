<?php
    declare(strict_types=1);
    namespace App\Controllers;
    use App\Models\InscriptionModel;
    use App\Views\Auth\InscriptionView;


class InscriptionController {
    private InscriptionView $view;
    private InscriptionModel $model;
    private const REGEX_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    private array $vals ;
    public function __construct()
    {
        $this->view = new InscriptionView();

    }
    public function execute() :void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->getShowForm();
            return;
        }
        $this->handlePost();
        exit;
    }
    private function getShowForm() : void {
            $this->view->showForm();
        }
        private function passwordValidation() : bool {
            return preg_match(self::REGEX_PASSWORD, $_POST['password']) && ($_POST['password'] === $_POST['password_confirmation']);
        }
        private function handlePost() : void {
            if (!$this->passwordValidation()) {
                $this->view->showForm('Mot de passe incorrect');
                return;
            }
            $this->vals = [
                'email' => $_POST['email'],
                'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'pays' => $_POST['pays']];

            $this->sendForm();
    }
    private function sendForm() : void {
        try {
            $this->model = new InscriptionModel($this->vals);
            $this->model->sendFormToDB();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            $this->view->showForm('Le mail saisi est invalide ou est déjà utilisé');
            return;
        }
        header('Location: /connexion');
        exit;
    }



    }

