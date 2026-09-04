<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Client;
use Core\Database;

/*
 * Accès aux données de la table clients. Contrairement au Java Console qui
 * ne fait que lire cette table, ce module PHP implémente RepositoryInterface
 * dans son intégralité : c'est ici que sont créés et modifiés les comptes
 * clients (inscription, mise à jour de profil, changement de mot de passe).
 */
final class ClientRepository implements RepositoryInterface
{
    private const COLONNES = 'id, nom, prenom, email, mot_de_passe, telephone, adresse';

    /**
     * Recherche un client par son identifiant.
     *
     * @param int $id Identifiant du client recherché
     * @return Client|null Le client trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Client
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM clients WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Client::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les clients, triés par nom puis prénom.
     *
     * @return Client[] Liste de tous les clients
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM clients ORDER BY nom, prenom'
        );

        return array_map(
            fn(array $ligne) => Client::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Recherche un client par son adresse email exacte, utilisée pour la
     * connexion et pour vérifier l'unicité de l'email à l'inscription.
     *
     * @param string $email Email recherché
     * @return Client|null Le client trouvé, ou null si aucun ne correspond
     */
    public function trouverParEmail(string $email): ?Client
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM clients WHERE email = :email'
        );
        $requete->execute(['email' => $email]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Client::depuisLigne($ligne) : null;
    }

    /**
     * Recherche les clients dont le nom ou le prénom contient le mot-clé
     * fourni, indépendamment de la casse — utilisée notamment pour
     * retrouver un client au comptoir.
     *
     * @param string $motCle Fragment de nom ou prénom recherché
     * @return Client[] Clients correspondants
     */
    public function rechercherParNom(string $motCle): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM clients
             WHERE nom ILIKE :motCle OR prenom ILIKE :motCle
             ORDER BY nom, prenom'
        );
        $requete->execute(['motCle' => '%' . $motCle . '%']);

        return array_map(
            fn(array $ligne) => Client::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Insère un nouveau client en base, lors de son inscription.
     *
     * @param Client $entite Client à créer (id ignoré)
     * @return Client Le client créé, avec son identifiant généré
     */
    public function creer(object $entite): Client
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO clients (nom, prenom, email, mot_de_passe, telephone, adresse)
             VALUES (:nom, :prenom, :email, :motDePasse, :telephone, :adresse)
             RETURNING id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'prenom' => $entite->getPrenom(),
            'email' => $entite->getEmail(),
            'motDePasse' => $entite->getMotDePasse(),
            'telephone' => $entite->getTelephone(),
            'adresse' => $entite->getAdresse(),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Client(
            $id,
            $entite->getNom(),
            $entite->getPrenom(),
            $entite->getEmail(),
            $entite->getMotDePasse(),
            $entite->getTelephone(),
            $entite->getAdresse(),
        );
    }

    /**
     * Met à jour les informations de profil d'un client existant, y
     * compris son mot de passe s'il a été changé.
     *
     * @param Client $entite Client contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE clients
             SET nom = :nom, prenom = :prenom, email = :email,
                 mot_de_passe = :motDePasse, telephone = :telephone, adresse = :adresse
             WHERE id = :id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'prenom' => $entite->getPrenom(),
            'email' => $entite->getEmail(),
            'motDePasse' => $entite->getMotDePasse(),
            'telephone' => $entite->getTelephone(),
            'adresse' => $entite->getAdresse(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime un client par son identifiant.
     *
     * @param int $id Identifiant du client à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM clients WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}