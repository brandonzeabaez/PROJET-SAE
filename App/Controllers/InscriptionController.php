<?php
    namespace App\Controllers;
    use App\Models\InscriptionModel;
    use App\Views\Auth\InscriptionView;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;


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

            // L'inscription est enregistrée : un échec d'envoi du mail ne doit pas la bloquer
            $this->envoyerMailConfirmation($vals['email'], $vals['prenom']);

            exit;
        }

        private function envoyerMailConfirmation(string $email, string $prenom): void
        {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USERNAME'];
                $mail->Password   = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = PHPMailer::CHARSET_UTF8;

                $mail->setFrom($_ENV['MAIL_USERNAME'], 'SAE');
                $mail->addAddress($email, $prenom);

                $prenomHtml = htmlspecialchars($prenom);
                $mail->isHTML(true);
                $mail->Subject = 'Confirmation de votre inscription';
                $mail->Body    = "<p>Bonjour $prenomHtml,</p>
                    <p>Votre inscription a bien été prise en compte. Vous pouvez dès maintenant vous connecter avec votre adresse e-mail.</p>
                    <p>L'équipe SAE</p>";
                $mail->AltBody = "Bonjour $prenom,\n\nVotre inscription a bien été prise en compte. "
                    . "Vous pouvez dès maintenant vous connecter avec votre adresse e-mail.\n\nL'équipe SAE";

                $mail->send();
            } catch (Exception $e) {
                error_log('Mail de confirmation non envoyé : ' . $mail->ErrorInfo);
            }
        }

    }
    ?>
