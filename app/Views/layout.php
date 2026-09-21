<?php

/**
 * Gabarit commun à toutes les vues. Le HTML de la vue courante arrive dans $content.
 *
 * @var string $title
 * @var string $description
 * @var string $content
 */

declare(strict_types=1);

// La balise de fermeture ci-dessous quitte le mode PHP : tout ce qui suit est
// envoyé tel quel au navigateur.
//
// Attention : ne jamais écrire une balise de fermeture PHP à l'intérieur d'un
// commentaire sur une seule ligne. Elle refermerait réellement le bloc, et la
// fin du commentaire serait affichée dans la page.
?>
<!DOCTYPE html>
<?php // lang="fr" annonce la langue : utile aux lecteurs d'écran et au référencement. ?>
<html lang="fr">
<head>
    <?php // charset UTF-8 : sans cette ligne, les accents s'affichent de travers. ?>
    <meta charset="UTF-8">
    <?php // viewport : indispensable pour que la page s'adapte aux mobiles. ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    // <?= est un raccourci de <?php echo : il affiche la valeur.
    // htmlspecialchars convertit < > " & en entités HTML, ce qui empêche une
    // donnée d'être interprétée comme du code (faille XSS).
    // ENT_QUOTES échappe aussi les guillemets simples et doubles, nécessaire
    // puisque la valeur est placée à l'intérieur d'un attribut.
    // UTF-8 précise l'encodage, sinon le comportement dépend de la configuration.
    ?>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <?php // Le titre change à chaque page, le suffixe reste commun. ?>
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> – Projet SAÉ</title>
</head>
<body>
    <?php // <header> et <nav> sont des balises sémantiques : elles donnent un sens
          // au contenu, contrairement à un <div> neutre. ?>
    <header>
        <?php // aria-label nomme la zone pour les lecteurs d'écran, qui annoncent
              // « navigation principale » au lieu de « navigation ». ?>
        <nav aria-label="Navigation principale">
            <?php // Une liste, car un menu est bien une liste de liens. ?>
            <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/connexion">Connexion</a></li>
                <li><a href="/inscription">Inscription</a></li>
            </ul>
        </nav>
    </header>

    <?php
    // $content contient le HTML déjà produit par la vue, donc déjà échappé là où
    // il le fallait. On ne le repasse pas dans htmlspecialchars, sinon les balises
    // s'afficheraient en texte brut à l'écran.
    ?>
    <?= $content ?>

    <footer>
        <?php
        // &copy; est l'entité HTML du symbole ©.
        // date('Y') renvoie l'année courante : le pied de page se met à jour seul.
        ?>
        <p>&copy; <?= date('Y') ?> – Projet SAÉ</p>
    </footer>
</body>
</html>
