<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;

/*
 * Représente un membre du personnel interne (Gérant ou Administrateur).
 * actif détermine l'accès à l'application : un compte désactivé ne peut
 * plus se connecter, règle vérifiée par le service d'authentification.
 */
final class Utilisateur
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $motDePasse,
        private bool $actif,
        private Role $role,
    ) {
    }

    /**
     * Identifiant unique de l'utilisateur en base.
     *
     * @return int Identifiant de l'utilisateur
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Nom de famille de l'utilisateur.
     *
     * @return string Nom de l'utilisateur
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Modifie le nom de famille de l'utilisateur.
     *
     * @param string $nom Nouveau nom
     */
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * Prénom de l'utilisateur.
     *
     * @return string Prénom de l'utilisateur
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * Modifie le prénom de l'utilisateur.
     *
     * @param string $prenom Nouveau prénom
     */
    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * Adresse email de l'utilisateur, utilisée pour la connexion.
     *
     * @return string Email de l'utilisateur
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Modifie l'adresse email de l'utilisateur. L'unicité de l'email est une
     * règle métier vérifiée en amont par le service, pas ici.
     *
     * @param string $email Nouvel email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Mot de passe haché de l'utilisateur. Ne doit jamais être affiché ni
     * journalisé en clair.
     *
     * @return string Hash du mot de passe (format BCrypt)
     */
    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    /**
     * Indique si le compte est actuellement actif.
     *
     * @return bool true si le compte peut se connecter, false sinon
     */
    public function isActif(): bool
    {
        return $this->actif;
    }

    /**
     * Réactive le compte, lui redonnant l'accès à l'application.
     */
    public function activer(): void
    {
        $this->actif = true;
    }

    /**
     * Désactive le compte : l'utilisateur ne pourra plus se connecter tant
     * qu'un administrateur ne l'aura pas réactivé.
     */
    public function desactiver(): void
    {
        $this->actif = false;
    }

    /**
     * Rôle actuel de l'utilisateur (ADMIN ou GERANT), déterminant les menus
     * et actions auxquels il a accès.
     *
     * @return Role Rôle de l'utilisateur
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * Change le rôle de l'utilisateur.
     *
     * @param Role $role Nouveau rôle à attribuer
     */
    public function changerRole(Role $role): void
    {
        $this->role = $role;
    }

    /**
     * Reconstruit une instance d'Utilisateur à partir d'une ligne de
     * résultat PDO. La ligne doit contenir une colonne role_nom (obtenue
     * par jointure avec la table roles), pas directement role_id.
     *
     * @param array $ligne Ligne issue d'une jointure utilisateurs + roles
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
            actif: (bool) $ligne['actif'],
            role: Role::from($ligne['role_nom']),
        );
    }
}