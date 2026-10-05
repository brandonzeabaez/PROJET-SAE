<?php
namespace App\Models;

use App\Core\Model;

/**
 * @brief Modèle de gestion de l'inscription des utilisateurs.
 *
 * Cette classe enregistre les informations soumises par le formulaire
 * d'inscription dans la table `User` de la base de données.
 */
class InscriptionModel extends Model
{
    /** @var string Requête SQL d'insertion d'un nouvel utilisateur. */
    private string $query = 'INSERT INTO User(email, mot_de_passe, nom, prenom, pays) VALUES (:email, :mot_de_passe, :nom, :prenom, :pays)';

    /** @var array<string, mixed> Données à insérer en base. */
    private array $dataInscription;

    /**
     * @brief Constructeur du modèle d'inscription.
     *
     * @param array<string, mixed> $dataInscription Tableau associatif contenant les informations de l'utilisateur.
     */
    public function __construct(array $dataInscription)
    {
        parent::__construct();
        $this->dataInscription = $dataInscription;
    }

    /**
     * @brief Envoie les données du formulaire vers la base de données.
     */
    public function sendFormToDB(): void
    {
        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->dataInscription);
    }
}
