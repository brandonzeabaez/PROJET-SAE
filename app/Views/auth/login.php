<?php

/**
 * @var string      $title
 * @var string|null $error
 * @var string|null $submittedEmail
 */

declare(strict_types=1);

// ??= affecte la valeur uniquement si la variable est absente ou vaut null.
// Nécessaire car le contrôleur n'envoie ces deux variables qu'en cas d'échec.
$error ??= '';
$submittedEmail ??= '';

?>
<main class="login-card">
    <h1>Connexion</h1>

    <?php
    // isset teste si la clé existe et n'est pas null. Sa présence dans $_SESSION
    // signifie que l'utilisateur est connecté.
    // La syntaxe if ( ) : ... endif; remplace les accolades. Elle est préférée
    // dans les vues, car elle reste lisible au milieu du HTML.
    ?>
    <?php if (isset($_SESSION['user_email'])) : ?>

        <p>
            Vous êtes connecté en tant que
            <?php // L'e-mail vient de la base, donc à l'origine d'un utilisateur :
                  // on l'échappe avant de l'afficher. ?>
            <?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') ?>.
        </p>

        <p><a href="/deconnexion">Se déconnecter</a></p>

    <?php // else : la branche exécutée quand l'utilisateur n'est pas connecté. ?>
    <?php else : ?>

        <?php // Le bloc n'apparaît que s'il y a effectivement une erreur à montrer. ?>
        <?php if ($error !== '') : ?>
            <?php // role="alert" fait annoncer le message immédiatement par un
                  // lecteur d'écran, sans attendre que l'utilisateur y arrive. ?>
            <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php
        // action : l'URL qui recevra les données. Le routeur y a associé
        // AuthController::login() pour la méthode POST.
        // method="post" : les données passent dans le corps de la requête. En GET,
        // le mot de passe finirait dans l'URL, l'historique et les logs du serveur.
        ?>
        <form action="/connexion" method="post">
            <div class="field">
                <?php // for doit valoir l'id du champ : cliquer le libellé y place
                      // le curseur, et les lecteurs d'écran l'annoncent. ?>
                <label for="email">Adresse e-mail</label>
                <?php
                // type="email" : clavier adapté sur mobile et contrôle du format.
                // name : la clé sous laquelle la valeur arrive dans $_POST.
                // value : réaffiche la saisie après un échec, échappée car elle
                //   provient du visiteur.
                // placeholder : exemple grisé, qui n'est pas une valeur.
                // autocomplete : autorise le remplissage par le navigateur.
                // required : le navigateur bloque l'envoi si le champ est vide.
                ?>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($submittedEmail, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="prenom.nom@example.com"
                    autocomplete="email"
                    required>
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <?php
                // type="password" masque la saisie à l'écran.
                // Aucun value ici : on ne réaffiche jamais un mot de passe.
                // autocomplete="current-password" indique au gestionnaire de mots
                //   de passe qu'il s'agit d'une connexion, non d'une création.
                // minlength est un confort : la vraie validation est côté serveur.
                ?>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    minlength="8"
                    required>
            </div>

            <?php // type="submit" envoie le formulaire. Un <button> sans type vaut
                  // submit par défaut, mais l'écrire évite toute ambiguïté. ?>
            <button class="button" type="submit">Se connecter</button>
        </form>

        <p class="login-links">
            <?php // href="#" en attendant que la page existe : le lien ne mène nulle part. ?>
            <a href="#">Mot de passe oublié ?</a>
            <?php // <br> force un retour à la ligne. ?>
            <br>
            Pas encore de compte ? <a href="/inscription">Inscrivez-vous</a>
        </p>

    <?php // endif ferme le if ouvert plus haut. ?>
    <?php endif; ?>
</main>
