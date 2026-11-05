<?php
/**
 * @file Departement.php
 * @brief Contrôleur de la première connexion d'un compte département.
 *
 * Le secrétariat arrive ici via le lien unique reçu par e-mail. Le nom du
 * département est choisi par l'administrateur : il doit saisir la ville et
 * choisir son mot de passe.
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

/**
 * @brief Contrôleur de la première connexion d'un compte département.
 *
 * Route : /departement/configurer?token=...
 */
final class Departement extends Controller
{
    /** @var string Expression régulière de validation du mot de passe (identique à l'inscription). */
    private const REGEX_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    /**
     * @brief Sans action précisée, renvoie vers la page de connexion.
     */
    public function execute(): void
    {
        $this->redirect('/connexion');
    }

    /**
     * @brief Affiche et traite le formulaire de première connexion.
     */
    public function configurer(): void
    {
        // code secret dans l'adresse
        $token = (string) filter_input(INPUT_GET, 'token');
        $model = new DepartementModel();
        $departement = $token !== '' ? $model->findBySetupToken($token) : null;

        // lien valide et formulaire envoyé : on vérifie les données
        if ($departement !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ville = trim((string) filter_input(INPUT_POST, 'ville'));
            $password = (string) filter_input(INPUT_POST, 'password');
            $confirm = (string) filter_input(INPUT_POST, 'password_confirm');

            $error = '';
            if ($ville === '' || mb_strlen($ville) > 100) {
                $error = 'La ville est obligatoire (100 caractères maximum).';
            } elseif (!preg_match(self::REGEX_PASSWORD, $password)) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &).';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            }

            if ($error !== '') {
                $_SESSION['error'] = $error;
                // on garde la ville saisie pour ne pas la retaper
                $_SESSION['old'] = ['ville' => $ville];
                $this->redirect('/departement/configurer?token=' . urlencode($token));
            }

            // on enregistre le mdp haché, jamais en clair, et le lien devient inutilisable
            $model->completeSetup((int) $departement['id_departement'], $ville, password_hash($password, PASSWORD_DEFAULT));

            $_SESSION['message'] = 'Informations enregistrées. Vous pourrez vous connecter dès qu\'un administrateur aura activé votre compte.';
            $this->redirect('/connexion');
        }

        $error = $_SESSION['error'] ?? '';
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['error'], $_SESSION['old']);

        // code inconnu, expiré ou déjà utilisé : pas de formulaire
        if ($departement === null) {
            $error = 'Lien invalide ou expiré. Contactez un administrateur pour obtenir un nouveau lien.';
            $token = '';
        }

        $this->render('Departement/configurer', [
            'title'       => 'Première connexion',
            'description' => 'Renseignez les informations de votre département.',
            'error'       => $error,
            'token'       => $token,
            'email'       => $departement['email_secretariat'] ?? '',
            'nom'         => $departement['nom_departement'] ?? '',
            'old'         => $old,
        ]);
    }
}
