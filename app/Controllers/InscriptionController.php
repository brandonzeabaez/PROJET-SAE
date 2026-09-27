<?php
    namespace App\Controllers;
    use App\Models\InscriptionModel;
    use App\Views\auth\InscriptionView;


class InscriptionController {
        public function execute() :void {

            (new InscriptionView())->showForm();
            $vals = array('email' => $_POST['email'] , 'password' => hash_pbkdf2($_POST['password']) ,
                          'nom' => $_POST['nom'],'prenom' => $_POST['prenom'] );
            (new InscriptionModel($vals))->sendFormToDB();
        }

    }
    ?>
