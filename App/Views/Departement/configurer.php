<?php
/**
 * Formulaire de première connexion d'un compte département.
 *
 * @var string $error
 * @var string $token
 * @var string $email
 * @var array<string, string> $old
 */
?>
<section class="login-card">
    <h1>Première connexion</h1>

    <?php // message erreur s'il y en a un ?>
    <?php if ($error !== '') : ?>
        <p role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php // affiche le formulaire seulement si le lien est valide ?>
    <?php if ($token !== '') : ?>
        <p>Compte du secrétariat : <strong><?= htmlspecialchars($email) ?></strong></p>
        <p>Pour des raisons de sécurité, vous devez choisir votre propre mot de passe.</p>

        <?php // le code reste dans l'adresse pour savoir de quel compte il s'agit à l'envoi ?>
        <form action="/departement/configurer?token=<?= htmlspecialchars($token) ?>" method="post">
            <div class="field">
                <label for="nom_departement">Nom du département</label>
                <input type="text" id="nom_departement" name="nom_departement" maxlength="100"
                       value="<?= htmlspecialchars($old['nom_departement'] ?? '') ?>"
                       placeholder="Informatique" required>
            </div>

            <div class="field">
                <label for="ville">Ville</label>
                <input type="text" id="ville" name="ville" maxlength="100"
                       value="<?= htmlspecialchars($old['ville'] ?? '') ?>"
                       placeholder="Aix-en-Provence" autocomplete="address-level2" required>
            </div>

            <p id="password-requirements">8 caractères minimum, avec une minuscule, une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &amp;).</p>

            <div class="field">
                <label for="password">Mot de passe</label>
                <?php // vérif navigateur, le serveur revérifie (sécurité) ?>
                <input type="password" id="password" name="password"
                       minlength="8" autocomplete="new-password" required
                       aria-describedby="password-requirements"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z0-9@$!%*?&]{8,}"
                       title="8 caractères minimum, avec une minuscule, une majuscule, un chiffre et un caractère spécial (@ $ ! % * ? &amp;)">
            </div>

            <div class="field">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       minlength="8" autocomplete="new-password" required>
            </div>

            <button class="button" type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>

    <p class="login-links">
        <a href="/connexion">Retour à la connexion</a>
    </p>
</section>
