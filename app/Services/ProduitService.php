<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ProduitInexistantException;
use App\Models\Produit;
use App\Repositories\ProduitRepository;

/*
 * Applique les règles métier liées aux produits, notamment la cohérence
 * entre quantiteStock et disponible — cette cohérence est en réalité
 * garantie par le Model lui-même (Produit::approvisionner/diminuerStock/
 * restaurerStock), ce service se contente d'orchestrer : charger le
 * produit, lui déléguer l'opération, persister le résultat.
 */
final class ProduitService
{
    /*
     * Illustration affichée pour tout produit n'ayant pas encore reçu sa
     * propre image — même logique que pour les catégories.
     */
    private const IMAGE_PAR_DEFAUT = '/assets/images/produit-defaut.svg';

    public function __construct(
        private readonly ProduitRepository $produitRepository,
    ) {
    }

    /**
     * Retourne tous les produits du catalogue.
     *
     * @return Produit[] Liste de tous les produits
     */
    public function listerProduits(): array
    {
        return $this->produitRepository->trouverTous();
    }

    /**
     * Récupère un produit par son identifiant.
     *
     * @param int $id Identifiant du produit recherché
     * @return Produit|null Le produit trouvé, ou null s'il n'existe pas
     */
    public function consulterProduit(int $id): ?Produit
    {
        return $this->produitRepository->trouverParId($id);
    }

    /**
     * Recherche les produits dont le libellé contient le mot-clé fourni.
     *
     * @param string $motCle Fragment de libellé recherché
     * @return Produit[] Produits correspondants
     */
    public function rechercherProduit(string $motCle): array
    {
        return $this->produitRepository->rechercherParLibelle($motCle);
    }

    /**
     * Retourne les produits appartenant à une catégorie donnée.
     *
     * @param int $categorieId Identifiant de la catégorie recherchée
     * @return Produit[] Produits de cette catégorie
     */
    public function filtrerParCategorie(int $categorieId): array
    {
        return $this->produitRepository->trouverParCategorie($categorieId);
    }

    /**
     * Retourne les produits dont le stock est sous leur seuil d'alerte.
     *
     * @return Produit[] Produits en stock faible
     */
    public function consulterStockFaible(): array
    {
        return $this->produitRepository->trouverStockFaible();
    }

    /**
     * Retourne les produits totalement épuisés.
     *
     * @return Produit[] Produits en rupture de stock
     */
    public function consulterRuptures(): array
    {
        return $this->produitRepository->trouverEnRupture();
    }

    /**
     * Crée un nouveau produit. Si aucune illustration n'est fournie, une
     * image générique par défaut est utilisée à sa place. disponible est
     * déterminée automatiquement à partir de la quantité initiale, jamais
     * saisie directement.
     *
     * @param string      $libelle       Nom du produit
     * @param string|null $description   Description facultative
     * @param float       $prix          Prix unitaire
     * @param int         $quantiteStock Quantité initiale en stock
     * @param int         $seuilAlerte   Seuil déclenchant l'alerte de stock faible
     * @param int         $categorieId   Identifiant de la catégorie de rattachement
     * @param string|null $image         URL ou chemin de l'illustration, ou
     *                                    null pour utiliser l'image par défaut
     * @return Produit Le produit créé
     */
    public function ajouterProduit(
        string $libelle,
        ?string $description,
        float $prix,
        int $quantiteStock,
        int $seuilAlerte,
        int $categorieId,
        ?string $image = null,
    ): Produit {
        $produit = new Produit(
            id: 0,
            libelle: $libelle,
            description: $description,
            prix: $prix,
            quantiteStock: $quantiteStock,
            seuilAlerte: $seuilAlerte,
            disponible: $quantiteStock > 0,
            image: $image ?? self::IMAGE_PAR_DEFAUT,
            categorieId: $categorieId,
        );

        return $this->produitRepository->creer($produit);
    }

    /**
     * Modifie le libellé, la description, le prix, l'illustration et la
     * catégorie d'un produit existant. Ne touche jamais à quantiteStock ni
     * disponible : ces deux champs ne peuvent évoluer que via
     * approvisionner(), diminuerStock() ou restaurerStock().
     *
     * @param int         $id          Identifiant du produit à modifier
     * @param string      $libelle     Nouveau libellé
     * @param string|null $description Nouvelle description
     * @param float       $prix        Nouveau prix
     * @param int         $categorieId Nouvelle catégorie de rattachement
     * @param string|null $image       Nouvelle illustration, ou null pour
     *                                  revenir à l'image par défaut
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     */
    public function modifierProduit(
        int $id,
        string $libelle,
        ?string $description,
        float $prix,
        int $categorieId,
        ?string $image = null,
    ): void {
        $produit = $this->trouverOuLever($id);

        $produit->setLibelle($libelle);
        $produit->setDescription($description);
        $produit->setPrix($prix);
        $produit->setCategorieId($categorieId);
        $produit->setImage($image ?? self::IMAGE_PAR_DEFAUT);

        $this->produitRepository->mettreAJour($produit);
    }

    /**
     * Supprime un produit du catalogue.
     *
     * @param int $id Identifiant du produit à supprimer
     */
    public function supprimerProduit(int $id): void
    {
        $this->produitRepository->supprimerParId($id);
    }

    /**
     * Définit un nouveau seuil d'alerte pour un produit.
     *
     * @param int $id    Identifiant du produit concerné
     * @param int $seuil Nouvelle valeur du seuil d'alerte
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     */
    public function definirSeuilAlerte(int $id, int $seuil): void
    {
        $produit = $this->trouverOuLever($id);
        $produit->setSeuilAlerte($seuil);

        $this->produitRepository->mettreAJour($produit);
    }

    /**
     * Augmente le stock d'un produit (réapprovisionnement) et persiste la
     * disponibilité recalculée par le Model.
     *
     * @param int $id       Identifiant du produit à approvisionner
     * @param int $quantite Quantité à ajouter au stock
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     */
    public function approvisionner(int $id, int $quantite): void
    {
        $produit = $this->trouverOuLever($id);
        $produit->approvisionner($quantite);

        $this->produitRepository->mettreAJour($produit);
    }

    /**
     * Diminue le stock d'un produit à la suite d'une vente et persiste la
     * disponibilité recalculée. Utilisée en interne par CommandeService
     * lors de la création d'une commande, jamais appelée directement par
     * un contrôleur.
     *
     * @param int $id       Identifiant du produit vendu
     * @param int $quantite Quantité vendue à retirer du stock
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     */
    public function diminuerStock(int $id, int $quantite): void
    {
        $produit = $this->trouverOuLever($id);
        $produit->diminuerStock($quantite);

        $this->produitRepository->mettreAJour($produit);
    }

    /**
     * Restitue du stock à la suite d'une annulation de commande et
     * persiste la disponibilité recalculée. Utilisée en interne par
     * CommandeService, jamais appelée directement par un contrôleur.
     *
     * @param int $id       Identifiant du produit concerné
     * @param int $quantite Quantité à restituer au stock
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     */
    public function restaurerStock(int $id, int $quantite): void
    {
        $produit = $this->trouverOuLever($id);
        $produit->restaurerStock($quantite);

        $this->produitRepository->mettreAJour($produit);
    }

    /**
     * Récupère un produit par son identifiant ou lève une exception s'il
     * n'existe pas — évite de dupliquer cette vérification dans chaque
     * méthode publique de ce service.
     *
     * @param int $id Identifiant du produit recherché
     * @return Produit Le produit trouvé
     *
     * @throws ProduitInexistantException si aucun produit ne correspond
     */
    private function trouverOuLever(int $id): Produit
    {
        $produit = $this->produitRepository->trouverParId($id);

        if ($produit === null) {
            throw new ProduitInexistantException("Produit introuvable avec l'id {$id}.");
        }

        return $produit;
    }
}