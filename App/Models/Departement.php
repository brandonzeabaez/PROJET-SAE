<?php
/**
 * @file Departement.php
 * @brief Modèle représentant les comptes des départements de l'IUT.
 *
 * Il gère la création des comptes par un administrateur, la première
 * connexion via le lien unique envoyé au secrétariat, ainsi que
 * l'activation et la suppression des comptes.
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
 * @brief Modèle représentant les comptes des départements de l'IUT.
 *
 * Les jetons des liens de première connexion sont stockés hachés (SHA-256) :
 * même en cas de fuite de la base, les liens ne sont pas réutilisables.
 */
final class Departement extends Model
{
    /** @var int Durée de validité du lien de première connexion, en heures. */
    public const SETUP_LINK_HOURS = 72;

    /**
     * @brief Crée un compte département en attente de première connexion.
     *
     * @param string $email Adresse e-mail du secrétariat du département.
     * @param string $token Jeton en clair envoyé dans le lien (seul son hash est stocké).
     */
    public function create(string $email, string $token): void
    {
        $sql = 'INSERT INTO Departement(email_secretariat, setup_token, setup_expire) '
            . 'VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL ' . self::SETUP_LINK_HOURS . ' HOUR))';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->bindValue('token', hash('sha256', $token), PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * @brief Liste les comptes départements pour le panel administrateur.
     *
     * Le mot de passe n'est jamais sélectionné : seul un indicateur
     * `configure` indique si la première connexion a été faite.
     *
     * @return array<int, array<string, mixed>> Liste des comptes, du plus récent au plus ancien.
     */
    public function findAll(): array
    {
        $sql = 'SELECT id_departement, email_secretariat, nom_departement, ville, actif, '
            . '(mot_de_passe IS NOT NULL) AS configure, (setup_expire < NOW()) AS lien_expire, date_creation '
            . 'FROM Departement ORDER BY date_creation DESC';
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @brief Recherche un département à partir de l'e-mail du secrétariat (connexion).
     *
     * @param string $email Adresse e-mail à tester.
     * @return array<string, mixed>|null Données du département si trouvé, sinon null.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id_departement, email_secretariat, mot_de_passe, nom_departement, actif '
            . 'FROM Departement WHERE email_secretariat = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $departement = $stmt->fetch(PDO::FETCH_ASSOC);

        return $departement === false ? null : $departement;
    }

    /**
     * @brief Vérifie qu'un lien de première connexion existe et n'est pas expiré.
     *
     * @param string $token Jeton en clair reçu via le lien.
     * @return array<string, mixed>|null Données du département si le lien est valide.
     */
    public function findBySetupToken(string $token): ?array
    {
        $sql = 'SELECT id_departement, email_secretariat FROM Departement '
            . 'WHERE setup_token = :token AND setup_expire > NOW() AND mot_de_passe IS NULL LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', hash('sha256', $token), PDO::PARAM_STR);
        $stmt->execute();
        $departement = $stmt->fetch(PDO::FETCH_ASSOC);

        return $departement === false ? null : $departement;
    }

    /**
     * @brief Enregistre les informations saisies à la première connexion et invalide le lien.
     *
     * @param int $id Identifiant du département.
     * @param string $nom Nom du département.
     * @param string $ville Ville du département.
     * @param string $hash Mot de passe hashé avec PASSWORD_DEFAULT.
     */
    public function completeSetup(int $id, string $nom, string $ville, string $hash): void
    {
        $sql = 'UPDATE Departement SET nom_departement = :nom, ville = :ville, mot_de_passe = :hash, '
            . 'setup_token = NULL, setup_expire = NULL WHERE id_departement = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue('ville', $ville, PDO::PARAM_STR);
        $stmt->bindValue('hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @brief Active ou désactive un compte département.
     *
     * Seul un compte dont la première connexion a été faite peut être activé.
     *
     * @param int $id Identifiant du département.
     * @param bool $actif true pour activer, false pour désactiver.
     * @return bool true si le compte a été modifié.
     */
    public function setActive(int $id, bool $actif): bool
    {
        $sql = 'UPDATE Departement SET actif = :actif WHERE id_departement = :id AND mot_de_passe IS NOT NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('actif', $actif, PDO::PARAM_BOOL);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * @brief Supprime définitivement un compte département.
     *
     * @param int $id Identifiant du département.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM Departement WHERE id_departement = :id');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
