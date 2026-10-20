<?php
namespace App\Models;

use App\Core\Model;

/**
 * @brief Modèle de gestion de l'inscription des utilisateurs.
 *
 * Cette classe enregistre les informations soumises par le formulaire
 * d'inscription dans la table `User` de la base de données.
 */
class RegisterModel extends Model
{
    /** @var string Requête SQL d'insertion d'un nouvel utilisateur. */
    private string $query = 'INSERT INTO User(email, mot_de_passe, nom, prenom, pays, email_verifie, verification_token, verification_expire) '
        . 'VALUES (:email, :mot_de_passe, :nom, :prenom, :pays, 0, :verification_token, DATE_ADD(NOW(), INTERVAL 24 HOUR))';

    /** @var array<string, mixed> Données à insérer en base. */
    private array $registrationData;

    /**
     * @brief Constructeur du modèle d'inscription.
     *
     * @param array<string, mixed> $registrationData Tableau associatif contenant les informations de l'utilisateur.
     */
    public function __construct(array $registrationData)
    {
        parent::__construct();
        $this->registrationData = $registrationData;
    }

    /**
     * @brief Envoie les données du formulaire vers la base de données.
     */
    public function sendFormToDB(): void
    {
        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->registrationData);
    }
}
