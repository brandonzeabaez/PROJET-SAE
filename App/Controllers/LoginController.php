<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class LoginController extends Controller
{
    public function execute(): void
    {
        // adresse demandée  connexion ou deconnexion
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($url === '/deconnexion') {
            $this->logout();
            return;
        }

        // mdp oublié
        if ($url === '/mot-de-passe-oublie') {
            $this->forgotPassword();
            return;
        }

        // lien reçu par email nouveau mdp
        if ($url === '/reinitialiser') {
            $this->resetPassword();
            return;
        }

        // formulaire  envoyé  vérif  connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
            return;
        }

        $this->showLoginForm();
    }

    // affiche le formulaire de connexion, avec une erreur si besoin
    private function showLoginForm(string $error = '', string $email = ''): void
    {
        $this->render('Auth/login', [
            'title'          => 'Connexion',
            'description'    => 'Connectez-vous à votre espace membre du Projet SAÉ.',
            'error'          => $error,
            'submittedEmail' => $email, // pour pas retaper l'email
        ]);
    }

    // Vérifie email et mdp
    private function login(): void
    {
        // On récupère ,donnée que l'user a tapé
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->showLoginForm('Veuillez renseigner votre e-mail et votre mot de passe.', $email);
            return;
        }

        //  cherche l'user dans la base avec son email
        $user = (new User())->findByEmail($email);

        // Email inconnu ou mauvais mdp  même message pour ne pas dire qui est inscrit
        if ($user === null || !password_verify($password, $user['mot_de_passe'])) {
            $this->showLoginForm('Identifiants incorrects.', $email);
            return;
        }

        //  identifiant de session
        session_regenerate_id(true);

        //  enregistre l'user dans la session 
        $_SESSION['user_id'] = (int) $user['id_user'];
        $_SESSION['user_email'] = $user['email'];

        $this->redirect('/connexion');
    }

    // Déconnecte l'user
    private function logout(): void
    {
        // vide session et supprime le cookie du navigateur
        $_SESSION = [];
        setcookie(session_name(), '', time() - 3600, '/');
        session_destroy();

        $this->redirect('/connexion');
    }

    // mdp oublié l'user donne son email
    private function forgotPassword(): void
    {
        $message = '';

        // formulaire envoyé
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            // email existe crée un code secret et envoie le lien
            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $userModel->saveResetToken((int) $user['id_user'], $token);

                $link = 'http://localhost:8000/reinitialiser?token=' . $token;
                mail($email, 'Mot de passe oublié', 'Cliquez sur ce lien : ' . $link);
                error_log($link); 
            }

            // même message dans tous les cas pour ne pas dire qui est inscrit
            $message = 'Si ce compte existe, un email a été envoyé.';
        }

        $this->render('Auth/forgotPassword', [
            'title'   => 'Mot de passe oublié',
            'message' => $message,
        ]);
    }

    // l'user arrive par le lien et choisit un nouveau mdp
    private function resetPassword(): void
    {
        // code secret dans l'adresse
        $token = (string) ($_GET['token'] ?? '');
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

        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        // mdp trop court
        if (strlen($password) < 8) {
            $this->renderReset('Le mot de passe doit faire au moins 8 caractères.', $token);
            return;
        }

        // au moins une majuscule et une minuscule, comme à l'inscription
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
            $this->renderReset('Le mot de passe doit contenir une majuscule et une minuscule.', $token);
            return;
        }

        // les 2 mdp sont différents
        if ($password !== $confirm) {
            $this->renderReset('Les mots de passe ne correspondent pas.', $token);
            return;
        }

        // hache new mdp et l'enregistre
        $userModel->updatePassword((int) $user['id_user'], password_hash($password, PASSWORD_DEFAULT));
        $this->redirect('/connexion');
    }

    // affiche la page nouveau mdp avec un message d'erreur
    private function renderReset(string $error, string $token): void
    {
        $this->render('Auth/resetPassword', [
            'title' => 'Nouveau mot de passe',
            'error' => $error,
            'token' => $token,
        ]);
    }
}