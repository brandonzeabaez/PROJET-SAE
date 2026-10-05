<?php
/**
 * @file LoginController.php
 * @brief Contrôleur principal de l'authentification et des mots de passe.
 *
 * Il gère les actions de connexion, déconnexion, mot de passe oublié et
 * réinitialisation du mot de passe.
 *
 * @author Brandon
 * @author Aymen
 * @author Ymen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * @brief Contrôleur principal de l'authentification et des mots de passe.
 *
 * Il gère les actions de connexion, déconnexion, mot de passe oublié et
 * réinitialisation du mot de passe.
 */
final class LoginController extends Controller
{
    /**
     * @brief Traite l'URL demandée et délègue vers la bonne action.
     *
     * Les routes prises en charge sont la connexion, la déconnexion, la
     * récupération de mot de passe et son renouvellement.
     */
    public function execute(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($url === '/deconnexion') {
            $this->logout();
            return;
        }

        if ($url === '/mot-de-passe-oublie') {
            $this->oublie();
            return;
        }

        if ($url === '/reinitialiser') {
            $this->reinitialiser();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
            return;
        }

        $this->showLoginForm();
    }

    /**
     * @brief Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->render('Auth/login', [
            'title'       => 'Connexion',
            'description' => 'Connectez-vous à votre espace membre du Projet SAÉ.',
        ]);
    }

    /**
     * @brief Vérifie les identifiants saisis et ouvre une session utilisateur.
     */
    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->renderLoginError('Veuillez renseigner votre e-mail et votre mot de passe.', $email);
            return;
        }

        try {
            $user = (new User())->findByEmail($email);
        } catch (PDOException $exception) {
            $this->renderLoginError('Base de données indisponible : ' . $exception->getMessage(), $email);
            return;
        }

        if ($user === null || !password_verify($password, $user['mot_de_passe'])) {
            $this->renderLoginError('Identifiants incorrects.', $email);
            return;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id_user'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_time'] = time();

        $this->redirect('/connexion');
    }

    /**
     * @brief Déconnecte l'utilisateur courant et détruit sa session.
     */
    public function logout(): void
    {
        $_SESSION = [];
        setcookie(session_name(), '', time() - 3600, '/');
        session_destroy();

        $this->redirect('/connexion');
    }

    /**
     * @brief Gère la demande de mot de passe oublié.
     *
     * Si un compte existe pour l'e-mail fourni, un token de sécurité est
     * généré et un e-mail de réinitialisation est envoyé.
     */
    private function oublie(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $userModel->saveResetToken((int) $user['id_user'], $token);

                $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
                $lien = $appUrl . '/reinitialiser?token=' . $token;

                error_log("Test identifiants : Utilisateur=[" . $_ENV['MAIL_USERNAME'] . "] MdP longueur=" . strlen($_ENV['MAIL_PASSWORD'] ?? ''));

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

                    $prenom = $user['prenom'] ?? '';
                    $mail->addAddress($email, $prenom);

                    $prenomHtml = htmlspecialchars($prenom);
                    $salutation = $prenomHtml !== '' ? "Bonjour $prenomHtml," : "Bonjour,";

                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe';
                    $mail->Body    = "<p>{$salutation}</p>
                        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :</p>
                        <p><a href='{$lien}'>{$lien}</a></p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.</p>
                        <p>L'équipe SAE</p>";

                    $mail->AltBody = "{$salutation}\n\nVous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :\n{$lien}\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.\n\nL'équipe SAE";

                    $mail->send();
                } catch (PHPMailerException $e) {
                    error_log('Mail de réinitialisation non envoyé : ' . $mail->ErrorInfo);
                }
            }

            $message = 'Si ce compte existe bien, un email a été envoyé.';
        }

        $this->render('Auth/mot-de-passe-oublie', [
            'title'   => 'Mot de passe oublié',
            'message' => $message,
        ]);
    }

    /**
     * @brief Traite la saisie du nouveau mot de passe via le token reçu par e-mail.
     */
    private function reinitialiser(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if ($user === null) {
            $this->renderReset('Lien invalide ou expiré.', '');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderReset('', $token);
            return;
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if (strlen($password) < 8) {
            $this->renderReset('Le mot de passe doit faire au moins 8 caractères.', $token);
            return;
        }

        if ($password !== $confirm) {
            $this->renderReset('Les mots de passe ne correspondent pas.', $token);
            return;
        }

        $userModel->updatePassword((int) $user['id_user'], password_hash($password, PASSWORD_DEFAULT));
        $this->redirect('/connexion');
    }

    /**
     * @brief Réaffiche le formulaire de connexion avec un message d'erreur.
     *
     * @param string $error Message d'erreur à afficher à l'utilisateur.
     * @param string $email E-mail déjà saisi pour éviter de le retaper.
     */
    private function renderLoginError(string $error, string $email): void
    {
        $this->render('Auth/login', [
            'title'          => 'Connexion',
            'description'    => 'Connectez-vous à votre espace membre du Projet SAÉ.',
            'error'          => $error,
            'submittedEmail' => $email,
        ]);
    }

    /**
     * @brief Affiche le formulaire de nouveau mot de passe avec un message éventuel.
     *
     * @param string $erreur Message de validation ou d'erreur.
     * @param string $token Jeton de sécurité associé à la demande.
     */
    private function renderReset(string $erreur, string $token): void
    {
        $this->render('Auth/reinitialiser', [
            'title'  => 'Nouveau mot de passe',
            'erreur' => $erreur,
            'token'  => $token,
        ]);
    }
}