<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Services\AuthService;
use App\Services\PanierService;
use Core\Response;

/*
 * Version JSON des actions du panier, destinée à être appelée en
 * arrière-plan par le JavaScript du site (ajout/modification/retrait
 * sans recharger la page). Appelle exactement le même PanierService que
 * PanierController : la logique métier n'est jamais dupliquée, seule la
 * façon de répondre change (JSON ici, redirection HTML côté
 * PanierController).
 */
final class PanierApiController
{
    public function __construct(
        private readonly PanierService $panierService,
        private readonly AuthService $authService,
    ) {
    }

    /**
     * Retourne le contenu actuel du panier au format JSON, pour que le
     * JavaScript puisse rafraîchir l'affichage (compteur, mini-liste)
     * sans recharger la page.
     */
    public function contenu(): void
    {
        if (!$this->clientEstConnecte()) {
            return;
        }

        $lignes = $this->panierService->contenu();

        Response::json([
            'succes' => true,
            'lignes' => array_map(
                fn(array $ligne) => [
                    'produitId' => $ligne['produit']->getId(),
                    'libelle' => $ligne['produit']->getLibelle(),
                    'quantite' => $ligne['quantite'],
                    'sousTotal' => $ligne['sousTotal'],
                ],
                $lignes
            ),
            'montantTotal' => $this->panierService->montantTotal(),
        ]);
    }

    /**
     * Ajoute un produit au panier et retourne le nouvel état du panier en
     * JSON. Renvoie un code 400 avec un message d'erreur si le produit
     * n'existe pas ou si le stock est insuffisant, plutôt que de faire
     * planter la requête.
     */
    public function ajouter(): void
    {
        if (!$this->clientEstConnecte()) {
            return;
        }

        try {
            $this->panierService->ajouter(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );

            Response::json([
                'succes' => true,
                'montantTotal' => $this->panierService->montantTotal(),
            ]);
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            Response::json(['succes' => false, 'erreur' => $exception->getMessage()], 400);
        }
    }

    /**
     * Modifie la quantité d'un produit déjà présent dans le panier et
     * retourne le nouvel état en JSON.
     */
    public function modifierQuantite(): void
    {
        if (!$this->clientEstConnecte()) {
            return;
        }

        try {
            $this->panierService->modifierQuantite(
                produitId: (int) ($_POST['produit_id'] ?? 0),
                quantite: (int) ($_POST['quantite'] ?? 1),
            );

            Response::json([
                'succes' => true,
                'montantTotal' => $this->panierService->montantTotal(),
            ]);
        } catch (ProduitInexistantException|StockInsuffisantException $exception) {
            Response::json(['succes' => false, 'erreur' => $exception->getMessage()], 400);
        }
    }

    /**
     * Retire un produit du panier et retourne le nouvel état en JSON.
     *
     * @param string $produitId Identifiant du produit, extrait de l'URL par le Router
     */
    public function retirer(string $produitId): void
    {
        if (!$this->clientEstConnecte()) {
            return;
        }

        $this->panierService->retirer((int) $produitId);

        Response::json([
            'succes' => true,
            'montantTotal' => $this->panierService->montantTotal(),
        ]);
    }

    /**
     * Vérifie qu'un client est connecté ; sinon, répond directement avec
     * une erreur JSON 401 plutôt que de rediriger comme le ferait la
     * version HTML (une redirection n'a pas de sens pour une requête
     * JavaScript en arrière-plan).
     *
     * @return bool true si un client est connecté, false sinon (réponse déjà envoyée)
     */
    private function clientEstConnecte(): bool
    {
        if ($this->authService->clientConnecte() === null) {
            Response::json(['succes' => false, 'erreur' => 'Vous devez être connecté.'], 401);
            return false;
        }

        return true;
    }
}