<?php
    declare(strict_types=1);
    namespace App\Controllers;
    use App\Models\InscriptionModel;
    use App\Views\Auth\InscriptionView;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;


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
                $this->view->showForm('Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &), et les deux saisies doivent être identiques.');
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

        $this->envoyerMailConfirmation($this->vals['email'], $this->vals['prenom']);
        header('Location: /connexion');
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

