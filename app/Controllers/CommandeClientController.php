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
use Core\Response;
use Core\View;

/*
 * Gère le passage de commande, le suivi et l'historique côté client.
 * Nommé CommandeClientController (plutôt que CommandeController tout
 * court) pour bien le distinguer de son équivalent côté Gérant, qui
 * couvre des actions très différentes (changement de statut, annulation).
 */
final class CommandeClientController extends ControllerClientBase
{
    public function __construct(
        private readonly CommandeService $commandeService,
        private readonly PanierService $panierService,
        private readonly PaiementService $paiementService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Valide le panier du client connecté pour créer une commande, puis
     * redirige vers la page de suivi de cette commande.
     */
    public function valider(): void
    {
        $client = $this->exigerClientConnecte();

        if ($this->panierService->estVide()) {
            View::render('panier/index', [
                'lignes' => $this->panierService->contenu(),
                'montantTotal' => $this->panierService->montantTotal(),
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
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Affiche le suivi d'une commande : son statut actuel dans le cycle
     * de préparation.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function suivi(string $id): void
    {
        $client = $this->exigerClientConnecte();
        $commande = $this->trouverCommandeDuClient((int) $id, $client->getId());

        View::render('commandes/suivi', ['commande' => $commande]);
    }

    /**
     * Affiche l'historique complet des commandes du client connecté.
     */
    public function historique(): void
    {
        $client = $this->exigerClientConnecte();

        View::render('commandes/historique', [
            'commandes' => $this->commandeService->listerCommandesClient($client->getId()),
        ]);
    }

    /**
     * Affiche le détail complet d'une commande passée : ses lignes, son
     * montant, son statut, et l'historique de ses paiements.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function detail(string $id): void
    {
        $client = $this->exigerClientConnecte();
        $commande = $this->trouverCommandeDuClient((int) $id, $client->getId());

        View::render('commandes/detail', [
            'commande' => $commande,
            'paiements' => $this->paiementService->consulterParCommande($commande->getId()),
        ]);
    }

    /**
     * Récupère une commande en vérifiant qu'elle appartient bien au client
     * en cours — empêche un client de consulter la commande d'un autre en
     * modifiant l'identifiant dans l'URL.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @param int $clientId   Identifiant du client actuellement connecté
     * @return \App\Models\Commande La commande trouvée
     *
     * @throws AccesRefuseException si la commande n'appartient pas à ce client
     */
    private function trouverCommandeDuClient(int $commandeId, int $clientId): \App\Models\Commande
    {
        $commande = $this->commandeService->consulterCommande($commandeId);

        if ($commande === null || $commande->getClientId() !== $clientId) {
            throw new AccesRefuseException('Cette commande ne vous appartient pas.');
        }

        return $commande;
    }

    /**
     * Interrompt la requête et redirige vers la connexion si aucun client
     * n'est actuellement authentifié, sinon retourne le client connecté.
     *
     * @return \App\Models\Client Le client actuellement connecté
     */
    private function exigerClientConnecte(): \App\Models\Client
    {
        $client = $this->authService->clientConnecte();

        if ($client === null) {
            Response::redirect('/connexion');
        }

        return $client;
    }
}