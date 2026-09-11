<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Services\AuthService;
use App\Services\PanierService;
use Core\Response;
use Core\View;

final class PanierController extends ControllerClientBase
{
    public function __construct(
        private readonly PanierService $panierService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function afficher(): void
    {
        $this->exigerClientConnecte();
    
        View::render('panier/index', $this->avecClient([
            'titrePage' => 'Mon panier',
            'lignes' => $this->panierService->contenu(),
            'montantTotal' => $this->panierService->montantTotal(),
        ]));
    }
    
    public function ajouter(): void
    {
        $this->exigerClientConnecte();
    
        try {
            $this->panierService->ajouter(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );
    
            Response::redirect('/panier');
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            View::render('produits/catalogue', $this->avecClient(['erreur' => $exception->getMessage()]));
        }
    }
    
    public function modifierQuantite(): void
    {
        $this->exigerClientConnecte();
    
        try {
            $this->panierService->modifierQuantite(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );
    
            Response::redirect('/panier');
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            View::render('panier/index', $this->avecClient([
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
                'erreur' => $exception->getMessage(),
            ]));
        }
    }

    public function retirer(string $produitId): void
    {
        $this->exigerClientConnecte();

        $this->panierService->retirer((int) $produitId);

        Response::redirect('/panier');
    }

    public function vider(): void
    {
        $this->exigerClientConnecte();

        $this->panierService->vider();

        Response::redirect('/panier');
    }
}