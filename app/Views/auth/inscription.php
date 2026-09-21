<?php

declare(strict_types=1);

// Cette vue n'affiche encore aucune donnée : le formulaire est vierge et son
// traitement n'est pas branché. Aucun échappement n'est donc nécessaire ici.

?>
<main class="register-card">
    <h1>Inscription</h1>

    <?php // action : l'URL qui recevra les données. method="post" : les données
          // voyagent dans le corps de la requête, pas dans l'URL. ?>
    <form action="/inscription" method="post">
        <div class="field">
            <?php // for="first-name" doit valoir l'id du champ associé. ?>
            <label for="first-name">Prénom</label>
            <?php
            // type="text" : saisie libre sur une ligne.
            // id : identifiant unique dans la page, cible du <label>.
            // name : clé sous laquelle la valeur arrivera dans $_POST.
            //   Le tiret bas est la convention en PHP, le tiret celle du HTML.
            // autocomplete="given-name" : le navigateur propose le prénom enregistré.
            // maxlength : limite la saisie, ici alignée sur la taille de la colonne.
            // required : le navigateur refuse l'envoi si le champ est vide.
            ?>
            <input
                type="text"
                id="first-name"
                name="first_name"
                placeholder="Prénom"
                autocomplete="given-name"
                maxlength="80"
                required>
        </div>

        <div class="field">
            <label for="last-name">Nom</label>
            <?php // family-name est la valeur normalisée pour un nom de famille. ?>
            <input
                type="text"
                id="last-name"
                name="last_name"
                placeholder="Nom"
                autocomplete="family-name"
                maxlength="80"
                required>
        </div>

        <div class="field">
            <label for="email">Adresse e-mail</label>
            <?php // type="email" fait vérifier le format par le navigateur et
                  // affiche un clavier adapté sur mobile. ?>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="prenom.nom@example.com"
                autocomplete="email"
                maxlength="180"
                required>
        </div>

        <div class="field">
            <label for="password">Mot de passe</label>
            <?php
            // autocomplete="new-password" : le gestionnaire de mots de passe
            //   comprend qu'il s'agit d'une création et propose un mot de passe fort.
            //   Avec "current-password", il tenterait de remplir avec un ancien.
            // aria-describedby relie le champ au texte d'aide qui suit : sans cet
            //   attribut, un lecteur d'écran n'annoncerait pas la contrainte.
            ?>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
                minlength="8"
                required
                aria-describedby="password-hint">
            <?php // L'id de ce <small> est celui cité par aria-describedby. ?>
            <small id="password-hint">8 caractères minimum.</small>
        </div>

        <div class="field">
            <label for="password-confirmation">Confirmation du mot de passe</label>
            <?php // L'égalité des deux mots de passe devra être vérifiée en PHP :
                  // le HTML seul ne sait pas comparer deux champs. ?>
            <input
                type="password"
                id="password-confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                minlength="8"
                required>
        </div>

        <div class="field">
            <?php
            // type="checkbox" : case à cocher.
            // value="1" est la valeur envoyée SI la case est cochée. Si elle ne
            //   l'est pas, la clé 'terms' est totalement absente de $_POST.
            // required n'est qu'un confort : la validation réelle se fait en PHP,
            //   car tout contrôle côté navigateur se contourne.
            ?>
            <input type="checkbox" id="terms" name="terms" value="1" required>
            <label for="terms">
                J'accepte les <a href="#">mentions légales</a>.
            </label>
        </div>

        <button class="button" type="submit">Créer mon compte</button>
    </form>

    <p class="register-links">
        Déjà inscrit ? <a href="/connexion">Connectez-vous</a>
    </p>
</main>
