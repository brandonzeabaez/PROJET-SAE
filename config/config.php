<?php

/**
 * Placé hors de public/, ce fichier n'est pas accessible depuis le navigateur.
 * Les identifiants réels devront être sortis dans un fichier non versionné.
 */

declare(strict_types=1);

// return dans un fichier inclus : la valeur est renvoyée à l'instruction require
// qui l'a chargé. C'est ce qui permet d'écrire $config = require 'config.php';
return [
    'database' => [
        // localhost : la base tourne sur la même machine que PHP.
        'host'     => 'localhost',
        // 3306 est le port par défaut de MySQL et MariaDB.
        'port'     => 3306,
        // Nom de la base de données à utiliser.
        'name'     => 'projet_sae',
        // root est l'utilisateur administrateur par défaut en local. À remplacer
        // par un compte aux droits limités sur l'hébergement.
        'user'     => 'root',
        // Vide par défaut sous XAMPP en local. À renseigner en production.
        'password' => '',
        // utf8mb4 et non utf8 : seul utf8mb4 gère les caractères sur 4 octets,
        // donc les emojis et certains caractères asiatiques.
        'charset'  => 'utf8mb4',
    ],
];
