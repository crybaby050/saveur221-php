<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Utilisateur;
use Core\Database;

/*
 * Accès aux données de la table utilisateurs. Le rôle est stocké via une
 * clé étrangère vers la table roles : chaque requête de lecture fait donc
 * une jointure pour reconstruire l'enum Role côté PHP, exactement comme
 * côté Java Console.
 */
final class UtilisateurRepository implements RepositoryInterface
{
    private const SELECT_BASE = 'SELECT u.id, u.nom, u.prenom, u.email, u.mot_de_passe, u.actif, r.nom AS role_nom
                                  FROM utilisateurs u JOIN roles r ON u.role_id = r.id';

    /**
     * Recherche un utilisateur interne par son identifiant.
     *
     * @param int $id Identifiant de l'utilisateur recherché
     * @return Utilisateur|null L'utilisateur trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Utilisateur
    {
        $requete = Database::getConnexion()->prepare(self::SELECT_BASE . ' WHERE u.id = :id');
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Utilisateur::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les utilisateurs internes, triés par nom puis prénom.
     *
     * @return Utilisateur[] Liste de tous les utilisateurs internes
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(self::SELECT_BASE . ' ORDER BY u.nom, u.prenom');

        return array_map(
            fn(array $ligne) => Utilisateur::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Recherche un utilisateur interne par son adresse email exacte,
     * utilisée pour la connexion et pour vérifier l'unicité de l'email à
     * la création d'un compte.
     *
     * @param string $email Email recherché
     * @return Utilisateur|null L'utilisateur trouvé, ou null si aucun ne correspond
     */
    public function trouverParEmail(string $email): ?Utilisateur
    {
        $requete = Database::getConnexion()->prepare(self::SELECT_BASE . ' WHERE u.email = :email');
        $requete->execute(['email' => $email]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Utilisateur::depuisLigne($ligne) : null;
    }

    /**
     * Recherche les utilisateurs internes dont le nom ou le prénom contient
     * le mot-clé fourni, indépendamment de la casse.
     *
     * @param string $motCle Fragment de nom ou prénom recherché
     * @return Utilisateur[] Utilisateurs correspondants
     */
    public function rechercherParNom(string $motCle): array
    {
        $requete = Database::getConnexion()->prepare(
            self::SELECT_BASE . ' WHERE u.nom ILIKE :motCle OR u.prenom ILIKE :motCle ORDER BY u.nom, u.prenom'
        );
        $requete->execute(['motCle' => '%' . $motCle . '%']);

        return array_map(
            fn(array $ligne) => Utilisateur::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Insère un nouvel utilisateur interne en base, en résolvant son rôle
     * vers l'identifiant correspondant dans la table roles.
     *
     * @param Utilisateur $entite Utilisateur à créer (id ignoré)
     * @return Utilisateur L'utilisateur créé, avec son identifiant généré
     */
    public function creer(object $entite): Utilisateur
    {
        $connexion = Database::getConnexion();
        $roleId = $this->resoudreRoleId($entite->getRole());

        $requete = $connexion->prepare(
            'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, actif, role_id)
             VALUES (:nom, :prenom, :email, :motDePasse, :actif, :roleId)
             RETURNING id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'prenom' => $entite->getPrenom(),
            'email' => $entite->getEmail(),
            'motDePasse' => $entite->getMotDePasse(),
            'actif' => $entite->isActif(),
            'roleId' => $roleId,
        ]);

        $id = (int) $requete->fetchColumn();

        return new Utilisateur(
            $id,
            $entite->getNom(),
            $entite->getPrenom(),
            $entite->getEmail(),
            $entite->getMotDePasse(),
            $entite->isActif(),
            $entite->getRole(),
        );
    }

    /**
     * Met à jour le nom, prénom, email, statut d'activation et rôle d'un
     * utilisateur existant. Le mot de passe n'est volontairement pas inclus
     * ici : un changement de mot de passe doit passer par une méthode
     * dédiée, pour éviter d'écraser un hash par erreur.
     *
     * @param Utilisateur $entite Utilisateur contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $connexion = Database::getConnexion();
        $roleId = $this->resoudreRoleId($entite->getRole());

        $requete = $connexion->prepare(
            'UPDATE utilisateurs
             SET nom = :nom, prenom = :prenom, email = :email, actif = :actif, role_id = :roleId
             WHERE id = :id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'prenom' => $entite->getPrenom(),
            'email' => $entite->getEmail(),
            'actif' => $entite->isActif(),
            'roleId' => $roleId,
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime un utilisateur interne par son identifiant.
     *
     * @param int $id Identifiant de l'utilisateur à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM utilisateurs WHERE id = :id');
        $requete->execute(['id' => $id]);
    }

    /**
     * Traduit une valeur de l'enum Role vers l'identifiant correspondant
     * dans la table roles, nécessaire puisque role_id est une clé
     * étrangère et non le nom du rôle stocké directement.
     *
     * @param Role $role Rôle à résoudre
     * @return int Identifiant du rôle en base
     */
    private function resoudreRoleId(Role $role): int
    {
        $requete = Database::getConnexion()->prepare('SELECT id FROM roles WHERE nom = :nom');
        $requete->execute(['nom' => $role->value]);

        $id = $requete->fetchColumn();

        if ($id === false) {
            throw new \RuntimeException("Rôle inconnu en base : {$role->value}");
        }

        return (int) $id;
    }
}