<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CategorieService;
use App\Services\PanierService;
use App\Services\ProduitService;
use Core\Response;
use Core\View;

final class ProduitController
{
    public function __construct(
        private readonly ProduitService $produitService,
        private readonly CategorieService $categorieService,
        private readonly PanierService $panierService,
        private readonly AuthService $authService,
    ) {
    }

    public function index(): void
    {
        $motCle = $_GET['recherche'] ?? null;
        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;

        $produits = match (true) {
            $motCle !== null && $motCle !== '' => $this->produitService->rechercherProduit($motCle),
            $categorieId !== null => $this->produitService->filtrerParCategorie($categorieId),
            default => $this->produitService->listerProduits(),
        };

        View::render('produits/catalogue', [
            'produits' => $produits,
            'categories' => $this->categorieService->listerCategories(),
            'categorieActive' => $categorieId,
            'client' => $this->authService->clientConnecte(),
            'titrePage' => 'Menu',
        ]);
    }

    public function show(string $id): void
    {
        $produit = $this->produitService->consulterProduit((int) $id);

        if ($produit === null) {
            Response::status(404);
            View::render('produits/introuvable', layout: null);
            return;
        }

        $suggestions = array_values(array_filter(
            $this->produitService->filtrerParCategorie($produit->getCategorieId()),
            fn($p) => $p->getId() !== $produit->getId()
        ));

        View::render('produits/detail', [
            'produit' => $produit,
            'suggestions' => array_slice($suggestions, 0, 3),
            'lignesPanier' => $this->panierService->contenu(),
            'montantTotalPanier' => $this->panierService->montantTotal(),
            'client' => $this->authService->clientConnecte(),
            'titrePage' => $produit->getLibelle(),
        ]);
    }
}