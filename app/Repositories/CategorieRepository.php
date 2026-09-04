<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Categorie;
use Core\Database;
use PDO;

/*
 * Accès aux données de la table categories. image et couleur sont
 * pleinement gérées par ce module PHP (contrairement au Java Console qui
 * ne fait que les lire) : mettreAJour() les inclut donc systématiquement,
 * sans précaution particulière contrairement à CategorieRepository.java.
 */
final class CategorieRepository implements RepositoryInterface
{
    private const COLONNES = 'id, nom, description, image, couleur';

    /**
     * Recherche une catégorie par son identifiant.
     *
     * @param int $id Identifiant de la catégorie recherchée
     * @return Categorie|null La catégorie trouvée, ou null si elle n'existe pas
     */
    public function trouverParId(int $id): ?Categorie
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM categories WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Categorie::depuisLigne($ligne) : null;
    }

    /**
     * Retourne toutes les catégories, triées par nom.
     *
     * @return Categorie[] Liste de toutes les catégories
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM categories ORDER BY nom'
        );

        return array_map(
            fn(array $ligne) => Categorie::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Recherche les catégories dont le nom contient le mot-clé fourni,
     * indépendamment de la casse.
     *
     * @param string $motCle Fragment de nom recherché
     * @return Categorie[] Catégories correspondantes
     */
    public function rechercherParNom(string $motCle): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM categories WHERE nom ILIKE :motCle ORDER BY nom'
        );
        $requete->execute(['motCle' => '%' . $motCle . '%']);

        return array_map(
            fn(array $ligne) => Categorie::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Vérifie si une couleur est déjà attribuée à une catégorie, pour faire
     * respecter la règle métier d'unicité de la colorimétrie. La contrainte
     * UNIQUE en base agit comme filet de sécurité final, cette méthode
     * permet au service d'afficher une erreur claire avant toute tentative
     * d'écriture.
     *
     * @param string   $couleur   Code hexadécimal à vérifier (ex: #8B1424)
     * @param int|null $exclureId Identifiant de catégorie à exclure de la
     *                             vérification (utile lors d'une modification,
     *                             pour ne pas se comparer à soi-même)
     * @return bool true si la couleur est déjà utilisée par une autre catégorie
     */
    public function couleurDejaUtilisee(string $couleur, ?int $exclureId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE couleur = :couleur';
        $parametres = ['couleur' => $couleur];

        if ($exclureId !== null) {
            $sql .= ' AND id != :exclureId';
            $parametres['exclureId'] = $exclureId;
        }

        $requete = Database::getConnexion()->prepare($sql);
        $requete->execute($parametres);

        return ((int) $requete->fetchColumn()) > 0;
    }

    /**
     * Insère une nouvelle catégorie en base. image et couleur ne sont pas
     * renseignées à la création : elles sont ajoutées dans un second temps
     * via des actions dédiées (upload d'illustration, choix de couleur).
     *
     * @param Categorie $entite Catégorie à créer (id ignoré)
     * @return Categorie La catégorie créée, avec son identifiant généré
     */
    public function creer(object $entite): Categorie
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO categories (nom, description) VALUES (:nom, :description) RETURNING id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'description' => $entite->getDescription(),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Categorie($id, $entite->getNom(), $entite->getDescription());
    }

    /**
     * Met à jour l'ensemble des champs d'une catégorie existante, y compris
     * son illustration et sa colorimétrie.
     *
     * @param Categorie $entite Catégorie contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE categories
             SET nom = :nom, description = :description, image = :image, couleur = :couleur
             WHERE id = :id'
        );
        $requete->execute([
            'nom' => $entite->getNom(),
            'description' => $entite->getDescription(),
            'image' => $entite->getImage(),
            'couleur' => $entite->getCouleur(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime une catégorie par son identifiant. La règle métier
     * empêchant la suppression d'une catégorie encore utilisée par des
     * produits est vérifiée en amont par le service, pas ici.
     *
     * @param int $id Identifiant de la catégorie à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM categories WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}