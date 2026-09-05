<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Services\AuthService;
use App\Services\PanierService;
use Core\Response;
use Core\View;

/*
 * Gère le panier d'un client avant validation de sa commande. Toutes les
 * actions ici requièrent qu'un client soit connecté — vérifié par
 * exigerClientConnecte(), plutôt qu'un middleware dédié, pour rester
 * simple dans le périmètre de ce projet.
 */
final class PanierController extends ControllerClientBase
{
    public function __construct(
        private readonly PanierService $panierService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche le contenu actuel du panier.
     */
    public function afficher(): void
    {
        $this->exigerClientConnecte();

        View::render('panier/index', [
            'lignes' => $this->panierService->contenu(),
            'montantTotal' => $this->panierService->montantTotal(),
        ]);
    }

    /**
     * Ajoute un produit au panier. Redirige vers le catalogue avec un
     * message d'erreur en cas d'échec (produit inexistant ou stock
     * insuffisant), plutôt que de faire planter la requête.
     */
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
            View::render('produits/catalogue', ['erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Modifie la quantité d'un produit déjà présent dans le panier.
     */
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
            View::render('panier/index', [
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Retire un produit du panier.
     *
     * @param string $produitId Identifiant du produit, extrait de l'URL par le Router
     */
    public function retirer(string $produitId): void
    {
        $this->exigerClientConnecte();

        $this->panierService->retirer((int) $produitId);

        Response::redirect('/panier');
    }

    /**
     * Vide entièrement le panier.
     */
    public function vider(): void
    {
        $this->exigerClientConnecte();

        $this->panierService->vider();

        Response::redirect('/panier');
    }

    /**
     * Interrompt la requête et redirige vers la connexion si aucun client
     * n'est actuellement authentifié.
     */
    private function exigerClientConnecte(): void
    {
        if ($this->authService->clientConnecte() === null) {
            Response::redirect('/connexion');
        }
    }
}