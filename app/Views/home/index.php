<?php

declare(strict_types=1);

// Cette vue n'a aucune donnée à traiter : elle n'affiche que du texte fixe.
// Aucun htmlspecialchars n'est donc nécessaire, rien ne vient de l'extérieur.

?>
<?php // <main> désigne le contenu principal de la page. Le <head>, le menu et le
      // pied de page sont fournis par le gabarit, pas par cette vue. ?>
<main>
    <?php // Un seul <h1> par page : c'est le titre principal, utile au
          // référencement et à la navigation par lecteur d'écran. ?>
    <h1>Hello World !</h1>

    <p>
        Cette page est servie par le routeur : aucun fichier nommé
        <?php // <code> indique que le texte est un élément de code. ?>
        « index » n'existe dans le dossier <code>public/</code>.
    </p>
</main>
