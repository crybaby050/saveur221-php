<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CategorieService;
use App\Services\ProduitService;
use Core\View;

/*
 * Page d'accueil publique : vitrine (hero, catégories, quelques
 * suggestions), distincte de /produits qui présente le menu complet avec
 * recherche et filtrage.
 */
final class AccueilController
{
    private const NOMBRE_SUGGESTIONS = 4;

    public function __construct(
        private readonly ProduitService $produitService,
        private readonly CategorieService $categorieService,
        private readonly AuthService $authService,
    ) {
    }

    public function index(): void
    {
        View::render('accueil/index', [
            'categories' => $this->categorieService->listerCategories(),
            'suggestions' => array_slice($this->produitService->listerProduits(), 0, self::NOMBRE_SUGGESTIONS),
            'client' => $this->authService->clientConnecte(),
            'titrePage' => 'Accueil',
        ]);
    }
}