<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CompteDesactiveException;
use App\Exceptions\MotDePasseIncorrectException;
use App\Exceptions\UtilisateurInexistantException;
use App\Models\Client;
use App\Models\Utilisateur;
use App\Repositories\ClientRepository;
use App\Repositories\UtilisateurRepository;
use Core\Session;

/*
 * Centralise l'authentification des deux populations d'acteurs du site :
 * clients (espace public) et utilisateurs internes (espace Gérant/Admin).
 * Chacune a sa propre méthode de connexion, mais partage la même logique
 * de vérification (existence du compte, mot de passe, activation) et la
 * même gestion de session via Core\Session.
 */
final class AuthService
{
    private const CLE_SESSION_CLIENT = 'client_id';
    private const CLE_SESSION_UTILISATEUR = 'utilisateur_id';

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly UtilisateurRepository $utilisateurRepository,
    ) {
    }

    /**
     * Authentifie un client à partir de son email et de son mot de passe
     * en clair, puis ouvre sa session. L'identifiant de session est
     * régénéré après succès, pour empêcher toute fixation de session.
     *
     * @param string $email      Email saisi par le client
     * @param string $motDePasse Mot de passe en clair saisi par le client
     * @return Client Le client authentifié
     *
     * @throws UtilisateurInexistantException si aucun client ne correspond à cet email
     * @throws MotDePasseIncorrectException si le mot de passe ne correspond pas
     */
    public function authentifierClient(string $email, string $motDePasse): Client
    {
        $client = $this->clientRepository->trouverParEmail($email);

        if ($client === null) {
            throw new UtilisateurInexistantException('Aucun client trouvé avec cet email.');
        }

        if (!password_verify($motDePasse, $client->getMotDePasse())) {
            throw new MotDePasseIncorrectException('Mot de passe incorrect.');
        }

        Session::set(self::CLE_SESSION_CLIENT, $client->getId());
        Session::regenerer();

        return $client;
    }

    /**
     * Authentifie un utilisateur interne (Gérant ou Administrateur) à
     * partir de son email et de son mot de passe en clair, puis ouvre sa
     * session. Vérifie en plus que le compte est actif, contrainte propre
     * aux utilisateurs internes (les clients n'ont pas de champ actif).
     *
     * @param string $email      Email saisi par l'utilisateur
     * @param string $motDePasse Mot de passe en clair saisi par l'utilisateur
     * @return Utilisateur L'utilisateur authentifié
     *
     * @throws UtilisateurInexistantException si aucun utilisateur ne correspond à cet email
     * @throws MotDePasseIncorrectException si le mot de passe ne correspond pas
     * @throws CompteDesactiveException si le compte a été désactivé
     */
    public function authentifierUtilisateur(string $email, string $motDePasse): Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->trouverParEmail($email);

        if ($utilisateur === null) {
            throw new UtilisateurInexistantException('Aucun utilisateur trouvé avec cet email.');
        }

        if (!password_verify($motDePasse, $utilisateur->getMotDePasse())) {
            throw new MotDePasseIncorrectException('Mot de passe incorrect.');
        }

        if (!$utilisateur->isActif()) {
            throw new CompteDesactiveException('Ce compte a été désactivé.');
        }

        Session::set(self::CLE_SESSION_UTILISATEUR, $utilisateur->getId());
        Session::regenerer();

        return $utilisateur;
    }

    /**
     * Retourne le client actuellement connecté, ou null si aucun ne l'est.
     *
     * @return Client|null Le client en session, ou null
     */
    public function clientConnecte(): ?Client
    {
        $id = Session::get(self::CLE_SESSION_CLIENT);

        return $id !== null ? $this->clientRepository->trouverParId($id) : null;
    }

    /**
     * Retourne l'utilisateur interne actuellement connecté, ou null si
     * aucun ne l'est.
     *
     * @return Utilisateur|null L'utilisateur en session, ou null
     */
    public function utilisateurConnecte(): ?Utilisateur
    {
        $id = Session::get(self::CLE_SESSION_UTILISATEUR);

        return $id !== null ? $this->utilisateurRepository->trouverParId($id) : null;
    }

    /**
     * Déconnecte l'acteur actuellement en session (client ou utilisateur
     * interne), en détruisant entièrement la session.
     */
    public function deconnecter(): void
    {
        Session::detruire();
    }
}