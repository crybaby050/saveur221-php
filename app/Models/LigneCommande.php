<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Représente une ligne d'une commande : association entre un produit, une
 * quantité et le prix unitaire figé au moment de la commande. prixUnitaire
 * est volontairement dupliqué depuis Produit::getPrix() plutôt que
 * recalculé à la volée, pour que le montant facturé reste inchangé même si
 * le prix du produit évolue par la suite.
 */
final class LigneCommande
{
    public function __construct(
        private int $id,
        private int $commandeId,
        private int $produitId,
        private int $quantite,
        private float $prixUnitaire,
    ) {
    }

    /**
     * Identifiant unique de la ligne en base.
     *
     * @return int Identifiant de la ligne de commande
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Identifiant de la commande à laquelle appartient cette ligne.
     *
     * @return int Identifiant de la commande parente
     */
    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    /**
     * Identifiant du produit concerné par cette ligne.
     *
     * @return int Identifiant du produit
     */
    public function getProduitId(): int
    {
        return $this->produitId;
    }

    /**
     * Quantité commandée pour ce produit dans cette ligne.
     *
     * @return int Quantité commandée
     */
    public function getQuantite(): int
    {
        return $this->quantite;
    }

    /**
     * Prix unitaire du produit, tel qu'il était au moment de la commande.
     *
     * @return float Prix unitaire figé
     */
    public function getPrixUnitaire(): float
    {
        return $this->prixUnitaire;
    }

    /**
     * Calcule le montant total de cette ligne (quantité multipliée par le
     * prix unitaire figé).
     *
     * @return float Sous-total de la ligne
     */
    public function calculerSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    /**
     * Reconstruit une instance de LigneCommande à partir d'une ligne de
     * résultat PDO.
     *
     * @param array $ligne Ligne issue de la table ligne_commandes
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            commandeId: (int) $ligne['commande_id'],
            produitId: (int) $ligne['produit_id'],
            quantite: (int) $ligne['quantite'],
            prixUnitaire: (float) $ligne['prix_unitaire'],
        );
    }
}