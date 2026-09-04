<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\LigneCommande;
use Core\Database;

/*
 * Accès aux données de la table ligne_commandes. Ce repository n'a pas
 * vocation à être appelé isolément par un contrôleur : c'est toujours le
 * service qui orchestre la reconstitution complète d'une commande avec
 * ses lignes, via trouverParCommande().
 */
final class LigneCommandeRepository implements RepositoryInterface
{
    private const COLONNES = 'id, commande_id, produit_id, quantite, prix_unitaire';

    /**
     * Recherche une ligne de commande par son identifiant.
     *
     * @param int $id Identifiant de la ligne recherchée
     * @return LigneCommande|null La ligne trouvée, ou null si elle n'existe pas
     */
    public function trouverParId(int $id): ?LigneCommande
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM ligne_commandes WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? LigneCommande::depuisLigne($ligne) : null;
    }

    /**
     * Retourne toutes les lignes de commande existantes, tous produits et
     * commandes confondus.
     *
     * @return LigneCommande[] Liste de toutes les lignes de commande
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM ligne_commandes'
        );

        return array_map(
            fn(array $ligne) => LigneCommande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les lignes appartenant à une commande donnée — méthode la
     * plus utilisée en pratique, pour reconstituer le détail complet d'une
     * commande.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return LigneCommande[] Lignes de cette commande
     */
    public function trouverParCommande(int $commandeId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM ligne_commandes WHERE commande_id = :commandeId'
        );
        $requete->execute(['commandeId' => $commandeId]);

        return array_map(
            fn(array $ligne) => LigneCommande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Insère une nouvelle ligne de commande en base.
     *
     * @param LigneCommande $entite Ligne à créer (id ignoré)
     * @return LigneCommande La ligne créée, avec son identifiant généré
     */
    public function creer(object $entite): LigneCommande
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO ligne_commandes (commande_id, produit_id, quantite, prix_unitaire)
             VALUES (:commandeId, :produitId, :quantite, :prixUnitaire)
             RETURNING id'
        );
        $requete->execute([
            'commandeId' => $entite->getCommandeId(),
            'produitId' => $entite->getProduitId(),
            'quantite' => $entite->getQuantite(),
            'prixUnitaire' => $entite->getPrixUnitaire(),
        ]);

        $id = (int) $requete->fetchColumn();

        return new LigneCommande(
            $id,
            $entite->getCommandeId(),
            $entite->getProduitId(),
            $entite->getQuantite(),
            $entite->getPrixUnitaire(),
        );
    }

    /**
     * Met à jour la quantité et le prix unitaire d'une ligne existante.
     * commandeId et produitId ne changent jamais après création d'une
     * ligne.
     *
     * @param LigneCommande $entite Ligne contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE ligne_commandes SET quantite = :quantite, prix_unitaire = :prixUnitaire WHERE id = :id'
        );
        $requete->execute([
            'quantite' => $entite->getQuantite(),
            'prixUnitaire' => $entite->getPrixUnitaire(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime une ligne de commande par son identifiant.
     *
     * @param int $id Identifiant de la ligne à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM ligne_commandes WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}