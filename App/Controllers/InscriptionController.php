<?php
    namespace App\Controllers;
    use App\Models\InscriptionModel;
    use App\Views\Auth\InscriptionView;


class InscriptionController {
        public function execute() :void
        {
            $view = new InscriptionView();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $view->showForm();
                return;
            }

            // POST : validation
            if ( $_POST['password'] !== $_POST['passwordConfirm']) {
                $view->showForm('Les mots de passe ne correspondent pas.');
                return;
            }
            $vals = [
                'email' => $_POST['email'],
                'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
            ];

            try {
                (new InscriptionModel($vals))->sendFormToDB();
            } catch (\PDOException $e) {
                error_log($e->getMessage());
                $view->showForm('Inscription impossible (email déjà utilisé ?).');
                return;
            }

            exit;
        }

    }
    ?>
