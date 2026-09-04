<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Produit;
use Core\Database;

/*
 * Accès aux données de la table produits. image est pleinement gérée par
 * ce module PHP (contrairement au Java Console qui ne fait que la lire) :
 * mettreAJour() l'inclut donc systématiquement.
 */
final class ProduitRepository implements RepositoryInterface
{
    private const COLONNES = 'id, libelle, description, prix, quantite_stock, seuil_alerte, disponible, image, categorie_id';

    /**
     * Recherche un produit par son identifiant.
     *
     * @param int $id Identifiant du produit recherché
     * @return Produit|null Le produit trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Produit
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM produits WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Produit::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les produits, triés par libellé.
     *
     * @return Produit[] Liste de tous les produits
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM produits ORDER BY libelle'
        );

        return array_map(
            fn(array $ligne) => Produit::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les produits appartenant à une catégorie donnée.
     *
     * @param int $categorieId Identifiant de la catégorie recherchée
     * @return Produit[] Produits de cette catégorie
     */
    public function trouverParCategorie(int $categorieId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM produits WHERE categorie_id = :categorieId ORDER BY libelle'
        );
        $requete->execute(['categorieId' => $categorieId]);

        return array_map(
            fn(array $ligne) => Produit::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Recherche les produits dont le libellé contient le mot-clé fourni,
     * indépendamment de la casse.
     *
     * @param string $motCle Fragment de libellé recherché
     * @return Produit[] Produits correspondants
     */
    public function rechercherParLibelle(string $motCle): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM produits WHERE libelle ILIKE :motCle ORDER BY libelle'
        );
        $requete->execute(['motCle' => '%' . $motCle . '%']);

        return array_map(
            fn(array $ligne) => Produit::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les produits dont le stock est sous leur seuil d'alerte,
     * sans être totalement épuisé.
     *
     * @return Produit[] Produits en stock faible
     */
    public function trouverStockFaible(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM produits
             WHERE quantite_stock > 0 AND quantite_stock <= seuil_alerte
             ORDER BY quantite_stock'
        );

        return array_map(
            fn(array $ligne) => Produit::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les produits totalement épuisés.
     *
     * @return Produit[] Produits en rupture de stock
     */
    public function trouverEnRupture(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM produits WHERE quantite_stock = 0 ORDER BY libelle'
        );

        return array_map(
            fn(array $ligne) => Produit::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Insère un nouveau produit en base. image n'est pas renseignée à la
     * création : elle est ajoutée dans un second temps via l'upload
     * d'illustration.
     *
     * @param Produit $entite Produit à créer (id ignoré)
     * @return Produit Le produit créé, avec son identifiant généré
     */
    public function creer(object $entite): Produit
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO produits (libelle, description, prix, quantite_stock, seuil_alerte, disponible, categorie_id)
             VALUES (:libelle, :description, :prix, :quantiteStock, :seuilAlerte, :disponible, :categorieId)
             RETURNING id'
        );
        $requete->execute([
            'libelle' => $entite->getLibelle(),
            'description' => $entite->getDescription(),
            'prix' => $entite->getPrix(),
            'quantiteStock' => $entite->getQuantiteStock(),
            'seuilAlerte' => $entite->getSeuilAlerte(),
            'disponible' => $entite->isDisponible(),
            'categorieId' => $entite->getCategorieId(),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Produit(
            $id,
            $entite->getLibelle(),
            $entite->getDescription(),
            $entite->getPrix(),
            $entite->getQuantiteStock(),
            $entite->getSeuilAlerte(),
            $entite->isDisponible(),
            null,
            $entite->getCategorieId(),
        );
    }

    /**
     * Met à jour l'ensemble des champs d'un produit existant, y compris son
     * stock, sa disponibilité et son illustration.
     *
     * @param Produit $entite Produit contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE produits
             SET libelle = :libelle, description = :description, prix = :prix,
                 quantite_stock = :quantiteStock, seuil_alerte = :seuilAlerte,
                 disponible = :disponible, image = :image, categorie_id = :categorieId
             WHERE id = :id'
        );
        $requete->execute([
            'libelle' => $entite->getLibelle(),
            'description' => $entite->getDescription(),
            'prix' => $entite->getPrix(),
            'quantiteStock' => $entite->getQuantiteStock(),
            'seuilAlerte' => $entite->getSeuilAlerte(),
            'disponible' => $entite->isDisponible(),
            'image' => $entite->getImage(),
            'categorieId' => $entite->getCategorieId(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime un produit par son identifiant.
     *
     * @param int $id Identifiant du produit à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM produits WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}