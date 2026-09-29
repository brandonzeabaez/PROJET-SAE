<?php

declare(strict_types=1);

namespace App\Models;

// Importe la classe mère, située dans un autre espace de noms.
use App\Core\Model;

// extends : User hérite de Model, donc dispose de sa méthode db().
final class User extends Model
{
    // ?array en retour : la méthode renvoie un tableau ou null.
    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        // prepare envoie le squelette de la requête à MySQL sans les valeurs.
        // :email est un marqueur nommé, remplacé plus tard par la valeur réelle.
        // L'e-mail n'étant jamais collé dans le texte de la requête, il ne peut
        // pas en modifier le sens : c'est ce qui bloque l'injection SQL.
        // LIMIT 1 arrête la recherche au premier résultat.
        $statement = $this->db()->prepare(
            'SELECT id_user, email, mot_de_passe FROM User WHERE email = :email LIMIT 1'
        );

        // execute fournit les valeurs des marqueurs et lance la requête.
        $statement->execute(['email' => $email]);

        // fetch récupère la ligne suivante du résultat, ou false s'il n'y en a plus.
        $user = $statement->fetch();

        // Opérateur ternaire : condition ? valeur si vrai : valeur si faux.
        // On convertit le false de PDO en null, plus explicite pour l'appelant.
        return $user === false ? null : $user;
    }

    public function create(string $email, string $password): int
    {
        // INSERT ajoute une ligne. Mêmes marqueurs nommés que ci-dessus.
        $statement = $this->db()->prepare(
            'INSERT INTO User (email, mot_de_passe) VALUES (:email, :mot_de_passe)'
        );

        $statement->execute([
            'email' => $email,
            // password_hash calcule une empreinte à sens unique du mot de passe :
            // on ne peut pas revenir en arrière. Elle inclut un sel aléatoire,
            // différent à chaque appel, ce qui rend inutilisables les tables de
            // correspondance précalculées. PASSWORD_DEFAULT laisse PHP choisir
            // le meilleur algorithme disponible, et suivra ses évolutions.
            'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // lastInsertId renvoie l'identifiant auto-incrémenté de la ligne créée.
        // (int) le convertit en entier, car PDO le renvoie sous forme de chaîne.
        return (int) $this->db()->lastInsertId();
    }
}