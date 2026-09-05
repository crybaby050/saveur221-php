<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CategorieService;
use App\Services\ProduitService;
use Core\View;
use Core\Response;

/*
 * Gère la consultation publique du catalogue : liste des produits, avec
 * recherche et filtrage, et détail d'un produit. Aucune authentification
 * requise ici, ces actions sont accessibles à tout visiteur.
 */
final class ProduitController
{
    public function __construct(
        private readonly ProduitService $produitService,
        private readonly CategorieService $categorieService,
    ) {
    }

    /**
     * Affiche le catalogue, filtré par mot-clé et/ou catégorie si ces
     * paramètres sont présents dans la requête GET.
     */
    public function index(): void
    {
        $motCle = $_GET['recherche'] ?? null;
        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;

        $produits = match (true) {
            $motCle !== null => $this->produitService->rechercherProduit($motCle),
            $categorieId !== null => $this->produitService->filtrerParCategorie($categorieId),
            default => $this->produitService->listerProduits(),
        };

        View::render('produits/catalogue', [
            'produits' => $produits,
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

    /**
     * Affiche le détail d'un produit.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function show(string $id): void
    {
        $produit = $this->produitService->consulterProduit((int) $id);

        if ($produit === null) {
            Response::status(404);
            View::render('produits/introuvable', layout: null);
            return;
        }

        View::render('produits/detail', ['produit' => $produit]);
    }
}