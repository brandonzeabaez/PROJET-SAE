<main class="login-card">
    <h1>Nouveau mot de passe</h1>

    <?php // message d'erreur seulement s'il y en a un ?>
    <?php if ($erreur !== '') : ?>
        <p role="alert"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <?php // formulaire seulement si le lien est valide ?>
    <?php if ($token !== '') : ?>
        <form action="/reinitialiser?token=<?= htmlspecialchars($token) ?>" method="post">
            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password"
                       minlength="8" autocomplete="new-password" required>
            </div>

            <div class="field">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       minlength="8" autocomplete="new-password" required>
            </div>

            <button class="button" type="submit">Changer le mot de passe</button>
        </form>
    <?php endif; ?>

    <p class="login-links"><a href="/connexion">Retour à la connexion</a></p>
</main>
