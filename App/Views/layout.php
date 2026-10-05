<?php

/**
 * Commun pour toutes les vues, le contenu est stocké dans $content
 *
 * @var string $title
 * @var string $description
 * @var string $content
 */

declare(strict_types=1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $description ?>">
    <title><?= $title . ' - Bourse d\'échange ' ?></title>
</head>
<body>
    <header>
        <p><strong>Bourse d'échange</strong></p>
        <nav aria-label="Navigation principale">
            <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/connexion">Connexion</a></li>
                <li><a href="/inscription">Inscription</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>Aix-Marseille Université IUT </p>
        <p>Donner une seconde vie au matériels de l'IUT, simplement et localement.</p>
        <ul>
            <li><a href="/mentions-legales">Mentions légales</a></li>
        </ul>
    </footer>
</body>
</html>
