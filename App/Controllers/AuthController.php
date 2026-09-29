<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
// PDOException est le type d'exception levé par PDO en cas d'erreur.
use PDOException;

final class AuthController extends Controller
{
    public function showLoginForm(): void
    {
        // render est héritée de Controller : elle charge la vue et le gabarit.
        $this->render('auth/login', [
            'title'       => 'Connexion',
            'description' => 'Connectez-vous à votre espace membre du Projet SAÉ.',
        ]);
    }

    public function login(): void
    {
        // $_POST contient les champs du formulaire, indexés par attribut name.
        // ?? '' fournit une chaîne vide si le champ est absent, pour éviter une
        // erreur si quelqu'un soumet une requête sans ce champ.
        // (string) garantit le type, car $_POST peut contenir un tableau.
        // trim retire les espaces au début et à la fin de la saisie.
        $email = trim((string) ($_POST['email'] ?? ''));
        // Pas de trim sur le mot de passe : un espace peut en faire partie.
        $password = (string) ($_POST['password'] ?? '');

        // Validation côté serveur : le required du HTML se contourne facilement.
        // || est le OU logique.
        if ($email === '' || $password === '') {
            $this->renderLoginError('Veuillez renseigner votre e-mail et votre mot de passe.', $email);

            // return arrête la méthode ici, sinon la suite s'exécuterait aussi.
            return;
        }

        // try délimite un bloc où une exception peut survenir.
        try {
            // On instancie le modèle et on l'interroge dans la même expression.
            $user = (new User())->findByEmail($email);
        // catch intercepte l'exception et évite la page blanche.
        } catch (PDOException $exception) {
            // getMessage récupère le texte de l'erreur. À retirer en production :
            // afficher une erreur technique au visiteur renseigne un attaquant.
            $this->renderLoginError('Base de données indisponible : ' . $exception->getMessage(), $email);

            return;
        }

        // password_verify recalcule l'empreinte du mot de passe saisi avec le sel
        // contenu dans l'empreinte stockée, puis compare les deux. La comparaison
        // se fait à temps constant, donc le temps de réponse ne révèle rien.
        // Message identique dans les deux cas : distinguer « e-mail inconnu » de
        // « mot de passe incorrect » permettrait de découvrir qui est inscrit.
        if ($user === null || !password_verify($password, $user['mot_de_passe'])) {
            $this->renderLoginError('Identifiants incorrects.', $email);

            return;
        }

        // session_regenerate_id attribue un nouvel identifiant de session.
        // true supprime l'ancien fichier de session. Appelé au moment précis où
        // l'utilisateur gagne des droits, cela neutralise la fixation de session :
        // un identifiant imposé à la victime avant sa connexion devient inutile.
        session_regenerate_id(true);

        // $_SESSION conserve ces valeurs d'une requête à l'autre. Leur présence
        // sert désormais de preuve que l'utilisateur est connecté.
        $_SESSION['user_id'] = (int) $user['id_user'];
        $_SESSION['user_email'] = $user['email'];

        // Redirection après un POST réussi : sans elle, un rafraîchissement
        // renverrait le formulaire une seconde fois.
        $this->redirect('/connexion');
    }

    public function showRegistrationForm(): void
    {
        $this->render('auth/inscription', [
            'title'       => 'Inscription',
            'description' => 'Créez votre compte membre pour accéder à votre espace personnel.',
        ]);
    }

    public function logout(): void
    {
        // Vide le tableau de session côté serveur.
        $_SESSION = [];

        // Vider la session ne suffit pas : le navigateur garde son cookie. On lui
        // en renvoie un vide avec une date d'expiration passée, ce qui le supprime.
        // session_name donne le nom du cookie de session, PHPSESSID par défaut.
        setcookie(session_name(), '', time() - 3600, '/');

        // session_destroy supprime les données de session sur le serveur.
        session_destroy();

        $this->redirect('/connexion');
    }

    // private : cette méthode est un détail interne de la classe, appelée
    // seulement par login() pour éviter de répéter trois fois le même render.
    private function renderLoginError(string $error, string $email): void
    {
        $this->render('auth/login', [
            'title'          => 'Connexion',
            'description'    => 'Connectez-vous à votre espace membre du Projet SAÉ.',
            'error'          => $error,
            // Réinjecté dans le champ pour que l'utilisateur ne resaisisse pas tout.
            'submittedEmail' => $email,
        ]);
    }
}
