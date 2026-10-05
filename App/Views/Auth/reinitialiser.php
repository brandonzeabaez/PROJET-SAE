<main class="login-card">
    <h1>Nouveau mot de passe</h1>

    <?php // message erreur  s'il y en a un ?>
    <?php if ($erreur !== '') : ?>
        
        <p role="alert"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <?php // affiche formulaire si le lien est valide ?>
    <?php if ($token !== '') : ?>
        <p>8 caractères minimum, avec au moins une majuscule et une minuscule.</p>

        <?php // le code reste dans l'adresse pour savoir de quel compte c'est  à l'envoi ?>
        <form action="/reinitialiser?token=<?= htmlspecialchars($token) ?>" method="post">
            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <?php //  navigateur propose un mdp solide ?>
                <input type="password" id="password" name="password"
                       minlength="8" autocomplete="new-password" required>
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
</main>

