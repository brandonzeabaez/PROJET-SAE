<?php
/**
 * @file Register.php
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

use App\Models\RegisterModel;
use App\Models\User;
use App\Views\Auth\RegisterView;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @brief Gère le flux d'inscription de l'utilisateur.
 */
class Register
{
    /** @var RegisterView Vue associée au formulaire d'inscription. */
    private RegisterView $view;

    /** @var RegisterModel Modèle de persistance des données d'inscription. */
    private RegisterModel $model;

    /** @var string Expression régulière de validation du mot de passe. */
    private const REGEX_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    /** @var string[] Prérequis du mot de passe affichés dans le formulaire (doivent correspondre à REGEX_PASSWORD). */
    private const PASSWORD_REQUIREMENTS = [
        'Au moins 8 caractères',
        'Au moins une lettre minuscule',
        'Au moins une lettre majuscule',
        'Au moins un chiffre',
        'Au moins un caractère spécial parmi @ $ ! % * ? &',
        'Uniquement des lettres non accentuées, des chiffres et ces caractères spéciaux',
    ];

    /** @var array<string, mixed> Données récupérées depuis le formulaire. */
    private array $vals;

    /**
     * @brief Initialise la vue du formulaire d'inscription.
     */
    public function __construct()
    {
        $this->view = new RegisterView();
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
        $this->view->showForm(null, [], self::PASSWORD_REQUIREMENTS);
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
    private function handlePost() : void {
        if (!$this->passwordValidation()) {
            $this->view->showForm(
                'Le mot de passe ne respecte pas les prérequis indiqués ou les deux saisies ne sont pas identiques.',
                $_POST, // <-- On renvoie les données saisies à la vue
                self::PASSWORD_REQUIREMENTS
            );
            return;
        }
        $this->vals = [
            'email' => $_POST['email'],
            'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'pays' => $_POST['pays'],
            // jeton secret envoyé dans le lien de confirmation
            'verification_token' => bin2hex(random_bytes(32))
        ];

        $this->sendForm();
    }

    /**
     * @brief Enregistre l'utilisateur dans la base de données.
     *
     * En cas d'erreur de persistance, l'utilisateur reste sur le formulaire
     * avec un message explicite.
     */
    private function sendForm() : void {
        try {
            $this->model = new RegisterModel($this->vals);
            $this->model->sendFormToDB();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            // Si l'email est déjà utilisé, on renvoie aussi $_POST pour ne pas tout effacer
            $this->view->showForm('Le mail saisi est invalide ou est déjà utilisé', $_POST, self::PASSWORD_REQUIREMENTS);
            return;
        }

        $this->sendConfirmationMail($this->vals['email'], $this->vals['prenom'], $this->vals['verification_token']);

        // message affiché une seule fois sur la page de connexion
        $_SESSION['message'] = 'Un email de confirmation vous a été envoyé. Cliquez sur le lien qu\'il contient pour activer votre compte.';
        header('Location: /connexion');
        exit;
    }

    /**
     * @brief Valide l'adresse mail via le lien de confirmation reçu par e-mail.
     *
     * Le compte est activé si le jeton existe et n'est pas expiré, puis
     * l'utilisateur est redirigé vers la page de connexion.
     */
    public function verify(): void
    {
        // code secret dans l'adresse
        $token = (string) filter_input(INPUT_GET, 'token');
        $userModel = new User();
        $user = $token !== '' ? $userModel->findByVerificationToken($token) : null;

        if ($user === null) {
            $_SESSION['error'] = 'Lien de confirmation invalide ou expiré.';
        } else {
            $userModel->markEmailVerified((int) $user['id_user']);
            $_SESSION['message'] = 'Adresse mail confirmée, vous pouvez vous connecter.';
        }

        header('Location: /connexion');
        exit;
    }

    /**
     * @brief Envoie à l'utilisateur inscrit un e-mail contenant le lien de confirmation.
     *
     * @param string $email Adresse e-mail du nouvel utilisateur.
     * @param string $firstName Prénom de l'utilisateur.
     * @param string $token Jeton de vérification de l'adresse mail.
     */
    private function sendConfirmationMail(string $email, string $firstName, string $token): void
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'https://masae2026.alwaysdata.net/', '/');
        $link = $appUrl . '/inscription/verify?token=' . $token;

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
            $mail->addAddress($email, $firstName);

            $firstNameHtml = htmlspecialchars($firstName);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre inscription';
            $mail->Body    = "<p>Bonjour $firstNameHtml,</p>
                <p>Merci pour votre inscription. Pour activer votre compte, confirmez votre adresse mail en cliquant sur le lien ci-dessous :</p>
                <p><a href='{$link}'>{$link}</a></p>
                <p>Ce lien est valable 24 heures. Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement cet e-mail.</p>
                <p>L'équipe SAE</p>";
            $mail->AltBody = "Bonjour $firstName,\n\nMerci pour votre inscription. Pour activer votre compte, "
                . "confirmez votre adresse mail en cliquant sur le lien ci-dessous :\n{$link}\n\n"
                . "Ce lien est valable 24 heures. Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement cet e-mail.\n\nL'équipe SAE";

            $mail->send();
        } catch (Exception $e) {
            error_log('Mail de confirmation non envoyé : ' . $mail->ErrorInfo);
        }
    }
}
