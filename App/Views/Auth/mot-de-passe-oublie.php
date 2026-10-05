<main class="login-card">
    <h1>Mot de passe oublié</h1>
    <p>Entrez votre adresse e-mail : vous recevrez un lien valable 1 heure pour choisir un nouveau mot de passe.</p>
    
    <?php // message affiché après l'envoi du formulaire ?>
    <?php if ($message !== '') : ?>
        <p role="status"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php // envoie l'email en POST vers /mot-de-passe-oublie ?>
    <form action="/mot-de-passe-oublie" method="post">
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email"
                   placeholder="prenom.nom@example.com"
                   autocomplete="email" required>
        </div>

        <button class="button" type="submit">Recevoir le lien</button>
    </form>

    <p class="login-links"><a href="/connexion">Retour à la connexion</a></p>
</main>
