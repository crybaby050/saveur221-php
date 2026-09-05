<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Exceptions\EmailDejaUtiliseException;
use App\Exceptions\MotDePasseInvalideException;
use App\Models\Utilisateur;
use App\Repositories\UtilisateurRepository;

/*
 * Applique les règles métier liées aux utilisateurs internes (Gérant,
 * Administrateur). Réservé à l'espace Administrateur côté contrôleur —
 * ce service lui-même ne vérifie aucun rôle, cette responsabilité
 * appartient au contrôleur qui l'appelle (voir AccesRefuseException).
 */
final class UtilisateurService
{
    private const LONGUEUR_MIN_MOT_DE_PASSE = 6;

    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
    ) {
    }

    /**
     * Retourne tous les utilisateurs internes.
     *
     * @return Utilisateur[] Liste de tous les utilisateurs internes
     */
    public function listerUtilisateurs(): array
    {
        return $this->utilisateurRepository->trouverTous();
    }

    /**
     * Récupère un utilisateur interne par son identifiant.
     *
     * @param int $id Identifiant de l'utilisateur recherché
     * @return Utilisateur|null L'utilisateur trouvé, ou null s'il n'existe pas
     */
    public function consulterUtilisateur(int $id): ?Utilisateur
    {
        return $this->utilisateurRepository->trouverParId($id);
    }

    /**
     * Recherche les utilisateurs internes dont le nom ou le prénom contient
     * le mot-clé fourni.
     *
     * @param string $motCle Fragment de nom ou prénom recherché
     * @return Utilisateur[] Utilisateurs correspondants
     */
    public function rechercherUtilisateur(string $motCle): array
    {
        return $this->utilisateurRepository->rechercherParNom($motCle);
    }

    /**
     * Crée un nouvel utilisateur interne, après avoir vérifié l'unicité de
     * son email et la longueur minimale de son mot de passe. Le compte est
     * actif dès sa création. Le mot de passe est haché avant d'être
     * transmis au repository.
     *
     * @param string $nom        Nom de l'utilisateur
     * @param string $prenom     Prénom de l'utilisateur
     * @param string $email      Email de l'utilisateur, doit être unique
     * @param string $motDePasse Mot de passe en clair, sera haché ici
     * @param Role   $role       Rôle attribué (ADMIN ou GERANT)
     * @return Utilisateur L'utilisateur créé
     *
     * @throws EmailDejaUtiliseException si l'email est déjà utilisé
     * @throws MotDePasseInvalideException si le mot de passe est trop court
     */
    public function ajouterUtilisateur(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        Role $role,
    ): Utilisateur {
        if ($this->utilisateurRepository->trouverParEmail($email) !== null) {
            throw new EmailDejaUtiliseException("Cet email est déjà utilisé : {$email}");
        }

        if (strlen($motDePasse) < self::LONGUEUR_MIN_MOT_DE_PASSE) {
            throw new MotDePasseInvalideException(
                'Le mot de passe doit contenir au moins ' . self::LONGUEUR_MIN_MOT_DE_PASSE . ' caractères.'
            );
        }

        $motDePasseHache = password_hash($motDePasse, PASSWORD_BCRYPT);

        $utilisateur = new Utilisateur(0, $nom, $prenom, $email, $motDePasseHache, true, $role);

        return $this->utilisateurRepository->creer($utilisateur);
    }

    /**
     * Modifie le nom, le prénom et l'email d'un utilisateur interne
     * existant, sans toucher à son mot de passe, son statut d'activation
     * ni son rôle — voir les méthodes dédiées pour ces opérations.
     *
     * @param int    $id     Identifiant de l'utilisateur à modifier
     * @param string $nom    Nouveau nom
     * @param string $prenom Nouveau prénom
     * @param string $email  Nouvel email
     *
     * @throws \InvalidArgumentException si l'utilisateur n'existe pas
     */
    public function modifierUtilisateur(int $id, string $nom, string $prenom, string $email): void
    {
        $utilisateur = $this->trouverOuLever($id);

        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setEmail($email);

        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    /**
     * Supprime un utilisateur interne.
     *
     * @param int $id Identifiant de l'utilisateur à supprimer
     */
    public function supprimerUtilisateur(int $id): void
    {
        $this->utilisateurRepository->supprimerParId($id);
    }

    /**
     * Réactive un compte utilisateur interne préalablement désactivé.
     *
     * @param int $id Identifiant de l'utilisateur à activer
     *
     * @throws \InvalidArgumentException si l'utilisateur n'existe pas
     */
    public function activer(int $id): void
    {
        $utilisateur = $this->trouverOuLever($id);
        $utilisateur->activer();

        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    /**
     * Désactive un compte utilisateur interne, lui interdisant toute
     * connexion future jusqu'à réactivation.
     *
     * @param int $id Identifiant de l'utilisateur à désactiver
     *
     * @throws \InvalidArgumentException si l'utilisateur n'existe pas
     */
    public function desactiver(int $id): void
    {
        $utilisateur = $this->trouverOuLever($id);
        $utilisateur->desactiver();

        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    /**
     * Change le rôle d'un utilisateur interne (ADMIN <-> GERANT).
     *
     * @param int  $id   Identifiant de l'utilisateur concerné
     * @param Role $role Nouveau rôle à attribuer
     *
     * @throws \InvalidArgumentException si l'utilisateur n'existe pas
     */
    public function changerRole(int $id, Role $role): void
    {
        $utilisateur = $this->trouverOuLever($id);
        $utilisateur->changerRole($role);

        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    /**
     * Récupère un utilisateur interne par son identifiant ou lève une
     * exception s'il n'existe pas.
     *
     * @param int $id Identifiant de l'utilisateur recherché
     * @return Utilisateur L'utilisateur trouvé
     *
     * @throws \InvalidArgumentException si aucun utilisateur ne correspond
     */
    private function trouverOuLever(int $id): Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->trouverParId($id);

        if ($utilisateur === null) {
            throw new \InvalidArgumentException("Utilisateur introuvable avec l'id {$id}.");
        }

        return $utilisateur;
    }
}