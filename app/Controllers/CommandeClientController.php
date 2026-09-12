<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AccesRefuseException;
use App\Exceptions\CommandeInvalideException;
use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Services\AuthService;
use App\Services\CommandeService;
use App\Services\PaiementService;
use App\Services\PanierService;
use App\Services\ProduitService;
use Core\Response;
use Core\View;

final class CommandeClientController extends ControllerClientBase
{
    public function __construct(
        private readonly CommandeService $commandeService,
        private readonly PanierService $panierService,
        private readonly PaiementService $paiementService,
        private readonly ProduitService $produitService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Valide le panier du client connecté pour créer une commande. Si
     * personne n'est connecté, réaffiche le panier avec une invitation à
     * se connecter, plutôt que de rediriger le visiteur ailleurs.
     */
    public function valider(): void
    {
        $client = $this->authService->clientConnecte();

        if ($client === null) {
            View::render('panier/index', [
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
                'client' => null,
            ]);
            return;
        }

        if ($this->panierService->estVide()) {
            View::render('panier/index', [
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
                'client' => $client,
                'erreur' => 'Votre panier est vide.',
            ]);
            return;
        }

        try {
            $lignesPanier = array_map(
                fn(array $ligne) => $ligne['quantite'],
                $this->panierService->contenu()
            );

            $commande = $this->commandeService->validerPanier($client->getId(), $lignesPanier);

            $this->panierService->vider();

            Response::redirect("/commandes/{$commande->getId()}/suivi");
        } catch (ProduitInexistantException|StockInsuffisantException|CommandeInvalideException $exception) {
            View::render('panier/index', [
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
                'client' => $client,
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    public function suivi(string $id): void
    {
        $client = $this->exigerClientConnecte();
        $commande = $this->trouverCommandeDuClient((int) $id, $client->getId());

        View::render('commandes/suivi', ['commande' => $commande, 'client' => $client]);
    }

    public function historique(): void
    {
        $client = $this->exigerClientConnecte();

        View::render('commandes/historique', [
            'commandes' => $this->commandeService->listerCommandesClient($client->getId()),
            'client' => $client,
        ]);
    }

    public function detail(string $id): void
    {
        $client = $this->exigerClientConnecte();
        $commande = $this->trouverCommandeDuClient((int) $id, $client->getId());

        View::render('commandes/detail', [
            'commande' => $commande,
            'lignesEnrichies' => $this->enrichirLignes($commande),
            'paiements' => $this->paiementService->consulterParCommande($commande->getId()),
            'client' => $client,
        ]);
    }

    private function trouverCommandeDuClient(int $commandeId, int $clientId): \App\Models\Commande
    {
        $commande = $this->commandeService->consulterCommande($commandeId);

        if ($commande === null || $commande->getClientId() !== $clientId) {
            throw new AccesRefuseException('Cette commande ne vous appartient pas.');
        }

        return $commande;
    }

    /**
     * Associe à chaque ligne de la commande le produit correspondant, pour
     * afficher son nom réel plutôt que son identifiant.
     *
     * @return array<int, array{ligne: \App\Models\LigneCommande, produit: ?\App\Models\Produit}>
     */
    private function enrichirLignes(\App\Models\Commande $commande): array
    {
        return array_map(
            fn($ligne) => [
                'ligne' => $ligne,
                'produit' => $this->produitService->consulterProduit($ligne->getProduitId()),
            ],
            $commande->getLignes()
        );
    }
}