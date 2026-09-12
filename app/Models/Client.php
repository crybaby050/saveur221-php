<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Représente un client ayant créé un compte sur la plateforme. Contrairement
 * au Java Console qui ne fait que lire cette entité, ce module PHP est
 * responsable de son cycle de vie complet (inscription, modification de
 * profil, changement de mot de passe).
 */
final class Client
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $motDePasse,
        private ?string $telephone,
        private ?string $adresse,
    ) {
    }

    /**
     * Identifiant unique du client en base.
     *
     * @return int Identifiant du client
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Nom de famille du client.
     *
     * @return string Nom du client
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Modifie le nom de famille du client.
     *
     * @param string $nom Nouveau nom
     */
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * Prénom du client.
     *
     * @return string Prénom du client
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * Modifie le prénom du client.
     *
     * @param string $prenom Nouveau prénom
     */
    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * Adresse email du client, utilisée pour la connexion.
     *
     * @return string Email du client
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Modifie l'adresse email du client. L'unicité de l'email est une règle
     * métier vérifiée en amont par le service, pas ici.
     *
     * @param string $email Nouvel email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Mot de passe haché du client. Ne doit jamais être affiché ni journalisé
     * en clair.
     *
     * @return string Hash du mot de passe (format BCrypt)
     */
    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    /**
     * Remplace le hash du mot de passe stocké. Le hachage lui-même doit être
     * effectué par le service appelant, jamais par ce Model.
     *
     * @param string $motDePasse Nouveau hash BCrypt
     */
    public function setMotDePasse(string $motDePasse): void
    {
        $this->motDePasse = $motDePasse;
    }

    /**
     * Numéro de téléphone du client, utile pour le retrouver au comptoir
     * (vente sur place côté Java Console).
     *
     * @return string|null Téléphone du client, ou null si non renseigné
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * Modifie le numéro de téléphone du client.
     *
     * @param string|null $telephone Nouveau numéro, ou null pour l'effacer
     */
    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    /**
     * Adresse postale du client.
     *
     * @return string|null Adresse du client, ou null si non renseignée
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Modifie l'adresse postale du client.
     *
     * @param string|null $adresse Nouvelle adresse, ou null pour l'effacer
     */
    public function setAdresse(?string $adresse): void
    {
        $this->adresse = $adresse;
    }

    /**
     * Reconstruit une instance de Client à partir d'une ligne de résultat
     * PDO (tableau associatif indexé par nom de colonne).
     *
     * @param array $ligne Ligne issue de la table clients
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            nom: $ligne['nom'],
            prenom: $ligne['prenom'],
            email: $ligne['email'],
            motDePasse: $ligne['mot_de_passe'],
            telephone: $ligne['telephone'],
            adresse: $ligne['adresse'],
        );
    }
}