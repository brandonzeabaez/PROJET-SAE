<?php
/**
 * @file Departement.php
 * @brief Modèle représentant les comptes des départements de l'IUT.
 *
 * Il gère la création et la modification des comptes par un administrateur,
 * la première connexion via le lien unique envoyé au secrétariat, ainsi que
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
 * Le mot de passe n'est jamais renvoyé au panel administrateur.
 */
final class Departement extends Model
{
    /** @var int Durée de validité du lien de première connexion, en heures. */
    public const SETUP_LINK_HOURS = 72;

    /** @var string Colonnes affichées dans le panel (jamais le mot de passe). */
    private const ADMIN_COLUMNS = 'id_departement, email_secretariat, nom_departement, ville, actif, '
        . '(mot_de_passe IS NOT NULL) AS configure, (setup_expire < NOW()) AS lien_expire, date_creation';

    /** @var string Filtre de recherche sur le nom, l'e-mail ou la ville. */
    private const SEARCH_FILTER = '(nom_departement LIKE :q1 OR email_secretariat LIKE :q2 OR ville LIKE :q3)';

    /**
     * @brief Crée un compte département en attente de première connexion.
     *
     * @param string $nom Nom du département (unique).
     * @param string $email Adresse e-mail partagée du secrétariat (unique).
     * @param string $token Jeton en clair envoyé dans le lien (seul son hash est stocké).
     */
    public function create(string $nom, string $email, string $token): void
    {
        $sql = 'INSERT INTO Departement(nom_departement, email_secretariat, setup_token, setup_expire) '
            . 'VALUES (:nom, :email, :token, DATE_ADD(NOW(), INTERVAL ' . self::SETUP_LINK_HOURS . ' HOUR))';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->bindValue('token', hash('sha256', $token), PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * @brief Compte les comptes départements correspondant à une recherche.
     *
     * @param string $search Texte recherché (vide = tous les comptes).
     * @return int Nombre de comptes trouvés.
     */
    public function count(string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM Departement' . ($search !== '' ? ' WHERE ' . self::SEARCH_FILTER : '');
        $stmt = $this->pdo->prepare($sql);
        $this->bindSearch($stmt, $search);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * @brief Liste une page de comptes départements pour le panel administrateur.
     *
     * @param string $search Texte recherché (vide = tous les comptes).
     * @param int $limit Nombre de comptes par page.
     * @param int $offset Nombre de comptes à sauter (pagination).
     * @return array<int, array<string, mixed>> Comptes triés par nom de département.
     */
    public function findPage(string $search, int $limit, int $offset): array
    {
        $sql = 'SELECT ' . self::ADMIN_COLUMNS . ' FROM Departement'
            . ($search !== '' ? ' WHERE ' . self::SEARCH_FILTER : '')
            . ' ORDER BY nom_departement LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        $this->bindSearch($stmt, $search);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @brief Recherche un compte par son identifiant (formulaire de modification).
     *
     * @param int $id Identifiant du département.
     * @return array<string, mixed>|null Données du compte (sans mot de passe) si trouvé.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::ADMIN_COLUMNS . ' FROM Departement WHERE id_departement = :id LIMIT 1');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $departement = $stmt->fetch(PDO::FETCH_ASSOC);

        return $departement === false ? null : $departement;
    }

    /**
     * @brief Met à jour le nom et l'e-mail partagé d'un compte.
     *
     * @param int $id Identifiant du département.
     * @param string $nom Nouveau nom du département.
     * @param string $email Nouvelle adresse e-mail partagée.
     */
    public function update(int $id, string $nom, string $email): void
    {
        $sql = 'UPDATE Departement SET nom_departement = :nom, email_secretariat = :email WHERE id_departement = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @brief Remplace le lien de première connexion (l'ancien lien ne fonctionne plus).
     *
     * @param int $id Identifiant du département.
     * @param string $token Nouveau jeton en clair (seul son hash est stocké).
     */
    public function renewSetupToken(int $id, string $token): void
    {
        $sql = 'UPDATE Departement SET setup_token = :token, '
            . 'setup_expire = DATE_ADD(NOW(), INTERVAL ' . self::SETUP_LINK_HOURS . ' HOUR) '
            . 'WHERE id_departement = :id AND mot_de_passe IS NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('token', hash('sha256', $token), PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
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
        $sql = 'SELECT id_departement, email_secretariat, nom_departement FROM Departement '
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
     * @param string $ville Ville (site) du département.
     * @param string $hash Mot de passe hashé avec PASSWORD_DEFAULT.
     */
    public function completeSetup(int $id, string $ville, string $hash): void
    {
        $sql = 'UPDATE Departement SET ville = :ville, mot_de_passe = :hash, '
            . 'setup_token = NULL, setup_expire = NULL WHERE id_departement = :id';
        $stmt = $this->pdo->prepare($sql);
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

    /**
     * @brief Lie le texte recherché aux paramètres du filtre (recherche « contient »).
     *
     * @param \PDOStatement $stmt Requête préparée.
     * @param string $search Texte recherché (rien n'est lié s'il est vide).
     */
    private function bindSearch(\PDOStatement $stmt, string $search): void
    {
        if ($search === '') {
            return;
        }

        // % et _ sont échappés pour être cherchés tels quels
        $pattern = '%' . addcslashes($search, '%_\\') . '%';
        foreach (['q1', 'q2', 'q3'] as $param) {
            $stmt->bindValue($param, $pattern, PDO::PARAM_STR);
        }
    }
}
