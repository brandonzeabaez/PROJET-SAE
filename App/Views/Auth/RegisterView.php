<?php
namespace App\Views\Auth;
use App\Content\Enums\CountriesEnum;

class RegisterView {
    public function showForm(?string $error = null, array $old = [], array $passwordRequirements = []): void {
        // Récupération et sécurisation des anciennes valeurs
        $nom    = htmlspecialchars($old['nom'] ?? '');
        $prenom = htmlspecialchars($old['prenom'] ?? '');
        $email  = htmlspecialchars($old['email'] ?? '');
        $pays   = $old['pays'] ?? '';

        ob_start();
        echo '<div class="card form-card">';
        echo '<h1>Inscription</h1>';
        if (isset($error)) {
            echo '<div><p>' . htmlspecialchars($error) . '</p></div>';
        }
        echo '<form method="POST" action="/inscription">';

        echo '<div><label for="nom">Nom</label>';
        echo '<input type="text" id="nom" name="nom" value="' . $nom . '" required></div>';

        echo '<div><label for="prenom">Prénom</label>';
        echo '<input type="text" id="prenom" name="prenom" value="' . $prenom . '" required></div>';

        echo '<div><label for="pays">Pays</label>';
        echo '<select id="pays" name="pays" required>';
        echo '<option value=""> Choisir un pays </option>';

        foreach (CountriesEnum::cases() as $country) {
            // Si le pays de la boucle correspond à celui qui avait été choisi, on ajoute "selected"
            $selected = ($pays === $country->value) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($country->value) . '"' . $selected . '>' . htmlspecialchars($country->name) . '</option>';
        }

        echo '</select></div>';

        echo '<div><label for="email">Email</label>';
        echo '<input type="email" id="email" name="email" value="' . $email . '" required></div>';

        echo '<div><label for="password">Mot de passe</label>';
        echo '<input type="password" id="password" name="password" aria-describedby="password-requirements" required>';

        // Affichage des prérequis du mot de passe
        if (!empty($passwordRequirements)) {
            echo '<div id="password-requirements">';
            echo '<p>Le mot de passe doit contenir :</p>';
            echo '<ul>';
            foreach ($passwordRequirements as $requirement) {
                echo '<li>' . htmlspecialchars($requirement) . '</li>';
            }
            echo '</ul></div>';
        }
        echo '</div>';

        echo '<div><label for="password_confirmation">Confirmation mot de passe</label>';
        echo '<input type="password" id="password_confirmation" name="password_confirmation" required></div>';

        echo '<div><button type="submit">S\'inscrire</button></div>';
        echo '</form>';
        echo '</div>';

        $content = ob_get_clean();
        $title = 'Inscription';
        $description = 'Création de compte';
        require __DIR__ . '/../layout.php';
    }
}