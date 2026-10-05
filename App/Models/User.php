<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;


final class User extends Model
{
    // Cherche user  avec son email  renvoie null s'il existe pas
    public function findByEmail(string $email): ?array
    {
        // email  remplacé par la vraie valeur, bloque l'injection SQL
        $sql = 'SELECT id_user, email, mot_de_passe FROM User WHERE email = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // fetch renvoie false si rien n'est trouvé on le transforme en null.
        return $user === false ? null : $user;
    }

    // Enregistre  code secret 1 heure
    public function saveResetToken(int $id, string $token): void
    {
        $sql = 'UPDATE User SET reset_token = :token, reset_expire = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id_user = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Cherche l'user qui a ce code, si code  pas expiré
    public function findByResetToken(string $token): ?array
    {
        $sql = 'SELECT id_user FROM User WHERE reset_token = :token AND reset_expire > NOW() LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    // Change le mdp et efface le code ,  lien sert une fois
    public function updatePassword(int $id, string $hash): void
    {
        $sql = 'UPDATE User SET mot_de_passe = :hash, reset_token = NULL, reset_expire = NULL WHERE id_user = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}