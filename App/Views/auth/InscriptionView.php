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
            echo '<form method="POST">';
            foreach (self::FORM_VALUES as $value)  {
                echo '<ul><label>' . $value . '</label><input type=text ></ul>';
            }
            echo '<ul><label>Email</label><input type="email" name="email"></ul>';
            echo '<ul><label>Mot de passe</label><input type="password" name="password"></ul>';
            echo '<ul><label>Confirmation mot de passe</label><input type="password" name="passwordConfirm"></ul>';
            echo '<ul><button type="submit" ></ul>';
            echo '</form>';
             ?>
                </body>
                </html>
                <?php
        }
    }
    $tmp = new InscriptionView();
    $tmp->showForm();
    ?>