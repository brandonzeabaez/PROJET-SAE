<?php
namespace App\Views\Auth;
use App\Content\Enums\countriesEnum;
    class InscriptionView {
        function showForm(?string $error = null): void {
            ob_start();
            echo '<div class="card form-card">';
            echo '<h1>Inscription</h1>';
            if (isset($error)) {
                echo '<div><p>' . $error . '</p></div>';
            }
            echo '<form method="POST" action="/inscription">';
            echo '<div><label for="nom">Nom</label>';
            echo '<input type="text" id="nom" name="nom" required></div>';
            echo '<div><label for="prenom">Prénom</label>';
            echo '<input type="text" id="prenom" name="prenom" required></div>';
            echo '<div><label for="pays">Pays</label>';
            echo '<select id="pays" name="pays" required>';
            echo '<option value=""> Choisir un pays </option>';

            foreach(countriesEnum::cases() as $country) {
                echo '<option value="' . $country->value . '">' . $country->name . '</option>';
            }

            echo '</select></div>';
            echo '<div><label for="email">Email</label>';
            echo '<input type="email" id="email" name="email" required></div>';
            echo '<div><label for="password">Mot de passe</label>';
            echo '<input type="password" id="password" name="password" required>';
            echo '<div><label for="password_confirmation">Confirmation mot de passe</label></div>';
            echo '<input type="password" id="password_confirmation" name="password_confirmation" required></div>';
            echo '<div><button type="submit" >S\'inscrire</button></div>';
            echo '</form>';
            echo '</div>';

            $content = ob_get_clean();
            $title = 'Inscription';
            $description = 'Création de compte';
            require __DIR__ . '/../layout.php';


        }
    }
