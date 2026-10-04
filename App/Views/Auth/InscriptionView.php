<?php
namespace App\Views\Auth;
use App\Content\Enums\countriesEnum;
    class InscriptionView {
        function showForm(?string $error = null): void {
            ob_start();
            echo '<div class="card form-card">';
            if (isset($error)) {
                echo '<div><p>' . $error . '</p></div>';
            }
            echo '<form method="POST" action="/inscription">';
            echo '<div><label> nom </label><input type="text" name="nom"></div>';
            echo '<div><label> prenom </label><input type="text" name="prenom"></div>';
            echo '<div><select>';
            echo '<option value="">-- Choisir un pays --</option>';

            foreach(countriesEnum::cases() as $country) {
                echo '<option value="' . $country->value . '">' . $country->name . '</option>';
            }

            echo '</select></div>';
            echo '<div><label>Email</label><input type="email" name="email"></div>';
            echo '<div><label>Mot de passe</label><input type="password" name="password"></div>';
            echo '<div><label>Confirmation mot de passe</label><input type="password" name="password_confirmation"></div>';
            echo '<div><button type="submit" >send</button></div>';
            echo '</form>';
            echo '</div>';

            $content = ob_get_clean();
            $title = 'Inscription';
            $description = 'Création de compte';
            require __DIR__ . '/../layout.php';


        }
    }
