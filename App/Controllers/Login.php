<?php
/**
 * @file Login.php
 * @brief Contrôleur principal de l'authentification et des mots de passe.
 *
 * Il gère les actions de connexion, déconnexion, mot de passe oublié et
 * réinitialisation du mot de passe.
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
use App\Models\Departement;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * @brief Contrôleur principal de l'authentification et des mots de passe.
 *
 * Il gère les actions de connexion, déconnexion, mot de passe oublié et
 * réinitialisation du mot de passe.
 */
final class Login extends Controller
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

    /**
     * @brief Affiche le formulaire de connexion.
     */
    private function showLoginForm(): void
    {
        // messages gardés en session avant la redirection, affichés une seule fois
        $error = $_SESSION['error'] ?? '';
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['error'], $_SESSION['message']);

        $this->render('Auth/login', [
            'title'       => 'Connexion',
            'description' => 'Connectez-vous à votre espace membre du Projet SAÉ.',
            'error'       => $error,
            'message'     => $message,
        ]);
    }

    /**
     * @brief Vérifie les identifiants saisis et ouvre une session utilisateur.
     */
    private function login(): void
    {
        $email = trim((string) filter_input(INPUT_POST, 'email'));
        $password = (string) filter_input(INPUT_POST, 'password');

        $user = (new User())->findByEmail($email);

        // pas d'utilisateur avec cet email : c'est peut-être le compte d'un département
        if ($user === null) {
            $this->loginDepartement($email, $password);
        }

        $validCredentials = $user !== null && password_verify($password, $user['mot_de_passe']);

        if ($validCredentials && !(bool) $user['email_verifie']) {
            // bons identifiants mais adresse mail pas encore confirmée via le lien reçu
            $_SESSION['error'] = 'Confirmez votre adresse mail avant de vous connecter (vérifiez votre boîte mail).';
        } elseif ($validCredentials) {
            // nouvel identifiant de session : empêche le vol de session
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id_user'];
            $_SESSION['user_email'] = $user['email'];
            // id_hierarchie 2 = administrateur (accès au panel /admin)
            $_SESSION['role'] = (int) $user['id_hierarchie'] === 2 ? 'admin' : 'utilisateur';
        } else {
            // même message si email ou mdp faux : on ne dit pas qui est inscrit
            $_SESSION['error'] = 'Identifiants incorrects.';
        }

        // Post-Redirect-Get : on revient toujours sur /connexion
        $this->redirect('/connexion');
    }

    /**
     * @brief Vérifie les identifiants d'un compte département et ouvre sa session.
     *
     * Le compte doit avoir fait sa première connexion (mot de passe choisi)
     * et avoir été activé par un administrateur.
     *
     * @param string $email Adresse e-mail du secrétariat.
     * @param string $password Mot de passe saisi.
     */
    private function loginDepartement(string $email, string $password): never
    {
        $departement = (new Departement())->findByEmail($email);

        // mot_de_passe est NULL tant que la première connexion n'est pas faite
        $validCredentials = $departement !== null
            && $departement['mot_de_passe'] !== null
            && password_verify($password, $departement['mot_de_passe']);

        if ($validCredentials && !(bool) $departement['actif']) {
            $_SESSION['error'] = 'Votre compte département n\'a pas encore été activé par un administrateur.';
        } elseif ($validCredentials) {
            // nouvel identifiant de session : empêche le vol de session
            session_regenerate_id(true);

            $_SESSION['departement_id'] = (int) $departement['id_departement'];
            $_SESSION['user_email'] = $departement['email_secretariat'];
            $_SESSION['role'] = 'departement';
        } else {
            // même message si email ou mdp faux : on ne dit pas qui est inscrit
            $_SESSION['error'] = 'Identifiants incorrects.';
        }

        $this->redirect('/connexion');
    }

    /**
     * @brief Déconnecte l'utilisateur courant et détruit sa session.
     */
    private function logout(): void
    {
        // vide session et supprime le cookie du navigateur
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
        session_destroy();

        $this->redirect('/Login');
    }

    /**
     * @brief Gère la demande de mot de passe oublié.
     *
     * Si un compte existe pour l'e-mail fourni, un token de sécurité est
     * généré et un e-mail de réinitialisation est envoyé.
     */
    private function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) filter_input(INPUT_POST, 'email'));
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            // email existe crée un code secret et envoie le lien
            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $userModel->saveResetToken((int) $user['id_user'], $token);

                $appUrl = rtrim($_ENV['APP_URL'] ?? 'https://masae2026.alwaysdata.net/', '/');
                $link = $appUrl . '/reinitialiser?token=' . $token;

                error_log("Test identifiants : Utilisateur=[" . $_ENV['MAIL_USERNAME'] . "] MdP longueur=" . strlen($_ENV['MAIL_PASSWORD'] ?? ''));

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['MAIL_USERNAME'];
                    $mail->Password   = $_ENV['MAIL_PASSWORD'];
                    // Utilisation de SMTPS sur le port 465 calquée sur le Register
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    $mail->CharSet    = PHPMailer::CHARSET_UTF8;

                    $mail->setFrom($_ENV['MAIL_USERNAME'], 'Bourse échange');

                    $firstName = $user['prenom'] ?? '';
                    $mail->addAddress($email, $firstName);

                    $firstNameHtml = htmlspecialchars($firstName);
                    $greeting = $firstNameHtml !== '' ? "Bonjour $firstNameHtml," : "Bonjour,";

                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe';
                    $mail->Body    = "<p>{$greeting}</p>
                        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :</p>
                        <p><a href='{$link}'>{$link}</a></p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.</p>
                        <p>L'équipe SAE</p>";

                    $mail->AltBody = "{$greeting}\n\nVous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :\n{$link}\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.\n\nL'équipe SAE";

                    $mail->send();
                } catch (PHPMailerException $e) {
                    error_log('Mail de réinitialisation non envoyé : ' . $mail->ErrorInfo);
                }
            }

            // même message dans tous les cas pour ne pas dire qui est inscrit
            $_SESSION['message'] = 'Si ce compte existe bien, un email a été envoyé.';

            // Post-Redirect-Get : F5 ne renvoie pas un deuxième mail
            $this->redirect('/mot-de-passe-oublie');
        }

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $this->render('Auth/forgotPassword', [
            'title'       => 'Mot de passe oublié',
            'description' => 'Recevez un lien pour choisir un nouveau mot de passe.',
            'message'     => $message,
        ]);
    }

    /**
     * @brief Traite la saisie du nouveau mot de passe via le token reçu par e-mail.
     */
    private function resetPassword(): void
    {
        // code secret dans l'adresse
        $token = (string) filter_input(INPUT_GET, 'token');
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        // lien valide et formulaire envoyé : on vérifie le nouveau mdp
        if ($user !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = (string) filter_input(INPUT_POST, 'password');
            $confirm = (string) filter_input(INPUT_POST, 'password_confirm');

            $error = '';
            if (strlen($password) < 8) {
                $error = 'Le mot de passe doit faire au moins 8 caractères.';
            } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
                // une majuscule, un chiffre et un caractère spécial, comme à l'inscription
                $error = 'Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &).';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            }

            if ($error !== '') {
                $_SESSION['error'] = $error;
                $this->redirect('/reinitialiser?token=' . urlencode($token));
            }

            // on enregistre le mdp haché, jamais en clair
            $userModel->updatePassword((int) $user['id_user'], password_hash($password, PASSWORD_DEFAULT));

            $_SESSION['message'] = 'Mot de passe modifié, vous pouvez vous connecter.';
            $this->redirect('/connexion');
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        // code inconnu ou expiré : pas de formulaire
        if ($user === null) {
            $error = 'Lien invalide ou expiré.';
            $token = '';
        }

        $this->render('Auth/resetPassword', [
            'title'       => 'Nouveau mot de passe',
            'description' => 'Choisissez un nouveau mot de passe.',
            'error'       => $error,
            'token'       => $token,
        ]);
    }
}

