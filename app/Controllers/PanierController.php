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
 * Gère le panier. Il vit en session, indépendamment de tout compte
 * client : un visiteur non connecté peut librement le consulter et le
 * modifier. Seule la validation finale de la commande (voir
 * CommandeClientController::valider) exige d'être connecté.
 */
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
        View::render('panier/index', [
            'lignes' => $this->panierService->contenu(),
            'montantTotal' => $this->panierService->montantTotal(),
            'client' => $this->authService->clientConnecte(),
            'erreur' => $_GET['erreur'] ?? null,
        ]);
    }

    public function ajouter(): void
    {
        try {
            $this->panierService->ajouter(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );

            Response::redirect('/panier');
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            Response::redirect('/panier?erreur=' . urlencode($exception->getMessage()));
        }
    }

    public function modifierQuantite(): void
    {
        try {
            $this->panierService->modifierQuantite(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );

            Response::redirect('/panier');
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            Response::redirect('/panier?erreur=' . urlencode($exception->getMessage()));
        }
    }

    public function retirer(string $produitId): void
    {
        $this->panierService->retirer((int) $produitId);

        Response::redirect('/panier');
    }

    public function vider(): void
    {
        $this->panierService->vider();

        Response::redirect('/panier');
    }
}