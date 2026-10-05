<section class="login-card">
    <h1>Connexion</h1>

    <?php // user connecté on affiche son email ?>
    <?php if (isset($_SESSION['user_email'])) : ?>

        <p>Vous êtes connecté en tant que <?= htmlspecialchars($_SESSION['user_email']) ?>.</p>
        <p><a href="/deconnexion">Se déconnecter</a></p>

    <?php else : ?>

        <?php // message d'erreur seulement s'il y en a un ?>
        <?php if ($error !== '') : ?>
            <p role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php // envoie email et mdp en POST vers /connexion ?>
        <form action="/connexion" method="post">
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <?php // htmlspecialchars bloque les failles XSS ?>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($submittedEmail) ?>"
                       placeholder="prenom.nom@example.com"
                       autocomplete="email" required>
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <?php // pas de minlength : on vérifie juste que le mdp correspond ?>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <button class="button" type="submit">Se connecter</button>
        </form>

        <p class="login-links">
            <a href="/mot-de-passe-oublie">Mot de passe oublié ?</a><br>
            Pas encore de compte ? <a href="/inscription">Inscrivez-vous</a>
        </p>

    <?php endif; ?>
</section>

