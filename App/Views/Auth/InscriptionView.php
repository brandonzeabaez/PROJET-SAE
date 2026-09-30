<?php namespace App\Views\Auth; ?>
<?php
    class InscriptionView {
        const FORM_VALUES = array('nom','prenom','pays');
        function showForm(): void {
            ?>
            <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Mon titre de page</title>
                </head>
                <body>
            <?php
            echo '<form method="POST" action="/inscription">';
            foreach (self::FORM_VALUES as $value)  {
                echo '<div><label>' . $value . '</label><input type="text" name="' . $value . '"></div>';
            }
            echo '<div><label>Email</label><input type="email" name="email"></div>';
            echo '<div><label>Mot de passe</label><input type="password" name="password"></div>';
            echo '<div><label>Confirmation mot de passe</label><input type="password" name="passwordConfirm"></div>';
            echo '<div><button type="submit" >send</button></div>';
            echo '</form>';
             ?>
                </body>
                </html>
                <?php
        }
    }
    ?>