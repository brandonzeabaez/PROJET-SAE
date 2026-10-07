<?php
/**
 * @file InscriptionController.php
 * @brief Contrôleur de la page d'inscription.
 *
 * Ce contrôleur traite l'affichage du formulaire et la soumission des
 * informations d'un nouvel utilisateur.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Models\InscriptionModel;
use App\Views\Auth\InscriptionView;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @brief Gère le flux d'inscription de l'utilisateur.
 */
class InscriptionController
{
    /** @var InscriptionView Vue associée au formulaire d'inscription. */
    private InscriptionView $view;

    /** @var InscriptionModel Modèle de persistance des données d'inscription. */
    private InscriptionModel $model;

    /** @var string Expression régulière de validation du mot de passe. */
    private const REGEX_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    /** @var array<string, mixed> Données récupérées depuis le formulaire. */
    private array $vals;

    /**
     * @brief Initialise la vue du formulaire d'inscription.
     */
    public function __construct()
    {
        $this->view = new InscriptionView();
    }

    /**
     * @brief Point d'entrée du contrôleur d'inscription.
     *
     * Affiche le formulaire si la requête est de type GET, sinon traite le POST.
     */
    public function execute(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->getShowForm();
            return;
        }

        $this->handlePost();
        exit;
    }

    /**
     * @brief Affiche le formulaire d'inscription.
     */
    private function getShowForm(): void
    {
        $this->view->showForm();
    }

    /**
     * @brief Vérifie la validité du mot de passe et de la confirmation.
     *
     * @return bool true si les règles de sécurité sont respectées, sinon false.
     */
    private function passwordValidation(): bool
    {
        return preg_match(self::REGEX_PASSWORD, $_POST['password']) && ($_POST['password'] === $_POST['password_confirmation']);
    }

    /**
     * @brief Traite la soumission du formulaire d'inscription.
     */
    private function handlePost(): void
    {
        if (!$this->passwordValidation()) {
            $this->view->showForm('Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &), et les deux saisies doivent être identiques.');
            return;
        }

        $this->vals = [
            'email' => $_POST['email'],
            'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'pays' => $_POST['pays'],
        ];

        $this->sendForm();
    }

    /**
     * @brief Enregistre l'utilisateur dans la base de données.
     *
     * En cas d'erreur de persistance, l'utilisateur reste sur le formulaire
     * avec un message explicite.
     */
    private function sendForm(): void
    {
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

    /**
     * @brief Envoie un e-mail de confirmation à l'utilisateur inscrit.
     *
     * @param string $email Adresse e-mail du nouvel utilisateur.
     * @param string $prenom Prénom de l'utilisateur.
     */
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
