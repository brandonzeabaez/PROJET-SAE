<?php
/**
 * @file User.php
 * @brief Modèle représentant un utilisateur de l'application.
 *
 * Il gère les opérations liées à l'authentification, à la récupération
 * du mot de passe et à la mise à jour des informations de sécurité.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * @brief Modèle représentant un utilisateur de l'application.
 *
 * Il gère les opérations liées à l'authentification, à la récupération
 * du mot de passe et à la mise à jour des informations de sécurité.
 */
final class User extends Model
{
    /**
     * @brief Recherche un utilisateur à partir de son adresse e-mail.
     *
     * @param string $email Adresse e-mail à tester.
     * @return array<string, mixed>|null Données utilisateur si trouvé, sinon null.
     */
    public function findByEmail(string $email): ?array
    {
        // prenom sert à dire « Bonjour {prénom} » dans le mail
        $sql = 'SELECT id_user, email, mot_de_passe, prenom FROM User WHERE email = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    /**
     * @brief Enregistre un jeton de réinitialisation associé à un compte.
     *
     * @param int $id Identifiant utilisateur.
     * @param string $token Jeton de sécurité généré pour la réinitialisation.
     */
    public function saveResetToken(int $id, string $token): void
    {
        $sql = 'UPDATE User SET reset_token = :token, reset_expire = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id_user = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @brief Vérifie qu'un jeton de réinitialisation existe et n'est pas expiré.
     *
     * @param string $token Jeton reçu via le lien de récupération.
     * @return array<string, mixed>|null Données utilisateur associées au token si valides.
     */
    public function findByResetToken(string $token): ?array
    {
        $sql = 'SELECT id_user FROM User WHERE reset_token = :token AND reset_expire > NOW() LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    /**
     * @brief Met à jour le mot de passe d'un utilisateur et invalide le jeton.
     *
     * @param int $id Identifiant utilisateur.
     * @param string $hash Mot de passe hashé avec PASSWORD_DEFAULT.
     */
    public function updatePassword(int $id, string $hash): void
    {
        $sql = 'UPDATE User SET mot_de_passe = :hash, reset_token = NULL, reset_expire = NULL WHERE id_user = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
