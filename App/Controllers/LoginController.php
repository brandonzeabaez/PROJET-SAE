<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

final class LoginController extends Controller
{
    public function execute(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($url === '/deconnexion') {
            $this->logout();
            return;
        }

        if ($url === '/mot-de-passe-oublie') {
            $this->forgotPassword();
            return;
        }

        if ($url === '/reinitialiser') {
            $this->resetPassword();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
            return;
        }

        $this->showLoginForm();
    }

    private function showLoginForm(): void
    {
        // erreur gardée en session par login(), affichée une seule fois
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        $this->render('Auth/login', [
            'title'       => 'Connexion',
            'description' => 'Connectez-vous à votre espace membre du Projet SAÉ.',
            'error'       => $error,
        ]);
    }

    private function login(): void
    {
        $email = trim((string) filter_input(INPUT_POST, 'email'));
        $password = (string) filter_input(INPUT_POST, 'password');

        $user = (new User())->findByEmail($email);

        if ($user !== null && password_verify($password, $user['mot_de_passe'])) {
            // nouvel identifiant de session : empêche le vol de session
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id_user'];
            $_SESSION['user_email'] = $user['email'];
        } else {
            // même message si email ou mdp faux : on ne dit pas qui est inscrit
            $_SESSION['error'] = 'Identifiants incorrects.';
        }

        $this->redirect('/connexion');
    }

    private function logout(): void
    {
        // vide session et supprime le cookie du navigateur
        $_SESSION = [];
        setcookie(session_name(), '', time() - 3600, '/');
        session_destroy();

        $this->redirect('/connexion');
    }

    private function forgotPassword(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) filter_input(INPUT_POST, 'email'));
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            // email existe crée un code secret et envoie le lien
            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $userModel->saveResetToken((int) $user['id_user'], $token);

                $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
                $link = $appUrl . '/reinitialiser?token=' . $token;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['MAIL_USERNAME'];
                    $mail->Password   = $_ENV['MAIL_PASSWORD'];
                    // Utilisation de SMTPS sur le port 465 calquée sur votre InscriptionController
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
                        <p><a href='{$link}'>{$link}</a></p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.</p>
                        <p>L'équipe SAE</p>";

                    $mail->AltBody = "{$salutation}\n\nVous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :\n{$link}\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.\n\nL'équipe SAE";

                    $mail->send();
                } catch (PHPMailerException $e) {
                    error_log('Mail de réinitialisation non envoyé : ' . $mail->ErrorInfo);
                }
            }

            // même message dans tous les cas pour ne pas dire qui est inscrit
            $message = 'Si ce compte existe bien, un email a été envoyé.';
        }

        $this->render('Auth/forgotPassword', [
            'title'   => 'Mot de passe oublié',
            'message' => $message,
        ]);
    }

    private function resetPassword(): void
    {
        // code secret dans l'adresse
        $token = (string) filter_input(INPUT_GET, 'token');
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        // code inconnu ou expiré
        if ($user === null) {
            $this->renderReset('Lien invalide ou expiré.', '');
            return;
        }

        // pas encore envoyé  on affiche le formulaire
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderReset('', $token);
            return;
        }

        $password = (string) filter_input(INPUT_POST, 'password');
        $confirm = (string) filter_input(INPUT_POST, 'password_confirm');

        if (strlen($password) < 8) {
            $this->renderReset('Le mot de passe doit faire au moins 8 caractères.', $token);
            return;
        }

        // au moins une majuscule et une minuscule, comme à l'inscription
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
            $this->renderReset('Le mot de passe doit contenir une majuscule et une minuscule.', $token);
            return;
        }

        if ($password !== $confirm) {
            $this->renderReset('Les mots de passe ne correspondent pas.', $token);
            return;
        }

        // on enregistre le mdp haché, jamais en clair
        $userModel->updatePassword((int) $user['id_user'], password_hash($password, PASSWORD_DEFAULT));
        $this->redirect('/connexion');
    }

    private function renderReset(string $error, string $token): void
    {
        $this->render('Auth/resetPassword', [
            'title' => 'Nouveau mot de passe',
            'error' => $error,
            'token' => $token,
        ]);
    }
}