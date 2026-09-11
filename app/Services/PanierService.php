<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Repositories\ProduitRepository;
use Core\Session;

/*
 * Gère le panier d'un client avant validation de la commande. Le panier
 * n'a pas d'entité ni de table dédiées : il vit entièrement en session,
 * sous la forme d'un tableau associatif [produitId => quantite]. C'est
 * volontairement léger, puisque son contenu n'a de valeur qu'avant d'être
 * transformé en véritable Commande par CommandeService.
 */
final class PanierService
{
    private const CLE_SESSION_PANIER = 'panier';

    public function __construct(
        private readonly ProduitRepository $produitRepository,
    ) {
    }

    public function contenu(): array
    {
        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $resultat = [];

        foreach ($panier as $produitId => $quantite) {
            $produit = $this->produitRepository->trouverParId($produitId);

            if ($produit === null) {
                continue;
            }

            $resultat[$produitId] = [
                'produit' => $produit,
                'quantite' => $quantite,
                'sousTotal' => $produit->getPrix() * $quantite,
            ];
        }

        return $resultat;
    }

    public function montantTotal(): float
    {
        return array_reduce(
            $this->contenu(),
            fn(float $total, array $ligne) => $total + $ligne['sousTotal'],
            0.0
        );
    }

    public function ajouter(int $produitId, int $quantite): void
    {
        $produit = $this->produitRepository->trouverParId($produitId);

        if ($produit === null) {
            throw new ProduitInexistantException("Produit introuvable avec l'id {$produitId}.");
        }

        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $quantiteExistante = $panier[$produitId] ?? 0;
        $nouvelleQuantite = $quantiteExistante + $quantite;

        if ($nouvelleQuantite > $produit->getQuantiteStock()) {
            throw new StockInsuffisantException(
                "Stock insuffisant pour {$produit->getLibelle()} (disponible : {$produit->getQuantiteStock()})."
            );
        }

        $panier[$produitId] = $nouvelleQuantite;
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    public function modifierQuantite(int $produitId, int $quantite): void
    {
        $produit = $this->produitRepository->trouverParId($produitId);

        if ($produit === null) {
            throw new ProduitInexistantException("Produit introuvable avec l'id {$produitId}.");
        }

        if ($quantite > $produit->getQuantiteStock()) {
            throw new StockInsuffisantException(
                "Stock insuffisant pour {$produit->getLibelle()} (disponible : {$produit->getQuantiteStock()})."
            );
        }

        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $panier[$produitId] = $quantite;
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    public function retirer(int $produitId): void
    {
        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        unset($panier[$produitId]);
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    public function vider(): void
    {
        Session::remove(self::CLE_SESSION_PANIER);
    }

    public function estVide(): bool
    {
        return empty(Session::get(self::CLE_SESSION_PANIER, []));
    }
}