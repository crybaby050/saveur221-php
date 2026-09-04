<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Représente un produit du catalogue, rattaché à une Categorie. disponible
 * n'est jamais fixée manuellement : elle est recalculée par le service
 * dès que quantiteStock change, pour garantir qu'elle reste toujours
 * cohérente avec le stock réel (règle métier : stock à 0 => indisponible).
 */
final class Produit
{
    public function __construct(
        private int $id,
        private string $libelle,
        private ?string $description,
        private float $prix,
        private int $quantiteStock,
        private int $seuilAlerte,
        private bool $disponible,
        private ?string $image,
        private int $categorieId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): void
    {
        $this->libelle = $libelle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): void
    {
        $this->prix = $prix;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function getSeuilAlerte(): int
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(int $seuilAlerte): void
    {
        $this->seuilAlerte = $seuilAlerte;
    }

    public function isDisponible(): bool
    {
        return $this->disponible;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getCategorieId(): int
    {
        return $this->categorieId;
    }

    public function setCategorieId(int $categorieId): void
    {
        $this->categorieId = $categorieId;
    }

    /*
     * Les mouvements de stock (approvisionnement, vente, restitution) sont
     * portés par l'entité elle-même plutôt que par le service, exactement
     * comme côté Java : recalculerDisponibilite() garantit qu'aucun
     * appelant ne peut faire évoluer quantiteStock sans que disponible
     * ne soit remis à jour dans le même mouvement.
     */
    public function approvisionner(int $quantite): void
    {
        $this->quantiteStock += $quantite;
        $this->recalculerDisponibilite();
    }

    public function diminuerStock(int $quantite): void
    {
        $this->quantiteStock -= $quantite;
        $this->recalculerDisponibilite();
    }

    public function restaurerStock(int $quantite): void
    {
        $this->quantiteStock += $quantite;
        $this->recalculerDisponibilite();
    }

    private function recalculerDisponibilite(): void
    {
        $this->disponible = $this->quantiteStock > 0;
    }

    public function estEnRupture(): bool
    {
        return $this->quantiteStock === 0;
    }

    public function estStockFaible(): bool
    {
        return $this->quantiteStock > 0 && $this->quantiteStock <= $this->seuilAlerte;
    }

    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            libelle: $ligne['libelle'],
            description: $ligne['description'],
            prix: (float) $ligne['prix'],
            quantiteStock: (int) $ligne['quantite_stock'],
            seuilAlerte: (int) $ligne['seuil_alerte'],
            disponible: (bool) $ligne['disponible'],
            image: $ligne['image'],
            categorieId: (int) $ligne['categorie_id'],
        );
    }
}