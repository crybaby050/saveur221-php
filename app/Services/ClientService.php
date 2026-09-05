<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EmailDejaUtiliseException;
use App\Exceptions\MotDePasseInvalideException;
use App\Models\Client;
use App\Repositories\ClientRepository;

/*
 * Applique les règles métier liées aux clients : inscription, mise à jour
 * de profil, changement de mot de passe. Contrairement au Java Console qui
 * ne fait que lire les clients, ce service porte l'intégralité de leur
 * cycle de vie.
 */
final class ClientService
{
    private const LONGUEUR_MIN_MOT_DE_PASSE = 6;

    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * Retourne tous les clients inscrits.
     *
     * @return Client[] Liste de tous les clients
     */
    public function listerClients(): array
    {
        return $this->clientRepository->trouverTous();
    }

    /**
     * Récupère un client par son identifiant.
     *
     * @param int $id Identifiant du client recherché
     * @return Client|null Le client trouvé, ou null s'il n'existe pas
     */
    public function consulterClient(int $id): ?Client
    {
        return $this->clientRepository->trouverParId($id);
    }

    /**
     * Recherche les clients dont le nom ou le prénom contient le mot-clé
     * fourni — utilisée notamment côté administrateur.
     *
     * @param string $motCle Fragment de nom ou prénom recherché
     * @return Client[] Clients correspondants
     */
    public function rechercherClient(string $motCle): array
    {
        return $this->clientRepository->rechercherParNom($motCle);
    }

    /**
     * Inscrit un nouveau client, après avoir vérifié l'unicité de son
     * email et la longueur minimale de son mot de passe. Le mot de passe
     * est haché avant d'être transmis au repository : ce dernier ne
     * manipule jamais de mot de passe en clair.
     *
     * @param string      $nom         Nom du client
     * @param string      $prenom      Prénom du client
     * @param string      $email       Email du client, doit être unique
     * @param string      $motDePasse  Mot de passe en clair, sera haché ici
     * @param string|null $telephone   Téléphone facultatif
     * @param string|null $adresse     Adresse facultative
     * @return Client Le client créé
     *
     * @throws EmailDejaUtiliseException si l'email est déjà utilisé
     * @throws MotDePasseInvalideException si le mot de passe est trop court
     */
    public function inscrire(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        ?string $telephone = null,
        ?string $adresse = null,
    ): Client {
        if ($this->clientRepository->trouverParEmail($email) !== null) {
            throw new EmailDejaUtiliseException("Cet email est déjà utilisé : {$email}");
        }

        if (strlen($motDePasse) < self::LONGUEUR_MIN_MOT_DE_PASSE) {
            throw new MotDePasseInvalideException(
                'Le mot de passe doit contenir au moins ' . self::LONGUEUR_MIN_MOT_DE_PASSE . ' caractères.'
            );
        }

        $motDePasseHache = password_hash($motDePasse, PASSWORD_BCRYPT);

        $client = new Client(0, $nom, $prenom, $email, $motDePasseHache, $telephone, $adresse);

        return $this->clientRepository->creer($client);
    }

    /**
     * Modifie les informations de profil d'un client existant, sans
     * toucher à son mot de passe. Voir changerMotDePasse() pour cette
     * opération, traitée séparément pour éviter d'écraser un hash par
     * erreur lors d'une simple mise à jour de coordonnées.
     *
     * @param int         $id        Identifiant du client à modifier
     * @param string      $nom       Nouveau nom
     * @param string      $prenom    Nouveau prénom
     * @param string      $email     Nouvel email
     * @param string|null $telephone Nouveau téléphone
     * @param string|null $adresse   Nouvelle adresse
     *
     * @throws \InvalidArgumentException si le client n'existe pas
     */
    public function modifierProfil(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        ?string $telephone,
        ?string $adresse,
    ): void {
        $client = $this->trouverOuLever($id);

        $client->setNom($nom);
        $client->setPrenom($prenom);
        $client->setEmail($email);
        $client->setTelephone($telephone);
        $client->setAdresse($adresse);

        $this->clientRepository->mettreAJour($client);
    }

    /**
     * Change le mot de passe d'un client, après vérification de la
     * longueur minimale requise.
     *
     * @param int    $id            Identifiant du client concerné
     * @param string $nouveauMotDePasse Nouveau mot de passe en clair
     *
     * @throws MotDePasseInvalideException si le mot de passe est trop court
     * @throws \InvalidArgumentException si le client n'existe pas
     */
    public function changerMotDePasse(int $id, string $nouveauMotDePasse): void
    {
        if (strlen($nouveauMotDePasse) < self::LONGUEUR_MIN_MOT_DE_PASSE) {
            throw new MotDePasseInvalideException(
                'Le mot de passe doit contenir au moins ' . self::LONGUEUR_MIN_MOT_DE_PASSE . ' caractères.'
            );
        }

        $client = $this->trouverOuLever($id);
        $client->setMotDePasse(password_hash($nouveauMotDePasse, PASSWORD_BCRYPT));

        $this->clientRepository->mettreAJour($client);
    }

    /**
     * Récupère un client par son identifiant ou lève une exception s'il
     * n'existe pas.
     *
     * @param int $id Identifiant du client recherché
     * @return Client Le client trouvé
     *
     * @throws \InvalidArgumentException si aucun client ne correspond
     */
    private function trouverOuLever(int $id): Client
    {
        $client = $this->clientRepository->trouverParId($id);

        if ($client === null) {
            throw new \InvalidArgumentException("Client introuvable avec l'id {$id}.");
        }

        return $client;
    }
}