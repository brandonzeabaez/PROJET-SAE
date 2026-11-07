<section class="login-card">
    <h1>Nouveau mot de passe</h1>

    <?php // message erreur  s'il y en a un ?>
    <?php if ($error !== '') : ?>
        <p role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php // affiche formulaire si le lien est valide ?>
    <?php if ($token !== '') : ?>
        <p>8 caractères minimum, avec une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &amp;).</p>

        <?php // code de l'URL gardé dans un champ caché pour l'envoi ?>
        <form action="/mot-de-passe-oublie" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <?php //  navigateur propose un mdp solide ?>
                <?php // vérif navigateur, le serveur revérifie (sécurité) ?>
                <input type="password" id="password" name="password"
                       minlength="8" autocomplete="new-password" required
                       pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&]).{8,}"
                       title="8 caractères minimum, avec une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &amp;)">
            </div>

            <div class="field">
                <?php // confirmation du mdp  ?>
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       minlength="8" autocomplete="new-password" required>
            </div>

            <button class="button" type="submit">Changer le mot de passe</button>
        </form>
    <?php endif; ?>

    <?php // lien expiré , user peut en redemander un ?>
    <p class="login-links">
        <a href="/mot-de-passe-oublie">Demander un nouveau lien</a><br>
        <a href="/connexion">Retour à la connexion</a>
    </p>
</section>