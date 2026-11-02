<?php
/**
 * @file Admin.php
 * @brief Contrôleur du panel administrateur des comptes départements.
 *
 * L'administrateur peut créer un compte pour un département (un lien unique
 * est envoyé au secrétariat), l'activer, le désactiver ou le supprimer.
 * Il ne peut ni modifier les informations d'un département ni voir son mot de passe.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Departement as DepartementModel;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @brief Contrôleur du panel administrateur des comptes départements.
 *
 * Routes : /admin (liste), et en POST /admin/create, /admin/activate,
 * /admin/deactivate, /admin/delete.
 */
final class Admin extends Controller
{
    /**
     * @brief Vérifie l'accès administrateur avant toute action du panel.
     */
    public function __construct()
    {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Accès réservé aux administrateurs.';
            $this->redirect('/connexion');
        }

        // jeton anti-CSRF : un autre site ne peut pas déclencher les actions du panel
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * @brief Affiche le panel : formulaire de création et liste des comptes.
     */
    public function execute(): void
    {
        // messages gardés en session avant la redirection, affichés une seule fois
        $error = $_SESSION['error'] ?? '';
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['error'], $_SESSION['message']);

        $this->render('Admin/panel', [
            'title'        => 'Administration',
            'description'  => 'Gestion des comptes des départements de l\'IUT.',
            'error'        => $error,
            'message'      => $message,
            'departements' => (new DepartementModel())->findAll(),
            'csrfToken'    => $_SESSION['csrf_token'],
        ]);
    }

    /**
     * @brief Crée un compte département et envoie le lien unique au secrétariat.
     */
    public function create(): void
    {
        $this->checkPostRequest();

        $email = trim((string) filter_input(INPUT_POST, 'email'));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100) {
            $_SESSION['error'] = 'Adresse e-mail du secrétariat invalide.';
            $this->redirect('/admin');
        }

        // jeton secret envoyé dans le lien, seul son hash est stocké en base
        $token = bin2hex(random_bytes(32));

        try {
            (new DepartementModel())->create($email, $token);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = 'Un compte existe déjà pour cette adresse e-mail.';
            $this->redirect('/admin');
        }

        if ($this->sendSetupMail($email, $token)) {
            $_SESSION['message'] = "Compte créé : un lien de première connexion a été envoyé à {$email}.";
        } else {
            $_SESSION['error'] = "Compte créé, mais le mail n'a pas pu être envoyé à {$email}. Supprimez le compte et recréez-le.";
        }

        // Post-Redirect-Get : F5 ne recrée pas le compte
        $this->redirect('/admin');
    }

    /**
     * @brief Active un compte département dont la première connexion est faite.
     */
    public function activate(): void
    {
        $this->checkPostRequest();

        if ((new DepartementModel())->setActive($this->postedId(), true)) {
            $_SESSION['message'] = 'Compte activé.';
        } else {
            $_SESSION['error'] = 'Ce compte ne peut pas être activé tant que le département n\'a pas fait sa première connexion.';
        }

        $this->redirect('/admin');
    }

    /**
     * @brief Désactive un compte département (il ne peut plus se connecter).
     */
    public function deactivate(): void
    {
        $this->checkPostRequest();

        (new DepartementModel())->setActive($this->postedId(), false);
        $_SESSION['message'] = 'Compte désactivé.';

        $this->redirect('/admin');
    }

    /**
     * @brief Supprime définitivement un compte département.
     */
    public function delete(): void
    {
        $this->checkPostRequest();

        (new DepartementModel())->delete($this->postedId());
        $_SESSION['message'] = 'Compte supprimé.';

        $this->redirect('/admin');
    }

    /**
     * @brief Refuse les actions qui ne viennent pas d'un formulaire POST du panel.
     */
    private function checkPostRequest(): void
    {
        $token = (string) filter_input(INPUT_POST, 'csrf_token');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['error'] = 'Requête invalide, veuillez réessayer.';
            $this->redirect('/admin');
        }
    }

    /**
     * @brief Récupère l'identifiant du département envoyé par le formulaire.
     *
     * @return int Identifiant du département (0 si absent ou invalide).
     */
    private function postedId(): int
    {
        return (int) filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    }

    /**
     * @brief Envoie au secrétariat le mail contenant le lien de première connexion.
     *
     * @param string $email Adresse e-mail du secrétariat.
     * @param string $token Jeton en clair à placer dans le lien.
     * @return bool true si le mail a été envoyé.
     */
    private function sendSetupMail(string $email, string $token): bool
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'https://masae2026.alwaysdata.net/', '/');
        $link = $appUrl . '/departement/configurer?token=' . $token;
        $hours = DepartementModel::SETUP_LINK_HOURS;

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

            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Bourse échange');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Création du compte de votre département - Bourse d\'échange';
            $mail->Body    = "<p>Bonjour,</p>
                <p>Un compte a été créé pour votre département sur la Bourse d'échange de l'IUT.</p>
                <p>Pour l'activer, cliquez sur le lien ci-dessous. Vous devrez indiquer le nom du département, la ville et choisir un mot de passe :</p>
                <p><a href='{$link}'>{$link}</a></p>
                <p>Ce lien est personnel et valable {$hours} heures. Une fois ces informations saisies, votre compte sera activé par un administrateur.</p>
                <p>Si vous n'êtes pas concerné par ce message, ignorez simplement cet e-mail.</p>
                <p>L'équipe SAE</p>";
            $mail->AltBody = "Bonjour,\n\nUn compte a été créé pour votre département sur la Bourse d'échange de l'IUT.\n\n"
                . "Pour l'activer, ouvrez le lien ci-dessous. Vous devrez indiquer le nom du département, la ville et choisir un mot de passe :\n{$link}\n\n"
                . "Ce lien est personnel et valable {$hours} heures. Une fois ces informations saisies, votre compte sera activé par un administrateur.\n\n"
                . "Si vous n'êtes pas concerné par ce message, ignorez simplement cet e-mail.\n\nL'équipe SAE";

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mail de création de compte département non envoyé : ' . $mail->ErrorInfo);
            return false;
        }
    }
}
